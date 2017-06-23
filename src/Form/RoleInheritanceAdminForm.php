<?php

namespace Drupal\role_inheritance\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Contribute form.
 */
class RoleInheritanceAdminForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'role_inheritance_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $roles = user_roles();

    $role_mapping = _role_inheritance_role_map();
    $role_mapping_collapsed = _role_inheritance_role_map(NULL, TRUE);

    $form["info"] = array(
      '#type' => 'inline_template',
      '#template' => '<div class="role"><span class="title">{{ title }}</span></div>',
      '#context' => array(
        'title' => "Configure inheritance of permissions and access from one role to another. Roles in the left column will inherit permissions from roles in the top row.",
      ),
    );

    $form['inheritance'] = array(
      '#type' => 'table',
      '#header' => array($this->t('Inherit From')),
      '#id' => 'role-inheritance-all',
      '#attributes' => ['class' => ['role-inheritance-all', 'js-role-inheritance-all']],
      '#sticky' => TRUE,
    );

    foreach ($roles as $rid => $role) {
      $form['inheritance']['#header'][] = array(
        'data' => $role->label(),
        'class' => array('checkbox'),
      );
    }

    foreach ($roles as $rid => $role) {
      // Fill in default values for the role.
      $form['inheritance'][$rid]['description'] = array(
        '#type' => 'inline_template',
        '#template' => '<div class="role"><span class="title">{{ title }}</span></div>',
        '#context' => array(
          'title' => $role->label(),
        ),
        '#wrapper_attributes' => array(
          'class' => array('ri-row-description', 'rid-' . $rid, 'js-rid-' . $rid),
        ),
      );
      foreach ($roles as $srid => $srole) {
        if ($srid == $rid) {
          $form['inheritance'][$rid][$srid] = array(
            '#type' => 'inline_template',
            '#template' => 'X',
            '#wrapper_attributes' => array(
              'class' => array('ri-no-inheritance'),
            ),
          );
        }
        else {
          $form['inheritance'][$rid][$srid] = array(
            '#title' => $role->label() . ' inherits from ' . $srole->label(),
            '#title_display' => 'invisible',
            '#wrapper_attributes' => array(
              'class' => array('checkbox'),
            ),
            '#type' => 'checkbox',
            '#default_value' => 0,
            '#attributes' => array(
              'class' => array('rid-' . $srid, 'js-rid-' . $srid),
              'data-ri-role' => $rid,
              'data-ri-inherits' => $srid,
            ),
            '#parents' => array($rid, $srid),
          );

          if (isset($role_mapping[$rid]) && in_array($srid, $role_mapping[$rid])) {
            $form['inheritance'][$rid][$srid]['#default_value'] = 1;
          } elseif (isset($role_mapping_collapsed[$rid]) && in_array($srid, $role_mapping_collapsed[$rid])) {
            $form['inheritance'][$rid][$srid]['#attributes']['class'][] = "js-ri-inherited";
          }

          // Admin inherits from everyone, and everyone inherites from authenticated.
          // This is how core handles permissions, so we should too.
          if ($role->isAdmin()
              || ($srid == AccountInterface::AUTHENTICATED_ROLE
                  && $rid !== AccountInterface::ANONYMOUS_ROLE)) {
            $form['inheritance'][$rid][$srid]["#default_value"] = 1;
            $form['inheritance'][$rid][$srid]["#disabled"] = true;
          }
        }
      }
    }

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save permissions'),
      '#button_type' => 'primary',
    );

    // mapping of a role and what roles inherit from it.
    $role_mapping_providers = array();
    $role_mapping_providers_collapsed = array();

    foreach ($role_mapping as $rid => $inherit_from) {
      foreach ($inherit_from as $provider) {
        $role_mapping_providers[$provider][] = $rid;
      }
    }

    foreach ($role_mapping_collapsed as $rid => $inherit_from) {
      foreach ($inherit_from as $provider) {
        $role_mapping_providers_collapsed[$provider][] = $rid;
      }
    }

    // Add js to disable inherited permissions.
    $form['#attached']['library'][] = 'role_inheritance/role_inheritance.mapping';
    $form['#attached']['drupalSettings']['role_inheritance']['map'] = $role_mapping;
    $form['#attached']['drupalSettings']['role_inheritance']['map_collapsed'] = $role_mapping_collapsed;
    $form['#attached']['drupalSettings']['role_inheritance']['providers'] = $role_mapping_providers;
    $form['#attached']['drupalSettings']['role_inheritance']['providers_collapsed'] = $role_mapping_providers_collapsed;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $roles = array_keys(user_roles());

    $mapping = array();

    foreach ($roles as $role) {

      foreach ($values[$role] as $iRole => $inherit) {
        if ($inherit) {
          $mapping[$role][] = $iRole;
        }
      }
    }

    _role_inheritance_role_map($mapping);
  }

}

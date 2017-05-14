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
      // Fill in default values for the permission.
      $form['inheritance'][$rid]['description'] = array(
        '#type' => 'inline_template',
        '#template' => '<div class="permission"><span class="title">{{ title }}</span></div>',
        '#context' => array(
          'title' => $role->label(),
        ),
      );
      foreach ($roles as $srid => $srole) {
        if ($srid == $rid) {
          $form['inheritance'][$rid][$srid] = array(
            '#type' => 'inline_template',
            '#template' => '<center>X</center>',
            '#style' => 'text-align:center;',
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
            '#attributes' => array('class' => array('rid-' . $srid, 'js-rid-' . $srid)),
            '#parents' => array($rid, $srid),
          );

          if (isset($role_mapping[$rid]) && in_array($srid, $role_mapping[$rid])) {
            $form['inheritance'][$rid][$srid]['#default_value'] = 1;
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

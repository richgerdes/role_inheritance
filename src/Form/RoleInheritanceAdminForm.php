<?php
/**
 * @file
 * Contains \Drupal\role_inheritance\Form\RoleInheritanceAdminForm.
 */

namespace Drupal\role_inheritance\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;

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
    $role_names = array();
    foreach (user_roles() as $role_name => $role) {
      // Retrieve role names for columns.
      $role_names[$role_name] = $role->label();
    }

    // Store $role_names for use when saving the data.
    $form['role_names'] = array(
      '#type' => 'value',
      '#value' => $role_names,
    );

    $form['inheritance'] = array(
      '#type' => 'table',
      '#header' => array($this->t('Inherit From')),
      '#id' => 'inheritance',
      '#attributes' => ['class' => ['inheritance', 'js-inheritance']],
      '#sticky' => TRUE,
    );
    
    foreach ($role_names as $name) {
      $form['inheritance']['#header'][] = array(
        'data' => $name,
        'class' => array('checkbox'),
      );
    }

    foreach ($role_names as $rid => $name) {
      // Fill in default values for the permission.
      $perm_item = array(
        'title' => $name,
        'restrict access' => FALSE,
      );
      $form['inheritance'][$rid]['description'] = array(
        '#type' => 'inline_template',
        '#template' => '<div class="permission"><span class="title">{{ title }}</span></div>',
        '#context' => array(
          'title' => $name,
        ),
      );
      foreach ($role_names as $srid => $sname) {
        if($srid == $rid){
          $form['inheritance'][$rid][$srid] = array(
            '#type' => 'inline_template',
            '#template' => '<center>X</center>',
            '#style' => 'text-align:center;',
          );
        }else{
          $form['inheritance'][$rid][$srid] = array(
            '#title' => $sname . ': ' . $perm_item['title'],
            '#title_display' => 'invisible',
            '#wrapper_attributes' => array(
              'class' => array('checkbox'),
            ),
            '#type' => 'checkbox',
            '#default_value' => 0,
            '#attributes' => array('class' => array('rid-' . $srid, 'js-rid-' . $srid)),
            '#parents' => array($rid, $srid),
          );
          
          
          
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
  }
}
?>
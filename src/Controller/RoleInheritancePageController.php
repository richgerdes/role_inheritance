<?php
/**
 * @file
 * Contains \Drupal\role_inheritance\Controller\RoleInheritancePageController.
 */
namespace Drupal\role_inheritance\Controller;

use Drupal\Core\Controller\ControllerBase;

class RoleInheritancePageController extends ControllerBase {
  public function confRolesPage() {
    return [
        '#markup' => $this->t('Something goes here!'),
    ];
  }
}
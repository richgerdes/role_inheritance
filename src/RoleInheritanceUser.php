<?php

/**
 * @file
 * Contains Drupal\role_inheritance\RoleInheritanceUser.
 */

namespace Drupal\role_inheritance;

use \Drupal\user\Entity\User;

class RoleInheritanceUser extends User {

  public function getRoles($exclude_locked_roles = FALSE) {
    $roles = parent::getRoles($exclude_locked_roles);
    if (!$exclude_locked_roles) {
      $roles = _role_inheritance_extendroles($roles);
    }
    return $roles;
  }

}

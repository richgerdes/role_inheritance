<?php

/**
 * @file
 * Contains Drupal\user\RoleStorage.
 */

namespace Drupal\role_inheritance;

use \Drupal\user\RoleStorage;

class RoleInheritanceStorage extends RoleStorage{

  public function isPermissionInRoles($permission, array $rids) {

    $extendedRids = _role_inheritance_extendroles($rids);

    $p = parent::isPermissionInRoles($permission, $extendedRids);

    return $p;
  }

}
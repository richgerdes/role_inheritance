<?php

namespace Drupal\role_inheritance;

use Drupal\user\RoleStorage;

/**
 * Class to replace Core role storage class to allow permission inheritance.
 */
class RoleInheritanceStorage extends RoleStorage {

  /**
   * {@inheritdoc}
   *
   * Before executing original isPermissionInRoles, extend the role list to
   * include roles inherited by this module.
   */
  public function isPermissionInRoles($permission, array $rids) {

    $extendedRids = _role_inheritance_extendroles($rids);

    $p = parent::isPermissionInRoles($permission, $extendedRids);

    return $p;
  }

}

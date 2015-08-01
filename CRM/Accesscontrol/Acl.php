<?php

class CRM_Accesscontrol_Acl {

  public static function aclWhereClause($type, &$tables, &$whereTables, &$contactID, &$where) {

    if (!$contactID) {
      return;
    }

    // Algemene core permissies die we al dan niet toestaan (was eerst vooral VIEW, nu ook EDIT)
    $config = CRM_Accesscontrol_Config::singleton();
    if (!in_array($type, $config->getAllowedCorePermissions())) {
      return;
    }

    // Hele lelijke hack om op basis van de oorspronkelijke klassenaam te bepalen of dit bewerkt mag worden
    // (dat is met name voor de weergave, notes lijken niet écht afgeschermd en de rest gebeurt obv ACLs)
    $allowedClasses = $config->getEditAllowedForClasses();
    if (count($allowedClasses) > 0) {
      $callingClass = $config->getCallingClass();
      // CRM_Core_Session::setStatus('Checking permission ' . $type . ' for class ' . $callingClass);
      if ($type == CRM_Core_Permission::EDIT && !in_array($callingClass, $allowedClasses)) {
        return;
      }
    }

    // Check passed, hand over to AclGenerator to extend where clause
    CRM_Geostelsel_AclGenerator::generateWhereClause($type, $tables, $whereTables, $contactID, $where);
  }

  public static function aclGroupList($type, $contactID, $tableName, &$allGroups, &$currentGroups) {
    if ($tableName != 'civicrm_saved_search') {
      return;
    }
    $group_access = new CRM_Geostelsel_Groep_ParentGroup();
    $parent_groups = $group_access->getParentGroupsByContact($contactID);
    foreach ($parent_groups as $gid) {
      if (isset($allGroups[$gid])) {
        $currentGroups[] = $gid;
      }
    }
  }

}

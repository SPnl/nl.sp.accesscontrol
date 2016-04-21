<?php

class CRM_Accesscontrol_Acl {

  public static function aclWhereClause($type, &$tables, &$whereTables, &$contactID, &$where) {
    if (!$contactID) {
      return;
    }

    if (CRM_Core_Permission::check('access all contacts (edit)')) {
      $where = '1';
      return;
    } elseif ($type == CRM_Core_Permission::VIEW && CRM_Core_Permission::check('access all contacts (view)')) {
      $where = '1';
      return;
    }

    // Algemene core permissies die we al dan niet toestaan (was eerst vooral VIEW, nu ook EDIT)
    $config = CRM_Accesscontrol_Config::singleton();
    if (!in_array($type, $config->getAllowedCorePermissions())) {
      return TRUE;
    }

    // Lelijke hack om het mogelijk te maken notities en custom data wél te bekijken en te bewerken,
    // want daar wordt de ACL clause voor uitgevoerd. Voor een call naar 1 form van 1 contact blijkbaar dus niet.
    $callingClass = $config->getCallingClass();
    if ($type == CRM_Core_Permission::EDIT && !in_array($callingClass, $config->getAclEditAllowedClasses())) {
      return TRUE;
    }

    // Check passed, hand over to AclGenerator to extend where clause
    CRM_Geostelsel_AclGenerator::generateWhereClause($type, $tables, $whereTables, $contactID, $where);
  }

  public static function aclGroupList($type, $contactID, $tableName, &$allGroups, &$currentGroups) {
    if ($tableName != 'civicrm_saved_search' && $tableName != 'civicrm_event' && $tableName != 'civicrm_custom_group') {
      return;
    }
    $group_access = new CRM_Geostelsel_Groep_ParentGroup();
    if (CRM_Core_Permission::check('hide local groups')) {
      $parent_groups = $group_access->parentGroups();
      $subgroups = CRM_Contact_BAO_GroupNesting::getChildGroupIds($parent_groups);
      foreach($allGroups as $gid => $label) {
        if (!in_array($gid, $subgroups)) {
          $currentGroups[] = $gid;
        }
      }
    } else {
      $parent_groups = $group_access->getParentGroupsByContact($contactID);
      foreach ($parent_groups as $gid) {
        $currentGroups[] = $gid;
      }
    }
  }

}

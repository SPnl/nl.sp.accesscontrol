<?php

class CRM_Accesscontrol_Acl {

  /**
   * Replace the 'View all contacts' and 'Edit all contacts' permissions
   *
   * @param $permissions
   */
  public static function alterApiPermissions(&$permissions) {
    foreach($permissions as $entity =>$entityPermissions) {
      foreach($entityPermissions as $action => $actionPermissions) {
        foreach ($actionPermissions as $index => $permission) {
          if (is_array($permission)) {
            foreach($permission as $subindex => $subpermission) {
              if ($subpermission == 'view all contacts') {
                $permissions[$entity][$action][$index][$subindex] = 'access all contacts (view)';
              } elseif ($subpermission == 'edit all contacts') {
                $permissions[$entity][$action][$index][$subindex] = 'access all contacts (edit)';
              }
            }
          } else {
            if ($permission == 'view all contacts') {
              $permissions[$entity][$action][$index] = 'access all contacts (view)';
            } elseif ($permission == 'edit all contacts') {
              $permissions[$entity][$action][$index] = 'access all contacts (edit)';
            }
          }
        }
      }
    }

    /**
     * Allow users to always get an e-mail address. But only for the contacts
     * they are allowed to see. 
		 * 
		 * We did this so that a local chapter user can see the list of recipients when composing a mass mail (if we dont do this the user ends up with an error)
     */
    $permissions['email']['getvalue'] = array('access CiviCRM');
    $permissions['email']['get'] = array('access CiviCRM');
  }

	public static function selectWhereClause($entity, &$clauses) {
		// We reset the api persmission for the email so that anyone with access civicrm can use this e-mail api.
		// So now we have to include an ACL clause when a query runs on the email table.
		if ($entity == 'Email' || $entity == 'Membership') {
			if (CRM_Core_Permission::check('access all contacts (edit)') || CRM_Core_Permission::check('access all contacts (view)') || CRM_Core_Permission::check('view all contacts') || CRM_Core_Permission::check('edit all contacts')) {
      	return;
			}
			$aclContactCache = \Civi::service('acl_contact_cache');
    	$aclWhere = $aclContactCache->getAclWhereClause(CRM_Core_Permission::VIEW, $tableAlias);
			$aclWhere = " AND contact_id IN (SELECT contact_id FROM `civicrm_acl_contacts` WHERE ".$aclWhere.")";			
			$clauses['contact_id'] = $aclWhere;
		}
	}

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

    // standaard groepen die afdelingen altijd zien.
    foreach(CRM_Accesscontrol_StandardGroup_Groups::getStandardGroups() as $gid) {
      $currentGroups[] = $gid;
    }
  }

}

<?php

// Use this api wrapper to limit the options in the group drop downs. E.g. on
// the basic search screen a local user can only chose one of the local groups.
// An administrator can see all groups except the local groups (if the permission 'hide local groups' is checked).

class CRM_Accesscontrol_GroupApiWrapper implements API_Wrapper {

  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * limit the list of "from"-addresses
   * @param array $apiRequest
   * @param array $result
   *
   * @return array
   *   modified $result
   */
  public function toApiOutput($apiRequest, $result) {
    $session = CRM_Core_Session::singleton();
    $contactID = $session->get('userID');
    $currentGroups = array();
    $groupsToHide = array();

    if ($apiRequest['entity'] == 'GroupContact' && $apiRequest['action'] == 'getoptions' && !empty($contactID)) {
      $validFieldName = array('group', 'group_id');
      if (!isset($apiRequest['params']) || !isset($apiRequest['params']['field']) || !in_array($apiRequest['params']['field'], $validFieldName)) {
        return $result;
      }

      $sequential = false;
      if (isset($apiRequest['params']['sequential']) && $apiRequest['params']['sequential']) {
        $sequential = true;
      }

      $accessToAllGroups = false;
      if (CRM_Core_Permission::check('access all contacts (view)') || CRM_Core_Permission::check('view all contacts')) {
        $accessToAllGroups = true;
      }

      $group_access = new CRM_Geostelsel_Groep_ParentGroup();
      if (CRM_Core_Permission::check('hide local groups')) {
        $parent_groups = $group_access->parentGroups();
        $subgroups = CRM_Contact_BAO_GroupNesting::getChildGroupIds($parent_groups);
        foreach($subgroups as $gid => $label) {
          $groupsToHide[] = $gid;
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

      $newValues = array();
      if (!$sequential) {
        foreach ($result['values'] as $gid => $group) {
          if (!$accessToAllGroups && in_array($gid, $currentGroups)) {
            $newValues[$gid] = $result['values'][$gid];
          } elseif ($accessToAllGroups && !in_array($gid, $groupsToHide)) {
            $newValues[$gid] = $result['values'][$gid];
          }
        }
      } else {
        $j=0;
        for ($i=0; $i < count($result['values']); $i++) {
          if (!$accessToAllGroups && in_array($result['values'][$i]['key'], $currentGroups)) {
            $newValues[$j] = $result['values'][$i];
            $j++;
          } elseif ($accessToAllGroups && !in_array($result['values'][$i]['key'], $groupsToHide)) {
            $newValues[$j] = $result['values'][$i];
            $j++;
          }
        }
      }
      $result['values'] = $newValues;
      $result['count'] = count($result['values']);
    }

    return $result;
  }
}
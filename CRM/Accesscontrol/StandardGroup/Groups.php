<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Accesscontrol_StandardGroup_Groups {

  public static function getStandardGroups() {
    try {
      $config = CRM_Accesscontrol_StandardGroup_Config::singleton();
      $sql = "SELECT `entity_id` as `group_id` FROM `" . $config->getCustomGroup('table_name') . "` WHERE `" . $config->getStandardAfdelingsGroep('column_name') . "` = '1'";
      $dao = CRM_Core_DAO::executeQuery($sql);
      $return = array();
      while ($dao->fetch()) {
        $return[] = $dao->group_id;
      }
      return $return;
    } catch (Exception $e) {
      return array();
    }
  }
  
  public static function pre($op, $objectName, $id, &$params) {
    if ($objectName == 'Group') {
      //if user has not permission to manage groups then add the parents of the access groups
      if (CRM_Core_Permission::check('administer reserved groups')) {
        $config = CRM_Accesscontrol_StandardGroup_Config::singleton();
        if (isset($params['custom'][$config->getStandardAfdelingsGroep('id')])) {
          $standaardAfdelingsGroep = reset($params['custom'][$config->getStandardAfdelingsGroep('id')]);
          if (isset($standaardAfdelingsGroep['value']) && $standaardAfdelingsGroep['value']) {
            $params['is_reserved'] = '1';
          }
        }
      }
    }
  }

}
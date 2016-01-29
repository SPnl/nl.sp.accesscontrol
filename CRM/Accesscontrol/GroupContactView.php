<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Accesscontrol_GroupContactView {

  public static function allowAddToGroup(&$page) {
    if (!$page instanceof  CRM_Contact_Page_View_GroupContact) {
      return;
    }

    if ($page->_permission == CRM_Core_Permission::VIEW && CRM_Core_Permission::check('add to group enabled')) {
      $page->assign('permission', 'edit');
      $page->_permission = CRM_Core_Permission::EDIT;
    }
  }

}
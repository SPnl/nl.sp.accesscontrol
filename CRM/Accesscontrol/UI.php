<?php

class CRM_Accesscontrol_UI {

  public static function restrictTabs(&$tabs, $contactID) {

    if (!CRM_Core_Permission::check('show tags tab')) {
      self::unsetTab('tag', $tabs);
    }

    if (!CRM_Core_Permission::check('show changelog tab')) {
      self::unsetTab('log', $tabs);
    }

    if (!CRM_Core_Permission::check('show toegangsgegevens tab')) {
      $config = CRM_Geostelsel_Config_Toegang::singleton();
      self::unsetTab('custom_' . $config->getToegangCustomGroup('id'), $tabs);
    }
  }

  private static function unsetTab($tab_id, &$tabs) {

    foreach ($tabs as $key => $tab) {
      if ($tab['id'] == $tab_id) {
        unset($tabs[$key]);
      }
    }
  }

  public static function pageRunHook(&$page) {

    if ($page instanceof CRM_Contact_Page_View_Summary) {

      // Verbergen bij niet-afdelingsgebruikers
      $is_afd_user = CRM_Core_Permission::checkGroupRole(array('Afdelingsgebruiker CiviCRM'));
      if (!$is_afd_user) {
        return TRUE;
      }

      // Link toevoegen aan template
      $cid = CRM_Utils_Request::retrieve('cid', 'Positive');
      CRM_Core_Region::instance('page-body')->add(array(
        'template' => "CRM/Contact/Page/View/Summary/link_afdedit.tpl",
      ));
      $smarty = CRM_Core_Smarty::singleton();
      $smarty->assign('link_afdedit', '/civicrm-afdeling/edit-contact?cid1=' . $cid);

    }

  }

}
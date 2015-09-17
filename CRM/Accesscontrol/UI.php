<?php

class CRM_Accesscontrol_UI {

  public static function restrictTabs(&$tabs, $contactID) {

    $tabPermissions = CRM_Accesscontrol_Config::singleton()->getPermissionsForTabs();
    $unsetTabs = array();
    foreach ($tabPermissions as $tabKey => $permission) {

      if (!CRM_Core_Permission::check($permission)) {
        if ($tabKey == 'custom_Toegangsgegevens') {
          $config = CRM_Geostelsel_Config_Toegang::singleton();
          $unsetTabs[] = 'custom_' . $config->getToegangCustomGroup('id');
        } else {
          $unsetTabs[] = $tabKey;
        }
      }
    }

    foreach ($tabs as $key => $tab) {
      if (in_array($tab['id'], $unsetTabs)) {
        if ($tab['id'] == 'activity') {
          $tabs[$key]['url'] = CRM_Utils_System::url('civicrm/accesscontrol/contact/view/activity', "show=1&cid=$contactID&snippet=1");
          $tabs[$key]['count'] = CRM_Accesscontrol_BAO_Activity::getActivitiesCount(array(
            'contact_id' => $contactID,
            'admin' => FALSE,
            'caseId' => NULL,
            'context' => 'activity',
          ));
        } else {
          unset($tabs[$key]);
        }
      }
    }
  }

  public static function restrictPages(&$page) {

    $pagePermissions = CRM_Accesscontrol_Config::singleton()->getPermissionsForPages();
    $currentPage = get_class($page);
//    CRM_Core_Session::setStatus('PAGE ' . $currentPage, '', 'info');

    if($currentPage && array_key_exists($currentPage, $pagePermissions)) {
      if(!CRM_Core_Permission::check($pagePermissions[$currentPage])) {
        self::returnAccessDenied($currentPage);
      }
    }
  }

  public static function restrictForms($formName, &$form) {

    $formPermissions = CRM_Accesscontrol_Config::singleton()->getPermissionsForForms();
//    CRM_Core_Session::setStatus('FORM ' . $formName, '', 'info');

    if($formName && array_key_exists($formName, $formPermissions)) {
      if(!CRM_Core_Permission::check($formPermissions[$formName])) {
        self::returnAccessDenied($formName);
      }
    }
  }

  public static function modifyMenu(&$params) {

    foreach($params as &$param) {

      if($param['attributes']['name'] == 'Events') {
        $param['attributes']['permission'] = 'show CiviEvent menu';
      }

      if($param['attributes']['name'] == 'Memberships') {
        $param['attributes']['permission'] = 'show CiviMember menu';
      }
    }
  }

  private static function returnAccessDenied($name = NULL) {

    CRM_Core_Session::setStatus('U hebt geen toegang tot deze pagina' . ($name ? ' (' . $name . ')' : '') . '.', '', 'info');

    $referer = CRM_Utils_System::refererPath();
    if ($referer && strpos($referer, $_SERVER['REQUEST_URI']) === false) {
      CRM_Utils_System::redirect($referer);
    }
    else {
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/dashboard'));
    }
  }

  public static function addContactPageLink(&$page) {

    // Link toevoegen aan page
    if ($page instanceof CRM_Contact_Page_View_Summary) {

      // Verbergen bij niet-afdelingsgebruikers (ie als je 'edit all contacts' hebt)
      if(CRM_Core_Permission::check('edit all contacts')) {
        return true;
      }

      // Link toevoegen aan template
      $cid = CRM_Utils_Request::retrieve('cid', 'Positive');
      CRM_Core_Region::instance('page-body')->add(array(
        'template' => "CRM/Contact/Page/View/Summary/link_afdedit.tpl",
      ));
      $smarty = CRM_Core_Smarty::singleton();
      $smarty->assign('link_afdedit', '/form/wijziging-contactgegevens?cid1=' . $cid);

    }
  }


}
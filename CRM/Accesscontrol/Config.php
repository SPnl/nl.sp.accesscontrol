<?php

class CRM_Accesscontrol_Config {

  private static $singleton;

  private $allowedCorePermissions = array(
    CRM_Core_Permission::VIEW,
    CRM_Core_Permission::EDIT,
    CRM_Core_Permission::VIEW_GROUPS,
    CRM_Core_Permission::SEARCH,
  );

  private $aclEditAllowedClasses = array(
    'CRM_Contact_Page_View_Note',
    'CRM_Contact_Page_View_CustomData',
  );

  private $permissionsForTabs = array(
    'tag'                     => 'show tags tab',
    'log'                     => 'show changelog tab',
    'activity'                => 'show activity tab',
    'custom_Toegangsgegevens' => 'show toegangsgegevens tab',
  );

  private $permissionsForPages = array(
    'CRM_Activity_Page_Tab' => 'show activity tab',
  );

  private $permissionsForForms = array(
    'CRM_Contact_Form_Relationship'   => 'edit all contacts',
    'CRM_Activity_Form_Search'        => 'show activity tab',
    'CRM_Report_Form_Mailing_Summary' => 'access CiviMail reports',
  );

  /**
   * @return CRM_Accesscontrol_Config
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new self;
    }
    return self::$singleton;
  }

  public function getAllowedCorePermissions() {
    return $this->allowedCorePermissions;
  }

  public function getAclEditAllowedClasses() {
    return $this->aclEditAllowedClasses;
  }

  public function getPermissionsForTabs() {
    return $this->permissionsForTabs;
  }

  public function getPermissionsForPages() {
    return $this->permissionsForPages;
  }

  public function getPermissionsForForms() {
    return $this->permissionsForForms;
  }

  public function getExtraPermissions(&$permissions) {

    $permissions['show changelog tab'] = ts('CiviCRM') . ': ' . ts('show Changelog tab');
    $permissions['show tags tab'] = ts('CiviCRM') . ': ' . ts('show Tags tab');
    $permissions['show activity tab'] = ts('CiviCRM') . ': ' . ts('show Activity tab');
    $permissions['show CiviEvent menu'] = ts('CiviEvent') . ': ' . ts('show CiviEvent menu');
    $permissions['show CiviMember menu'] = ts('CiviMember') . ': ' . ts('show CiviMember menu');
    $permissions['show toegangsgegevens tab'] = ts('CiviCRM') . ': ' . ts('show Toegangsgegevens tab');
    $permissions['CiviMail use from default'] = ts('CiviMail') . ': ' . ts('use default from addresses');
    $permissions['CiviMail use from afdeling'] = ts('CiviMail') . ': ' . ts('use afdeling from addresses');
    $permissions['CiviMail use from personal'] = ts('CiviMail') . ': ' . ts('use personal from address');
    $permissions['access CiviMail reports'] = ts('CiviReport') . ': ' . ts('access CiviMail reports');
    $permissions['access local reports'] = ts('CiviReport') . ': ' . ts('access local reports');
    $permissions['restrict activities'] = ts('CiviCRM') . ': ' . ts('restrict activities');
  }

  public function getCallingClass() {
    $trace = debug_backtrace(FALSE);
    foreach ($trace as $key => $t) {
      if ($t['class'] == 'CRM_Core_Invoke') {
        return $trace[$key - 1]['class'];
      }
    }
  }

  public function isClassInStack($className) {
    $trace = debug_backtrace(FALSE);
    foreach ($trace as $key => $t) {
      if ($t['class'] == $className) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
<?php

class CRM_Accesscontrol_Config {

  private static $singleton;

  private $allowedCorePermissions = array(
    CRM_Core_Permission::VIEW,
    CRM_Core_Permission::EDIT,
    CRM_Core_Permission::VIEW_GROUPS,
    CRM_Core_Permission::SEARCH,
  );

  private $editAllowedForClasses = array(
    'CRM_Contact_Page_View_Note',
    'CRM_Contact_Page_View_CustomData',
  );

  private $status_ids = array();

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

  public function getEditAllowedForClasses() {
    return $this->editAllowedForClasses;
  }

  public function getExtraPermissions(&$permissions) {

    $permissions['show changelog tab'] = ts('CiviCRM') . ': ' . ts('show Changelog tab');
    $permissions['show tags tab'] = ts('CiviCRM') . ': ' . ts('show Tags tab');
    $permissions['show toegangsgegevens tab'] = ts('CiviCRM') . ': ' . ts('show Toegangsgegevens tab');
    $permissions['CiviMail use from default'] = ts('CiviMail') . ': ' . ts('Use default from addresses');
    $permissions['CiviMail use from afdeling'] = ts('CiviMail') . ': ' . ts('Use afdeling from addresses');
    $permissions['CiviMail use from personal'] = ts('CiviMail') . ': ' . ts('Use personal from address');
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
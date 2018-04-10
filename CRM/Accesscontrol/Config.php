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
    'CRM_Contribute_Form_ContributionView' => 'access CiviContribute',
  );
	
	private $mailingPages = array(
		'CRM_Mailing_Page_Event' => array('mid' => 'mid')
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
	
	public function getMailingPages() {
		return $this->mailingPages;
	}

  public function getExtraPermissions(&$permissions) {
    $permissions['show changelog tab'] = ts('CiviCRM') . ': ' . ts('show Changelog tab');
    $permissions['show tags tab'] = ts('CiviCRM') . ': ' . ts('show Tags tab');
    $permissions['add to group enabled'] = ts('CiviCRM') . ': ' . ts('Add to group enabled on group tab of contact summary');
    $permissions['show activity tab'] = ts('CiviCRM') . ': ' . ts('show Activity tab');
    $permissions['show CiviEvent menu'] = ts('CiviEvent') . ': ' . ts('show CiviEvent menu');
    $permissions['show CiviMember menu'] = ts('CiviMember') . ': ' . ts('show CiviMember menu');
    $permissions['show toegangsgegevens tab'] = ts('CiviCRM') . ': ' . ts('show Toegangsgegevens tab');
    $permissions['hide local groups'] = ts('CiviCRM') . ': ' . ts('Hide local groups');
    $permissions['access all contacts (view)'] = array(ts('CiviCRM') . ': ' . ts('SP - View all contacts'), ts('This is a SP specific permission to override the core view all contacts permission. View ANY CONTACT in the CiviCRM database, export contact info and perform activities such as Send Email, Phone Call, etc.'));
    $permissions['access all contacts (edit)'] = array(ts('CiviCRM') . ': ' . ts('SP - Edit all contacts'), ts('This is a SP specific permission to override the core view all contacts permission. View, Edit and Delete ANY CONTACT in the CiviCRM database; Create and edit relationships, tags and other info about the contacts'));
    $permissions['CiviMail use from default'] = ts('CiviMail') . ': ' . ts('use default from addresses');
    $permissions['CiviMail use from afdeling'] = ts('CiviMail') . ': ' . ts('use afdeling from addresses');
    $permissions['CiviMail use from personal'] = ts('CiviMail') . ': ' . ts('use personal from address');
    $permissions['CiviMail access send to test group'] = ts('CiviCRM') . ': ' . ts('allow sending test mails to groups');
    $permissions['access CiviMail reports'] = ts('CiviReport') . ': ' . ts('access CiviMail reports');
    $permissions['access local reports'] = ts('CiviReport') . ': ' . ts('access local reports');
    $permissions['restrict activities'] = ts('CiviCRM') . ': ' . ts('restrict activities');
    $permissions['access to all files'] = ts('CiviCRM') . ': ' . ts('Access to all files and folders');
    $permissions['access to update messagetemplates'] = ts('CiviCRM') . ': ' . ts('add or update message templates');
    $permissions['access to custom group search'] = ts('CiviCRM') . ': ' . ts('Access to Inclusive/Exclusive group search');
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
<?php

/**
 * Afsplitsing van nl.sp.geostelsel met nieuwe functies
 * om CiviCRM beter af te schermen voor SP-afdelingsgebruikers,
 * en juist een aantal kleine extra knopjes toe te voegen.
 */

require_once 'accesscontrol.civix.php';



function accesscontrol_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  if ($entity == 'activity' and $action == 'create') {
    //check contacts in activity create
    $allContactsAllowed = true;
    foreach($params['target_contact_id'] as $cid) {
      if (!CRM_Contact_BAO_Contact_Permission::allow($cid, CRM_Core_Permission::VIEW)) {
        $allContactsAllowed = false;
      }
    }
    foreach($params['assignee_contact_id'] as $cid) {
      if (!CRM_Contact_BAO_Contact_Permission::allow($cid, CRM_Core_Permission::VIEW)) {
        $allContactsAllowed = false;
      }
    }
    foreach($params['source_contact_id'] as $cid) {
      if (!CRM_Contact_BAO_Contact_Permission::allow($cid, CRM_Core_Permission::VIEW)) {
        $allContactsAllowed = false;
      }
    }
    if ($allContactsAllowed) {
      $params['check_permissions'] = false;
    }
  }
}

/**
 * Implementation of hook_civicrm_aclWhereClause
 * Voegt where-clause / restricties toe aan alle queries van deze gebruiker.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_aclWhereClause
 */
function accesscontrol_civicrm_aclWhereClause($type, &$tables, &$whereTables, &$contactID, &$where) {
  CRM_Accesscontrol_Acl::aclWhereClause($type, $tables, $whereTables, $contactID, $where);
}

/**
 * Implementation of hook_civicrm_aclGroup
 * Beperkt de groepen die voor deze gebruiker zichtbaar zijn.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_aclGroup
 */
function accesscontrol_civicrm_aclGroup($type, $contactID, $tableName, &$allGroups, &$currentGroups) {
  CRM_Accesscontrol_Acl::aclGroupList($type, $contactID, $tableName, $allGroups, $currentGroups);
}

/**
 * Implementation of hook_civicrm_tabs
 * Geeft een aantal tabs op het contactoverzicht eigen permissies.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tabs
 */
function accesscontrol_civicrm_tabs(&$tabs, $contactID) {
  CRM_Accesscontrol_UI::restrictTabs($tabs, $contactID);
}

/**
 * Implementation of hook_civicrm_optionValues
 * Verwijdert voor afdelingsgebruikers de standaard-afzendadressen uit CiviMail.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_optionValues
 */
function accesscontrol_civicrm_optionValues(&$options, $name) {
  if ($name == 'from_email_address') {
    CRM_Accesscontrol_CiviMail_FromMailAddresses::optionValues($options, $name);
  }
}

/**
 * Implementation of hook_civicrm_permission
 * Voegt extra permissies toe die gebruikt worden door deze extensie.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 */
function accesscontrol_civicrm_permission(&$permissions) {
  CRM_Accesscontrol_Config::singleton()->getExtraPermissions($permissions);
}

/**
 * Implementation of hook_civicrm_pageRun
 * Check permissions for specific pages.
 * + Link naar de webforms om een wijziging in contactgegevens/relatie door te geven.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pageRun
 */
function accesscontrol_civicrm_pageRun(&$page) {
  CRM_Accesscontrol_UI::restrictPages($page);
  CRM_Accesscontrol_UI::addContactPageLink($page);
  CRM_Accesscontrol_GroupContactView::allowAddToGroup($page);
  CRM_Accesscontrol_SearchCustomGroup::pageRun($page);
}

/**
 * Implementation of hook_civicrm_buildForm
 * Check permissions for specific forms.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 */
function accesscontrol_civicrm_buildForm($formName, &$form) {
  CRM_Accesscontrol_UI::restrictForms($formName, $form);
  CRM_Accesscontrol_MessageTemplates_PermissionToChangeTemplate::restrictForm($formName, $form);
  CRM_Accesscontrol_SearchCustomGroup::buildForm($formName, $form);
}

/**
 * Implementation of hook_civicrm_navigationMenu
 * Changes permissions for menu items in order to hide Evenementen / Lidmaatschappen.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function accesscontrol_civicrm_navigationMenu(&$params) {
  CRM_Accesscontrol_UI::modifyMenu($params);
}

/**
 * Implementation of hook_civicrm_enable
 * Controleert of nl.sp.geostelsel geinstalleerd is.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function accesscontrol_civicrm_enable() {

  // Check if geostelsel extension is installed
  $manager = CRM_Extension_System::singleton()->getManager();
  if ($manager->getStatus('nl.sp.geostelsel') != $manager::STATUS_INSTALLED) {
    throw new Exception('This extension requires nl.sp.geostelsel to be installed.');
  }

  // Run installer
  require_once __DIR__ . '/CRM/Accesscontrol/Installer.php'; // We zitten nog niet in de autoloader
  $installer = CRM_Accesscontrol_Installer::singleton();
  $installer->runInstall();

  // Default install/enable
  _accesscontrol_civix_civicrm_enable();
}


/** ----- Overige hooks zijn Civix-standaard ----- */

/**
 * Implementation of hook_civicrm_config
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function accesscontrol_civicrm_config(&$config) {
  _accesscontrol_civix_civicrm_config($config);

  CRM_Accesscontrol_Filesystem::config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 * @param $files array(string)
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function accesscontrol_civicrm_xmlMenu(&$files) {
  _accesscontrol_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function accesscontrol_civicrm_install() {

  _accesscontrol_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function accesscontrol_civicrm_uninstall() {
  _accesscontrol_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_disable
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function accesscontrol_civicrm_disable() {
  _accesscontrol_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function accesscontrol_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _accesscontrol_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function accesscontrol_civicrm_managed(&$entities) {
  _accesscontrol_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 * Generate a list of case-types
 * Note: This hook only runs in CiviCRM 4.4+.
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function accesscontrol_civicrm_caseTypes(&$caseTypes) {
  _accesscontrol_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function accesscontrol_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _accesscontrol_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

<?php

require_once 'accesscontrol.civix.php';

/**
 * Implementatio of hook__civicrm_tabs
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tabs
 */
function accesscontrol_civicrm_tabs(&$tabs, $contactID) {
  //hide tab Tags if user has no permission to edit the contact
  if (!CRM_Core_Permission::check('view contact tag tab')) {
    foreach($tabs as $tab_id => $tab) {
      if ($tab['id'] == 'tag') {
        unset($tabs[$tab_id]);
      }
    }
  }
}

function accesscontrol_civicrm_permission(&$permissions) {
  $permissions['view contact tag tab'] = ts('CiviCRM').': '.ts('View contact tags on summary');
}

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function accesscontrol_civicrm_config(&$config) {
  _accesscontrol_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function accesscontrol_civicrm_xmlMenu(&$files) {
  _accesscontrol_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function accesscontrol_civicrm_install() {
  _accesscontrol_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function accesscontrol_civicrm_uninstall() {
  _accesscontrol_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function accesscontrol_civicrm_enable() {
  _accesscontrol_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function accesscontrol_civicrm_disable() {
  _accesscontrol_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function accesscontrol_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _accesscontrol_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function accesscontrol_civicrm_managed(&$entities) {
  _accesscontrol_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function accesscontrol_civicrm_caseTypes(&$caseTypes) {
  _accesscontrol_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function accesscontrol_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _accesscontrol_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

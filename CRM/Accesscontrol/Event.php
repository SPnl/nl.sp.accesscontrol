<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Accesscontrol_Event {

  /**
   * Delegation of hook_civicrm_aclGroup
   *
   * @param $type
   * @param $contactID
   * @param $tableName
   * @param $allGroups
   * @param $currentGroups
   */
  public static function aclGroup($type, $contactID, $tableName, &$allGroups, &$currentGroups) {
    self::aclEvents($type, $contactID, $tableName, $allGroups, $currentGroups);
    self::aclEventCustomGroups($type, $contactID, $tableName, $allGroups, $currentGroups);
  }

  public static function buildForm($formName, &$form) {
    if ($formName != 'CRM_Event_Form_ManageEvent_EventInfo') {
      return;
    }
    if (!CRM_Core_Permission::check('access CiviEvent')) {
      return;
    }
    if (CRM_Core_Permission::check('edit all events')) {
      return;
    }

    $afdeling_custom_group_id = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'event_afdeling', 'extends' => 'Event', 'return' => 'id'));
    $contact_id_field_id = civicrm_api3('CustomField', 'getvalue', array('name' => 'contact_id', 'custom_group_id' => $afdeling_custom_group_id, 'return' => 'id'));
    $groupTree = $form->getVar('_groupTree');
    if (isset($groupTree[$afdeling_custom_group_id]) && isset($groupTree[$afdeling_custom_group_id]['fields'][$contact_id_field_id])) {
      $elementName = $groupTree[$afdeling_custom_group_id]['fields'][$contact_id_field_id]['element_name'];
      if ($form->elementExists($elementName)) {
        $element = $form->getElement($elementName);
        $form->addRule($element->getName(), ts('%1 is a required field.', array(1 => $element->getLabel())), 'required');
      }
    }
  }

  /**
   * Load a set of Afdelingen/Regio's/Provincies for the field afdeling on Events.
   *
   * @param $customFieldID
   * @param $options
   * @param $detailedFormat
   * @throws \CiviCRM_API3_Exception
   */
  public static function optionValues($customFieldID, &$options, $detailedFormat) {
    $afdeling_custom_group_id = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'event_afdeling', 'extends' => 'Event', 'return' => 'id'));
    $contact_id_field_id = civicrm_api3('CustomField', 'getvalue', array('name' => 'contact_id', 'custom_group_id' => $afdeling_custom_group_id, 'return' => 'id'));
    if ($customFieldID != $contact_id_field_id) {
      return;
    }

    $newOptions = array();
    $provincies = civicrm_api3('Contact', 'get', array('contact_sub_type' => 'SP_Provincie', 'check_permissions' => 1));
    foreach($provincies['values'] as $cid => $contact) {
      $newOptions[$cid] = $contact['display_name'];
    }

    $regios = civicrm_api3('Contact', 'get', array('contact_sub_type' => 'SP_Regio', 'check_permissions' => 1));
    foreach($regios['values'] as $cid => $contact) {
      $newOptions[$cid] = $contact['display_name'];
    }

    $afdelingen = civicrm_api3('Contact', 'get', array('contact_sub_type' => 'SP_Afdeling', 'check_permissions' => 1));
    foreach($afdelingen['values'] as $cid => $contact) {
      $newOptions[$cid] = $contact['display_name'];
    }


    foreach($newOptions as $id => $label) {
      if ( $detailedFormat ) {
        $options[$id] = array(
          'id' => $id,
          'value' => $id,
          'label' => $label,
        );
      } else {
        $options[$id] = $label;
      }
    }
  }

  /**
   * When user has access to CiviCRM but not to edit all events
   * then show the custom group Afdeling
   *
   * @param $type
   * @param $contactID
   * @param $tableName
   * @param $allGroups
   * @param $currentGroups
   */
  private static function aclEventCustomGroups($type, $contactID, $tableName, &$allGroups, &$currentGroups) {
    if ($tableName != 'civicrm_custom_group') {
      return;
    }

    if (!CRM_Core_Permission::check('access CiviEvent')) {
      return;
    }
    if (CRM_Core_Permission::check('edit all events')) {
      return;
    }

    $afdeling_custom_group_id = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'event_afdeling', 'extends' => 'Event', 'return' => 'id'));
    if (isset($allGroups[$afdeling_custom_group_id]) && !in_array($afdeling_custom_group_id, $currentGroups)) {
      $currentGroups[] = $afdeling_custom_group_id;
    }
  }

  /**
   * When user has access to CiviCRM but not to edit all events
   * then only show his/her own events
   *
   * @param $type
   * @param $contactID
   * @param $tableName
   * @param $allGroups
   * @param $currentGroups
   */
  private static function aclEvents($type, $contactID, $tableName, &$allGroups, &$currentGroups) {
    if ($tableName != 'civicrm_event') {
      return;
    }

    if (!CRM_Core_Permission::check('access CiviEvent')) {
      return;
    }
    if (CRM_Core_Permission::check('edit all events')) {
      return;
    }

    $createdEvents = array();
    $session = CRM_Core_Session::singleton();
    if ($userID = $session->get('userID')) {
      $createdEvents = array_keys(CRM_Event_PseudoConstant::event(NULL, TRUE, "created_id={$userID}"));
    }
    $currentGroups = $createdEvents;

    $afdeling_custom_group_id = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'event_afdeling', 'extends' => 'Event', 'return' => 'id'));
    $contact_id_field_id = civicrm_api3('CustomField', 'getvalue', array('name' => 'contact_id', 'custom_group_id' => $afdeling_custom_group_id, 'return' => 'id'));
    $contacts = array();
    self::optionValues($contact_id_field_id, $contacts, false);
    $contactIDs = array_keys($contacts);
    $dao = CRM_Core_DAO::executeQuery("SELECT entity_id FROM `civicrm_value_event_afdeling` WHERE `contact_id` IN (".implode(",", $contactIDs).")");
    while($dao->fetch()) {
      if (!in_array($dao->entity_id, $currentGroups)) {
        $currentGroups[] = $dao->entity_id;
      }
    }
  }

}
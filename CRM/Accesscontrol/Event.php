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

    $cfsp = CRM_Spgeneric_CustomField::singleton();
    $afdeling_custom_group_id = $cfsp->getGroupId('event_afdeling');
    $contact_id_field_id = $cfsp->getFieldId('event_afdeling', 'contact_id');
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
   * @param $customFieldID
   * @param $options
   * @param $detailedFormat
   * @throws \CiviCRM_API3_Exception
   */
  public static function optionValues($customFieldID, &$options, $detailedFormat) {

    // Changed CustomField call + tried to refactor contact call into one call for performance reasons
    // but apparently 4.6 doesn't properly support the ['IN' => []] syntax yet...

    $cfsp = CRM_Spgeneric_CustomField::singleton();
    $contact_id_field_id = $cfsp->getFieldId('event_afdeling', 'contact_id');
    if ($customFieldID != $contact_id_field_id) {
      return;
    }

    foreach (['SP_Afdeling', 'SP_Regio', 'SP_Provincie'] as $sporg) {
      $res = civicrm_api3('Contact', 'get', [
        'contact_sub_type'  => $sporg,
        'return'            => 'id,display_name',
        'option.limit'      => '1000',
        'check_permissions' => 1,
      ]);
      foreach ($res['values'] as $cid => $contact) {
        if ($detailedFormat) {
          $options[$cid] = [
            'id'    => $cid,
            'value' => $cid,
            'label' => $contact['display_name'],
          ];
        } else {
          $options[$cid] = $contact['display_name'];
        }
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

    $afdeling_custom_group_id = CRM_Spgeneric_CustomField::singleton()->getGroupId('event_afdeling');
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

    $contact_id_field_id = CRM_Spgeneric_CustomField::singleton()->getFieldId('event_afdeling', 'contact_id');
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
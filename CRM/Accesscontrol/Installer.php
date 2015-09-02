<?php

class CRM_Accesscontrol_Installer {

  private static $singleton;

  /**
   * Returns instance
   * @return CRM_Accesscontrol_Installer
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new self;
    }
    return self::$singleton;
  }

  /**
   * Install script. Adds activities, sets activity filters
   */
  public function runInstall() {

    $this->createActivityType('Wijziging adres door afdeling', 1);
    $this->createActivityType('Wijziging relatie door afdeling', 1);
    $this->createActivityType('Nieuwe relatie', 1);
    $this->createActivityType('Relatie beëindigd', 1);
    $this->createActivityType('Wijziging doorgeven aan administratie', 0);

    $this->filterActivityTypes(['Aan bestand toegevoegd','leden_telling', 'tribune_bezorging', 'kwartaal_bijdrage', 'Account SPnet', 'odoo_communication', 'payment_reminder', 'Opzegging via website', 'Wijziging adres via website', 'Aanmelding via website', 'FinanciÃ«le wijziging via website', 'Wijziging interesses via website', 'address_change', 'Documenthistorie']);
  }

  /**
   * Create activity type
   * @param string $name Name
   * @param int $filter Filter / don't show in option lists
   * @return int|bool Activity type ID or false
   * @throws \CiviCRM_API3_Exception API Exception
   */
  private function createActivityType($name, $filter = 0) {

    $activity_option_group = civicrm_api3('OptionGroup', 'getvalue', ['return' => 'id', 'name' => 'activity_type']);

    try {
      $activity = civicrm_api3('OptionValue', 'getsingle', ['option_group_id' => $activity_option_group, 'name' => $name]);
      return $activity['value'];
    } catch (\CiviCRM_API3_Exception $e) {
      // Does not exist yet
    }

    $ret = civicrm_api3('OptionValue', 'create', [
      'name'            => $name,
      'label'           => $name,
      'filter'          => $filter,
      'option_group_id' => $activity_option_group,
    ]);
    if ($ret && !$ret['is_error']) {
      return $ret['id'];
    }

    return FALSE;
  }

  private function filterActivityTypes($names = []) {

    $activity_option_group = civicrm_api3('OptionGroup', 'getvalue', ['return' => 'id', 'name' => 'activity_type']);

    foreach ($names as $name) {

      try {
        $activity = civicrm_api3('OptionValue', 'getsingle', ['option_group_id' => $activity_option_group, 'name' => $name]);

        $ret = civicrm_api3('OptionValue', 'create', [
          'id'     => $activity['id'],
          'filter' => 1,
        ]);
      } catch (\CiviCRM_API3_Exception $e) {
        // Does not exist, so we don't have to filter it
      }
    }
  }
}
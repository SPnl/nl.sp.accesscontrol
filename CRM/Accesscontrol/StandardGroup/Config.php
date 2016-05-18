<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Accesscontrol_StandardGroup_Config {

  private static $singleton;

  private $custom_group;

  private $standaard_afdelingsgroep;

  private function __construct() {
    $this->custom_group = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'group_settings'));
    $this->standaard_afdelingsgroep = civicrm_api3('CustomField', 'getsingle', array('name' => 'standaard_afdelingsgroep', 'custom_group_id' => $this->custom_group['id']));
  }

  /**
   * @return \CRM_Accesscontrol_StandardGroup_Config
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Accesscontrol_StandardGroup_Config();
    }
    return self::$singleton;
  }

  public function getCustomGroup($key='id') {
    return $this->custom_group[$key];
  }

  public function getStandardAfdelingsGroep($key='id') {
    return $this->standaard_afdelingsgroep[$key];
  }

}
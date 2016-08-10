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
    $cfsp = CRM_Spgeneric_CustomField::singleton();
    $this->custom_group = $cfsp->getGroupByName('group_settings');
    $this->standaard_afdelingsgroep = $cfsp->getField('group_settings', 'standaard_afdelingsgroep');
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
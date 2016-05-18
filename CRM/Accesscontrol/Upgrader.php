<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Accesscontrol_Upgrader extends CRM_Accesscontrol_Upgrader_Base {

  public function install() {
    $this->executeCustomDataFile('xml/event_afdeling.xml');
    $this->executeCustomDataFile('xml/group_settings.xml');
  }


  public function upgrade_1001() {
    $this->executeCustomDataFile('xml/event_afdeling.xml');
    return TRUE;
  }

  public function upgrade_1002() {
    $this->executeCustomDataFile('xml/group_settings.xml');
    return true;
  }

}

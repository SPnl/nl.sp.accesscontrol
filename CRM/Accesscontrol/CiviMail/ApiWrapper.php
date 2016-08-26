<?php
/**
 * @author Alain Benbassat (CiviCooP) <alain.benbassat@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Accesscontrol_CiviMail_APIWrapper implements API_Wrapper {

  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * limit the list of "from"-addresses
   * @param array $apiRequest
   * @param array $result
   *
   * @return array
   *   modified $result
   */
  public function toApiOutput($apiRequest, $result) {
    if (array_key_exists('option_group_id', $apiRequest['params']) && ($from_email_address_id=$this->validateOptionGroupIdForFromEmailAddress($apiRequest['params']['option_group_id']))) {
      CRM_Accesscontrol_CiviMail_FromMailAddresses::checkApiOutput($result['values']);
      $result['count'] = count($result['values']);
    }
    
    return $result;
  }

  protected function validateOptionGroupIdForFromEmailAddress($option_group_id) {
    try {
      $from_email_address_id = civicrm_api3('OptionGroup', 'getvalue', array(
        'name' => 'from_email_address',
        'return' => 'id'
      ));
      if ($from_email_address_id == $option_group_id) {
        return $from_email_address_id;
      }
    } catch (Exception $e) {
      //do nothing
    }
    return false;
  }

}

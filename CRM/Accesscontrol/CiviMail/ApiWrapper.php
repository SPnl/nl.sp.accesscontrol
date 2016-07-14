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
    // check if the option group "from_email_address" (id = 31) is requested
    if (array_key_exists('option_group_id', $apiRequest['params']) && $apiRequest['params']['option_group_id'] == 31) {
      // get the allowed e-mail addresses
      $options = array();
      CRM_Accesscontrol_CiviMail_FromMailAddresses::optionValues($options, 'from_email_address');
      
	  // store them in the results array
	  $fromAddresses = array();
	  $i = 0;
	  foreach ($options as $k => $v) {
	    $i++;
	    $addr = array(
	      'id' => $i,
	      'is_active' => 1,
	      'label' => $v,
	      'name' => $v,
	    );
	    
	    $fromAddresses[] = $addr;
	  }
	  
	  $result['values'] = $fromAddresses;
	  $result['count'] = $i;
    }
    
    return $result;
  }

}

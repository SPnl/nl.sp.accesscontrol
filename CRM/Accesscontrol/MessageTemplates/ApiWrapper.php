<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Accesscontrol_MessageTemplates_ApiWrapper implements API_Wrapper {

  /**
   * Interface for interpreting api input.
   *
   * @param array $apiRequest
   *
   * @return array
   *   modified $apiRequest
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * Reset the list with templates when a user has no permission to update message templates.
   *
   * Use this only with the api action get and getsingle
   *
   * @param array $apiRequest
   * @param array $result
   *
   * @return array
   *   modified $result
   */
  public function toApiOutput($apiRequest, $result) {
    if (!CRM_Core_Permission::check('access to update messagetemplates') && $apiRequest['action'] == 'get') {
      $result['values'] = array();
      $result['count'] = '0';
    } elseif (!CRM_Core_Permission::check('access to update messagetemplates') && $apiRequest['action'] == 'getsingle') {
      $result['count'] = 0;
      return civicrm_api3_create_error("Expected one " . $apiRequest['entity'] . " but found " . $result['count'], array('count' => $result['count']));
    }
    return $result;
  }

  public static function validActions() {
    return array(
      'get',
      'getsingle',
    );
  }

}
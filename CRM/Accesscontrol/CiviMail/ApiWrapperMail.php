<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Accesscontrol_CiviMail_ApiWrapperMail implements API_Wrapper {

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
    $values = array();
    $allowedMailingIds = CRM_Mailing_BAO_Mailing::mailingACLIDs();
    //var_dump($allowedMailingIds);
    foreach($result['values'] as $mid => $mailing) {
      if (in_array($mailing['id'], $allowedMailingIds)) {
        $values[$mid] = $mailing;
      }
    }
    $result['values'] = $values;
    $result['count'] = count($result['values']);
    return $result;
  }
}

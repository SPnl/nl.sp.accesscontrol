<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Accesscontrol_SearchCustomGroup {

  /**
   * Remove the custom inclusive/exclusive search from the custom searches when
   * the user has not access to see the custom group search.
   *
   * @param $page
   */
  public static function pageRun(&$page) {
    if (CRM_Core_Permission::check('access to custom group search')) {
      return;
    }

    if ($page instanceof CRM_Contact_Page_CustomSearch) {
      $sql = "
SELECT v.value
FROM   civicrm_option_group g,
       civicrm_option_value v
WHERE  v.option_group_id = g.id
AND    g.name = 'custom_search'
AND    v.is_active = 1 AND v.label = 'CRM_Contact_Form_Search_Custom_Group'
ORDER By  v.weight
";
      $inclusiefExclusiefZoeken = CRM_Core_DAO::singleValueQuery($sql);
      $options = $page::info();
      unset($options[$inclusiefExclusiefZoeken]);
      $page->assign('rows', $options);
    }
  }

  /**
   * Redirect and display an error message when a user is not allowed
   * to see the custom inclusive/exclusive search.
   *
   * @param $formName
   * @param $form
   */
  public static function buildForm($formName, &$form) {
    if (CRM_Core_Permission::check('access to custom group search')) {
      return;
    }

    if ($formName == 'CRM_Contact_Form_Search_Custom') {
      $customSearchClass = $form->getVar('_customSearchClass');
      if ($customSearchClass == 'CRM_Contact_Form_Search_Custom_Group') {
        $session = CRM_Core_Session::singleton();
        $session->setStatus(ts('You don\'t have access to this custom search'), 'error');
        $userContext = $session->popUserContext();
        if (empty($userContext)) {
          $userContext = CRM_Utils_System::url('civicrm');
        }
        CRM_Utils_System::redirect($userContext);
      }

    }
  }

}
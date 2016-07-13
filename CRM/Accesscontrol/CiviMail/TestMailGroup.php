<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Accesscontrol_CiviMail_TestMailGroup {
  /**
   * When the user has no access to send test mail to group freeze the element
   * in the form.
   */
  public static function hideBlock($page) {
    // check if it's the angular mailing page
    // NOTE: there's no way to check it's the mailing page???
    $pageName = $page->getVar('_name');
    if ($pageName == 'Civi\Angular\Page\Main') {
      // hide the access to the test group, if user is not allowed to do this
      if (!CRM_Core_Permission::check('CiviMail access send to test group')) {
        CRM_Core_Resources::singleton()->addScriptFile('nl.sp.accesscontrol', 'js/customizeMailingForm.js', 800);
      }
    }
  }
}

<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Accesscontrol_CiviMail_TestMailGroup {

  /**
   * When the user has no access to send test mail to group freeze the element
   * in the form.
   *
   * @param $formName
   * @param $form
   */
  public static function buildForm($formName, &$form) {
    if ($formName == 'CRM_Mailing_Form_Test' && !CRM_Core_Permission::check('CiviMail access send to test group')) {
      if ($form->elementExists('test_group')) {
        $element = $form->getElement('test_group');
        $element->freeze();
        $element->setLabel('');
      }
    }
  }

  /**
   * When the user has no access to send test mail to group remove the fields from
   * the UI. This done by adding jQuery which removes the elements from the DOM.
   *
   * @param $content
   * @param $context
   * @param $tplName
   * @param $object
   */
  public static function alterContent(  &$content, $context, $tplName, &$object ) {
    if ($object instanceof CRM_Mailing_Form_Test && !CRM_Core_Permission::check('CiviMail access send to test group')) {
      $template = CRM_Core_Smarty::singleton();
      $content .= $template->fetch('CRM/Mailing/Form/Test/remove_test_group_js.tpl');
    }
  }

}
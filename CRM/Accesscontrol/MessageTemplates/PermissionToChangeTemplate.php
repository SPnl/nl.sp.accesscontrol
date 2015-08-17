<?php

class CRM_Accesscontrol_MessageTemplates_PermissionToChangeTemplate {

  public static function restrictForm($formName, CRM_Core_Form &$form) {
    if (!self::restrictionInPlace($formName)) {
      return;
    }

    /**
     * Set message template fields to readonly and disabled
     */
    $form->getElement('updateTemplate')->setAttribute('readonly', true);
    $form->getElement('updateTemplate')->setAttribute('disabled', true);
    $form->getElement('saveTemplate')->setAttribute('readonly', true);
    $form->getElement('saveTemplate')->setAttribute('disabled', true);
    $form->getElement('saveTemplate')->setAttribute('onclick', ''); //also remove the onclick handler
    $form->getElement('saveTemplateName')->setAttribute('readonly', true);
    $form->getElement('saveTemplateName')->setAttribute('disabled', true);


    /*
     * If the fields get submitted reset their submitted values
     */
    if (!empty($form->_submitValues['saveTemplate'])) {
      $form->_submitValues['saveTemplate'] = '';
    }
    if (!empty($form->_submitValues['updateTemplate'])) {
      $form->_submitValues['updateTemplate'] = '';
    }
    if (!empty($form->_submitValues['saveTemplateName'])) {
      $form->_submitValues['saveTemplateName'] = '';
    }
  }

  protected static function restrictionInPlace($formName) {
    if (CRM_Core_Permission::check('access to add or update message templates')) {
      return false;
    }

    if ($formName != 'CRM_Contact_Form_Task_Email' && $formName != 'CRM_Contact_Form_Task_PDF' && $formName != 'CRM_Mailing_Form_Upload') {
      return false;
    }

    return true;
  }

}
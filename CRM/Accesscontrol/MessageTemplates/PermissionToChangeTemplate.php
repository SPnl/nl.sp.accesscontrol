<?php

class CRM_Accesscontrol_MessageTemplates_PermissionToChangeTemplate {

  public static function restrictForm($formName, CRM_Core_Form &$form) {
    if (!self::restrictionInPlace($formName)) {
      return;
    }

    /**
     * Set message template fields to readonly and disabled
     */
    if ($form->getElement('updateTemplate')) {
      $form->getElement('updateTemplate')->setAttribute('readonly', TRUE);
      $form->getElement('updateTemplate')->setAttribute('disabled', TRUE);
    }
    if ($form->getElement('saveTemplate')) {
      $form->getElement('saveTemplate')->setAttribute('readonly', TRUE);
      $form->getElement('saveTemplate')->setAttribute('disabled', TRUE);
      $form->getElement('saveTemplate')->setAttribute('onclick', ''); //also remove the onclick handler
    }
    if ($form->getElement('saveTemplateName')) {
      $form->getElement('saveTemplateName')->setAttribute('readonly', TRUE);
      $form->getElement('saveTemplateName')->setAttribute('disabled', TRUE);
    }


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
    if (CRM_Core_Permission::check('access to update messagetemplates')) {
      return false;
    }

    if ($formName != 'CRM_Contact_Form_Task_Email' && $formName != 'CRM_Contact_Form_Task_PDF' && $formName != 'CRM_Mailing_Form_Upload') {
      return false;
    }

    return true;
  }
  
  public static function getRestrictedTemplates($all=TRUE, $isSMS=FALSE) {
    if (CRM_Core_Permission::check('access to update messagetemplates')) {
      $msgTpls = array();

      $messageTemplates = new CRM_Core_DAO_MessageTemplate();
      $messageTemplates->is_active = 1;
      $messageTemplates->is_sms = $isSMS;

      if (!$all) {
        $messageTemplates->workflow_id = 'NULL';
      }
      $messageTemplates->find();
      while ($messageTemplates->fetch()) {
        $msgTpls[$messageTemplates->id] = $messageTemplates->msg_title;
      }
      asort($msgTpls);
      return $msgTpls;
    }
    return array();


  }

}
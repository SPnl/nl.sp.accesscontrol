<?php

class CRM_Accesscontrol_CiviMail_FromMailAddresses {
    /**
     * Change the option list with from values from the afdelingen, regio's en provincies
     * @param $options
     * @param $name
     */
    public static function optionValues(&$options, $name) {
        if ($name != 'from_email_address') {
            return;
        }

        if (!CRM_Core_Permission::check('CiviMail use from default')) {
            // unset default options
            $options = [];
        }

        if(CRM_Core_Permission::check('CiviMail use from afdeling')) {
            // add afdelingen/regio's/provincies
            self::getFromContacts($options);
        }

        if(CRM_Core_Permission::check('CiviMail use from personal')) {
            $session = CRM_Core_Session::singleton();
            $contactID = $session->get('userID');

            // add personal email
            $config = CRM_Accesscontrol_Config::singleton();
            if(!$config->isClassInStack('CRM_Contact_Form_Task_Email') && $contactID) {
                $contactEmails = CRM_Core_BAO_Email::allEmails($contactID);
                $fromDisplayName = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $contactID, 'display_name');
                if (count($contactEmails) > 0) {
                    foreach ($contactEmails as $email) {
                      $options['contact_id_'.$contactID] = '"' . $fromDisplayName . '" <' . $email['email'] . '>';
                    }
                }
            }
        }
    }

    public static function checkApiOutput(&$values) {
      if (!CRM_Core_Permission::check('CiviMail use from default')) {
        // unset default options
        $values = [];
      }

      $extra_addresses = array();
      if(CRM_Core_Permission::check('CiviMail use from afdeling')) {
        // add afdelingen/regio's/provincies
        self::getFromContacts($extra_addresses);
      }

      if(CRM_Core_Permission::check('CiviMail use from personal')) {
        $session = CRM_Core_Session::singleton();
        $contactID = $session->get('userID');

        // add personal email
        $config = CRM_Accesscontrol_Config::singleton();
        if(!$config->isClassInStack('CRM_Contact_Form_Task_Email') && $contactID) {
          $contactEmails = CRM_Core_BAO_Email::allEmails($contactID);
          $fromDisplayName = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $contactID, 'display_name');
          if (count($contactEmails) > 0) {
            $i = 0;
            foreach ($contactEmails as $email) {
              //$options['contact_id_' . $contactID] = $addr;
              $extra_addresses [] = '"' . $fromDisplayName . '" <' . $email['email'] . '>';
            }
          }
        }
      }

      foreach($extra_addresses as $id => $email) {
        $values[$id] = array(
          'id' => $id,
          'is_active' => 1,
          'label' => $email,
          'name' => $email,
        );
      }
    }

    protected static function getFromContacts(&$options) {
        $sep = CRM_Core_DAO::VALUE_SEPARATOR;
        list($aclFrom, $aclWhere) = CRM_Contact_BAO_Contact_Permission::cacheClause('contact_a');
        $sql = "SELECT contact_a.id as contact_id, contact_a.display_name, e.email
            FROM civicrm_contact contact_a
            INNER JOIN civicrm_email e on contact_a.id = e.contact_id AND e.is_primary = 1
            {$aclFrom}
            WHERE (
              contact_a.contact_sub_type LIKE '%{$sep}SP_Afdeling{$sep}%'
              OR contact_a.contact_sub_type LIKE '%{$sep}SP_Regio{$sep}%'
              OR contact_a.contact_sub_type LIKE '%{$sep}SP_Provincie{$sep}%'
               OR contact_a.contact_sub_type LIKE '%{$sep}SP_Werkgroep{$sep}%'
              )
            AND {$aclWhere}
            ORDER BY contact_a.sort_name";

        $dao = CRM_Core_DAO::executeQuery($sql);

        while ($dao->fetch()) {
            $options['contact_id_' . $dao->contact_id] = '"' . $dao->display_name . '" <' . $dao->email . '>';
        }
    }

}

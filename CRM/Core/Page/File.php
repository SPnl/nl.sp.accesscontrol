<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.6                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2015                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 | 2018-04-11: Jaap Jansma (jaap.jansma@civicoop.org): Issue #1571    |
 |             Added check whether the user is allowed to download    |
 |             the file.                                              |
 |                                                                    |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2015
 * $Id$
 *
 */
class CRM_Core_Page_File extends CRM_Core_Page {

  public function run() {
    $fileName = CRM_Utils_Request::retrieve('filename', 'String', $this);
    $path = CRM_Core_Config::singleton()->customFileUploadDir . $fileName;
    $mimeType = CRM_Utils_Request::retrieve('mime-type', 'String', $this);
    $action = CRM_Utils_Request::retrieve('action', 'String', $this);

    // if we are not providing essential parameter needed for file preview then
    if (empty($fileName) && empty($mimeType)) {
      $eid = CRM_Utils_Request::retrieve('eid', 'Positive', $this, TRUE);
      $fid = CRM_Utils_Request::retrieve('fid', 'Positive', $this, FALSE);
      $id = CRM_Utils_Request::retrieve('id', 'Positive', $this, TRUE);
      $quest = CRM_Utils_Request::retrieve('quest', 'String', $this);

			/**
			 * #1571: added check whether the user is allowed to download the file.
			 */
			// Original code:
      //list($path, $mimeType) = CRM_Core_BAO_File::path($id, $eid, NULL, $quest);
      // changed into:
      try {
      	$config = CRM_Core_Config::singleton();
      	$attachment = civicrm_api3('Attachment', 'getsingle', array('id' => $id, 'check_permissions' =>1));
				$file = civicrm_api3('File', 'getsingle', array('id' => $id, 'check_permissions' =>1));
				$path = $config->customFileUploadDir . $file['uri'];
				$mimeType = $file['mime_type'];
			} catch (Exception $e) {
				CRM_Core_Error::statusBounce('Could not retrieve the file');
			}
			/**
			 * END #1571
			 */
    }

    if (!$path) {
      CRM_Core_Error::statusBounce('Could not retrieve the file');
    }

    $buffer = file_get_contents($path);
    if (!$buffer) {
      CRM_Core_Error::statusBounce('The file is either empty or you do not have permission to retrieve the file');
    }

    if ($action & CRM_Core_Action::DELETE) {
      if (CRM_Utils_Request::retrieve('confirmed', 'Boolean', CRM_Core_DAO::$_nullObject)) {
        CRM_Core_BAO_File::deleteFileReferences($id, $eid, $fid);
        CRM_Core_Session::setStatus(ts('The attached file has been deleted.'), ts('Complete'), 'success');

        $session = CRM_Core_Session::singleton();
        $toUrl = $session->popUserContext();
        CRM_Utils_System::redirect($toUrl);
      }
    }
    else {
      CRM_Utils_System::download(
        CRM_Utils_File::cleanFileName(basename($path)),
        $mimeType,
        $buffer
      );
    }
  }

}

<?php

class CRM_Reports_Form_Report_Toegangen extends CRM_Report_Form {
//	private $debug = 1;

	private $groepAfdelingsgebruikersCiviCRM = -1;
	private $accessTypeOptionGroupID = -1;
	
	function __construct() {
		// get the id of the group "Afdelingsgebruikers CiviCRM"
		try {
			$params = array(
				'title' => 'Afdelingsgebruiker CiviCRM',
			);
			$group = civicrm_api3('Group', 'getsingle', $params);
			$this->groepAfdelingsgebruikersCiviCRM = $group['id'];
		}
		catch (CiviCRM_API3_Exception $e) {
			CRM_Core_Session::setStatus("Kan de groep met naam 'Afdelingsgebruiker CiviCRM' niet vinden.", ts('Error'), 'error');
		}

		// get the id of the option group "access type"
		try {
			$params = array(
				'name' => 'access_type',
			);
			$optionGroup = civicrm_api3('OptionGroup', 'getsingle', $params);
			$this->accessTypeOptionGroupID = $optionGroup['id'];
		}
		catch (CiviCRM_API3_Exception $e) {
			CRM_Core_Session::setStatus("Kan de option group met naam 'access_type' niet vinden.", ts('Error'), 'error');
		}
				
		$this->_columns = array(
			'civicrm_contact' => array(
				'dao' => 'CRM_Contact_DAO_Contact',
				'fields' => array(
					'id' => array(
						'no_display' => TRUE,
						'required' => TRUE,
					),																			
					'sort_name' => array(
						'title' => ts('Contact Name'),
						'required' => TRUE,
						'default' => TRUE,
						'no_repeat' => TRUE,
					),
				),
			),
			'civicrm_value_toegangsgegevens' => array(
				'fields' => array(
					'type' => array(
						'title' => 'Type toegang',
						'required' => TRUE,
						'no_repeat' => TRUE,
						'dbAlias' => 'civicrm_option_value.label'
					),
					'link' => array(
						'no_display' => TRUE,					
						'required' => TRUE,
					),					
				),
			),
			'civicrm_group' => array(
				'fields' => array(
					'title' => array(
						'no_display' => TRUE,
						'required' => TRUE,					
					),
				),
			),
			'civicrm_contact2' => array(
				'dao' => 'CRM_Contact_DAO_Contact',
				'fields' => array(
					'display_name' => array(
						'no_display' => TRUE,
						'required' => TRUE,
					),
				),
			),			
		);
				
		parent::__construct();
	}

	function preProcess() {
		$this->assign('reportTitle', 'Overzicht toegangen');
		parent::preProcess();
	}
	
	function postProcess() {
		// show debug, if selected
		if (isset($this->debug) && $this->debug) {
			$this->beginPostProcess();
			$sql = $this->buildQuery(TRUE);
			echo $sql;
			exit;
		}
		else {
			parent::postProcess();
		}
	}	
	
	function select() {
		$select = $this->_columnHeaders = array();

		foreach ($this->_columns as $tableName => $table) {
			if (array_key_exists('fields', $table)) {
				foreach ($table['fields'] as $fieldName => $field) {
					if (CRM_Utils_Array::value('required', $field) || CRM_Utils_Array::value($fieldName, $this->_params['fields'])) {
						$select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
						$this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
						$this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
					}
				}
			}
		}

		$this->_select = "SELECT " . implode(', ', $select) . " ";
	}	
	
	function from() {
		$this->_from = "
			FROM
				civicrm_contact {$this->_aliases['civicrm_contact']} 
			INNER JOIN
				civicrm_value_toegangsgegevens {$this->_aliases['civicrm_value_toegangsgegevens']}
			ON
				{$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_value_toegangsgegevens']}.entity_id
			INNER JOIN
				civicrm_option_value
			ON
				{$this->_aliases['civicrm_value_toegangsgegevens']}.type = civicrm_option_value.value AND civicrm_option_value.option_group_id = {$this->accessTypeOptionGroupID}
			INNER JOIN
				civicrm_group_contact
			ON
				{$this->_aliases['civicrm_contact']}.id = civicrm_group_contact.contact_id AND civicrm_group_contact.group_id = {$this->groepAfdelingsgebruikersCiviCRM} AND civicrm_group_contact.status = 'Added'
			LEFT OUTER JOIN
				civicrm_group {$this->_aliases['civicrm_group']}
			ON
				{$this->_aliases['civicrm_value_toegangsgegevens']}.group_id = {$this->_aliases['civicrm_group']}.id
			LEFT OUTER JOIN
				civicrm_contact {$this->_aliases['civicrm_contact2']}
			ON
				{$this->_aliases['civicrm_value_toegangsgegevens']}.toegang_tot_contacten_van = {$this->_aliases['civicrm_contact2']}.id
		";
	}
	
	function where() {
		// standard code to retrieve criteria		
		$clauses = array();
		foreach ($this->_columns as $tableName => $table) {
			if (array_key_exists('filters', $table)) {
				foreach ($table['filters'] as $fieldName => $field) {
					$clause = NULL;
					if (CRM_Utils_Array::value('operatorType', $field) & CRM_Report_Form::OP_DATE) {
						$relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
						$from     = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
						$to       = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

						$clause = $this->dateClause($field['name'], $relative, $from, $to, $field['type']);
					}
					else {
						$op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
						if ($op) {
							$clause = $this->whereClause($field,
								$op,
								CRM_Utils_Array::value("{$fieldName}_value", $this->_params),
								CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
								CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
							);
						}
					}

					if (!empty($clause)) {
						$clauses[$fieldName] = $clause;
					}
				}
			}
		}
		
		// make sure contacts are not deleted
		$this->_where = "WHERE {$this->_aliases['civicrm_contact']}.is_deleted = 0 ";
		
		// add other clauses
		if (!empty($clauses)) {
			$this->_where .= " " . implode(' AND ', $clauses);
		}

		// add access rights clauses
		if ($this->_aclWhere) {
			$this->_where .= " AND {$this->_aclWhere} ";
		}

	}

	function orderBy() {
		$this->_orderBy = " ORDER BY 
			{$this->_aliases['civicrm_contact']}.sort_name
			, {$this->_aliases['civicrm_value_toegangsgegevens']}.id
		";
	}	
	
	function alterDisplay(&$rows) {
		foreach ($rows as $rowNum => $row) {
			if (array_key_exists('civicrm_contact_id', $row) && array_key_exists('civicrm_contact_sort_name', $row)) {
				$url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid=" . $row['civicrm_contact_id']);
				$rows[$rowNum]['civicrm_contact_sort_name'] = '<a href="' . $url .'">' . $row['civicrm_contact_sort_name'] . '</a>';				
			}
			
			if ($row['civicrm_value_toegangsgegevens_link'] == 'OR') {
				$prefix = 'Contact die ';
			}
			else {
				$prefix = 'Waarvan de contacten ook ';
			}
			
			if ($row['civicrm_contact2_display_name']) {
				// lid van afdeling
				$rows[$rowNum]['civicrm_value_toegangsgegevens_type'] = $prefix . $row['civicrm_value_toegangsgegevens_type'] . ' ' . $row['civicrm_contact2_display_name'];
			}
			else if ($row['civicrm_group_title']) {
				// lid van groep
				$rows[$rowNum]['civicrm_value_toegangsgegevens_type'] = $prefix . $row['civicrm_value_toegangsgegevens_type'] . ': ' . $row['civicrm_group_title'];
			}
		}
    }
}

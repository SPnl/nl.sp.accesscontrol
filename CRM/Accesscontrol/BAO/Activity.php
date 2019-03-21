<?php

class CRM_Accesscontrol_BAO_Activity {

  /**
   * This function is a wrapper for ajax activity selector
   *
   * @param  array   $params associated array for params record id.
   *
   * @return array   $contactActivities associated array of contact activities
   * @access public
   */
  public static function getContactActivitySelector(&$params) {
    // format the params
    $params['offset']   = ($params['page'] - 1) * $params['rp'];
    $params['rowCount'] = $params['rp'];
    $params['sort']     = CRM_Utils_Array::value('sortBy', $params);
    $params['caseId']   = NULL;
    $context            = CRM_Utils_Array::value('context', $params);

    // get contact activities
    $activities = CRM_Accesscontrol_BAO_Activity::getActivities($params);

    // add total
    $params['total'] = CRM_Accesscontrol_BAO_Activity::getActivitiesCount($params);

    // format params and add links
    $contactActivities = array();

    if (!empty($activities)) {
      $activityStatus = CRM_Core_PseudoConstant::activityStatus();

      // check logged in user for permission
      $page = new CRM_Core_Page();
      CRM_Contact_Page_View::checkUserPermission($page, $params['contact_id']);
      $permissions = array($page->_permission);
      if (CRM_Core_Permission::check('delete activities')) {
        $permissions[] = CRM_Core_Permission::DELETE;
      }

      $mask = CRM_Core_Action::mask($permissions);

      foreach ($activities as $activityId => $values) {
        $contactActivities[$activityId]['activity_type'] = $values['activity_type'];
        $contactActivities[$activityId]['subject'] = $values['subject'];
        if ($params['contact_id'] == $values['source_contact_id']) {
          $contactActivities[$activityId]['source_contact'] = $values['source_contact_name'];
        }
        elseif ($values['source_contact_id']) {
          $contactActivities[$activityId]['source_contact'] = CRM_Utils_System::href($values['source_contact_name'],
            'civicrm/contact/view', "reset=1&cid={$values['source_contact_id']}");
        }
        else {
          $contactActivities[$activityId]['source_contact'] = '<em>n/a</em>';
        }

        if (isset($values['mailingId']) && !empty($values['mailingId'])) {
          $contactActivities[$activityId]['target_contact'] = CRM_Utils_System::href($values['recipients'],
            'civicrm/mailing/report/event',
            "mid={$values['source_record_id']}&reset=1&event=queue&cid={$params['contact_id']}&context=activitySelector");
        }
        elseif (CRM_Utils_Array::value('recipients', $values)) {
          $contactActivities[$activityId]['target_contact'] = $values['recipients'];
        }
        elseif (!$values['target_contact_name']) {
          $contactActivities[$activityId]['target_contact'] = '<em>n/a</em>';
        }
        elseif (!empty($values['target_contact_name'])) {
          $count = 0;
          $contactActivities[$activityId]['target_contact'] = '';
          foreach ($values['target_contact_name'] as $tcID => $tcName) {
            if ($tcID && $count < 5) {
              $contactActivities[$activityId]['target_contact'] .= CRM_Utils_System::href($tcName,
                'civicrm/contact/view', "reset=1&cid={$tcID}");
              $count++;
              if ($count) {
                $contactActivities[$activityId]['target_contact'] .= ";&nbsp;";
              }

              if ($count == 4) {
                $contactActivities[$activityId]['target_contact'] .= "(" . ts('more') . ")";
                break;
              }
            }
          }
        }

        if (empty($values['assignee_contact_name'])) {
          $contactActivities[$activityId]['assignee_contact'] = '<em>n/a</em>';
        }
        elseif (!empty($values['assignee_contact_name'])) {
          $count = 0;
          $contactActivities[$activityId]['assignee_contact'] = '';
          foreach ($values['assignee_contact_name'] as $acID => $acName) {
            if ($acID && $count < 5) {
              $contactActivities[$activityId]['assignee_contact'] .= CRM_Utils_System::href($acName, 'civicrm/contact/view', "reset=1&cid={$acID}");
              $count++;
              if ($count) {
                $contactActivities[$activityId]['assignee_contact'] .= ";&nbsp;";
              }

              if ($count == 4) {
                $contactActivities[$activityId]['assignee_contact'] .= "(" . ts('more') . ")";
                break;
              }
            }
          }
        }

        $contactActivities[$activityId]['activity_date'] = CRM_Utils_Date::customFormat($values['activity_date_time']);
        $contactActivities[$activityId]['status'] = $activityStatus[$values['status_id']];

        // add class to this row if overdue
        $contactActivities[$activityId]['class'] = '';
        if (CRM_Utils_Date::overdue(CRM_Utils_Array::value('activity_date_time', $values))
          && CRM_Utils_Array::value('status_id', $values) == 1
        ) {
          $contactActivities[$activityId]['class'] = 'status-overdue';
        }
        else {
          $contactActivities[$activityId]['class'] = 'status-ontime';
        }

        // build links
        $contactActivities[$activityId]['links'] = '';
        $accessMailingReport = FALSE;
        if (CRM_Utils_Array::value('mailingId', $values)) {
          $accessMailingReport = TRUE;
        }

        $actionLinks = CRM_Activity_Selector_Activity::actionLinks(
          CRM_Utils_Array::value('activity_type_id', $values),
          CRM_Utils_Array::value('source_record_id', $values),
          $accessMailingReport,
          CRM_Utils_Array::value('activity_id', $values)
        );

        $actionMask = array_sum(array_keys($actionLinks)) & $mask;

        $contactActivities[$activityId]['links'] = CRM_Core_Action::formLink($actionLinks,
          $actionMask,
          array(
            'id' => $values['activity_id'],
            'cid' => $params['contact_id'],
            'cxt' => $context,
            'caseid' => CRM_Utils_Array::value('case_id', $values),
          )
        );
      }
    }

    return $contactActivities;
  }

  /**
   * function to get the activity Count
   *
   * @param array   $input            array of parameters
   *    Keys include
   *    - contact_id  int            contact_id whose activities we want to retrieve
   *    - admin       boolean        if contact is admin
   *    - caseId      int            case ID
   *    - context     string         page on which selector is build
   *    - activity_type_id int|string the activity types we want to restrict by
   *
   * @return int   count of activities
   *
   * @access public
   * @static
   */
  static function &getActivitiesCount($input) {
    $input['count'] = TRUE;
    list($sqlClause, $params) = self::getActivitySQLClause($input);

    //filter case activities - CRM-5761
    $components = CRM_Activity_BAO_Activity::activityComponents();
    if (!in_array('CiviCase', $components)) {
      $query = "
   SELECT   COUNT(DISTINCT(tbl.activity_id)) as count
     FROM   ( {$sqlClause} ) as tbl
LEFT JOIN   civicrm_case_activity ON ( civicrm_case_activity.activity_id = tbl.activity_id )
    WHERE   civicrm_case_activity.id IS NULL";
    }
    else {
      $query = "SELECT COUNT(DISTINCT(activity_id)) as count  from ( {$sqlClause} ) as tbl";
    }

    return CRM_Core_DAO::singleValueQuery($query, $params);
  }

  /**
   * function to get the list Activities
   *
   * @param array   $input            array of parameters
   *    Keys include
   *    - contact_id  int            contact_id whose activities we want to retrieve
   *    - offset      int            which row to start from ?
   *    - rowCount    int            how many rows to fetch
   *    - sort        object|array   object or array describing sort order for sql query.
   *    - admin       boolean        if contact is admin
   *    - caseId      int            case ID
   *    - context     string         page on which selector is build
   *    - activity_type_id int|string the activitiy types we want to restrict by
   *
   * @return array (reference)      $values the relevant data object values of open activities
   *
   * @access public
   * @static
   */
  static function &getActivities($input) {
    //step 1: Get the basic activity data
    $bulkActivityTypeID = CRM_Core_OptionGroup::getValue(
      'activity_type',
      'Bulk Email',
      'name'
    );

    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
    $sourceID = CRM_Utils_Array::key('Activity Source', $activityContacts);
    $assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);
    $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);

    $config = CRM_Core_Config::singleton();

    $randomNum = md5(uniqid());
    $activityTempTable = "civicrm_temp_activity_details_{$randomNum}";

    $tableFields = array(
      'activity_id' => 'int unsigned',
      'activity_date_time' => 'datetime',
      'source_record_id' => 'int unsigned',
      'status_id' => 'int unsigned',
      'subject' => 'varchar(255)',
      'source_contact_name' => 'varchar(255)',
      'activity_type_id' => 'int unsigned',
      'activity_type' => 'varchar(128)',
      'case_id' => 'int unsigned',
      'case_subject' => 'varchar(255)',
      'campaign_id' => 'int unsigned',
    );

    $sql = "CREATE TEMPORARY TABLE {$activityTempTable} ( ";
    $insertValueSQL = array();
    // The activityTempTable contains the sorted rows
    // so in order to maintain the sort order as-is we add an auto_increment
    // field; we can sort by this later to ensure the sort order stays correct.
    $sql .= " fixed_sort_order INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,";
    foreach ($tableFields as $name => $desc) {
      $sql .= "$name $desc,\n";
      $insertValueSQL[] = $name;
    }

    // add unique key on activity_id just to be sure
    // this cannot be primary key because we need that for the auto_increment
    // fixed_sort_order field
    $sql .= "
          UNIQUE KEY ( activity_id )
        ) ENGINE=HEAP DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci
        ";

    CRM_Core_DAO::executeQuery($sql);

    $insertSQL = "INSERT INTO {$activityTempTable} (" . implode(',', $insertValueSQL) . " ) ";

    $order = $limit = $groupBy = '';
    $groupBy = " GROUP BY tbl.activity_id ";

    if (!empty($input['sort'])) {
      if (is_a($input['sort'], 'CRM_Utils_Sort')) {
        $orderBy = $input['sort']->orderBy();
        if (!empty($orderBy)) {
          $order = " ORDER BY $orderBy";
        }
      }
      elseif (trim($input['sort'])) {
        $sort = CRM_Utils_Type::escape($input['sort'], 'String');
        $order = " ORDER BY $sort ";
      }
    }

    if (empty($order)) {
      // context = 'activity' in Activities tab.
      $order = (CRM_Utils_Array::value('context', $input) == 'activity') ? " ORDER BY tbl.activity_date_time desc " : " ORDER BY tbl.status_id asc, tbl.activity_date_time asc ";
    }

    if (!empty($input['rowCount']) &&
      $input['rowCount'] > 0
    ) {
      $limit = " LIMIT {$input['offset']}, {$input['rowCount']} ";
    }

    $input['count'] = FALSE;
    list($sqlClause, $params) = self::getActivitySQLClause($input);

    $query = "{$insertSQL}
       SELECT DISTINCT tbl.*  from ( {$sqlClause} )
as tbl ";

    //filter case activities - CRM-5761
    $components = CRM_Activity_BAO_Activity::activityComponents();
    if (!in_array('CiviCase', $components)) {
      $query .= "
LEFT JOIN  civicrm_case_activity ON ( civicrm_case_activity.activity_id = tbl.activity_id )
    WHERE  civicrm_case_activity.id IS NULL";
    }

    $query = $query . $groupBy . $order . $limit;
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    // step 2: Get target and assignee contacts for above activities
    // create temp table for target contacts
    $activityContactTempTable = "civicrm_temp_activity_contact_{$randomNum}";
    $query = "CREATE TEMPORARY TABLE {$activityContactTempTable} (
                activity_id int unsigned, contact_id int unsigned, record_type_id varchar(16),
                 contact_name varchar(255), is_deleted int unsigned, counter int unsigned, INDEX index_activity_id( activity_id ) )
                ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";

    CRM_Core_DAO::executeQuery($query);

    // note that we ignore bulk email for targets, since we don't show it in selector
    $query = "
INSERT INTO {$activityContactTempTable} ( activity_id, contact_id, record_type_id, contact_name, is_deleted )
SELECT     ac.activity_id,
           ac.contact_id,
           ac.record_type_id,
           c.sort_name,
           c.is_deleted
FROM       {$activityTempTable}
INNER JOIN civicrm_activity a ON ( a.id = {$activityTempTable}.activity_id AND a.activity_type_id != {$bulkActivityTypeID} )
INNER JOIN civicrm_activity_contact ac ON ( ac.activity_id = {$activityTempTable}.activity_id )
INNER JOIN civicrm_contact c ON c.id = ac.contact_id

";
    CRM_Core_DAO::executeQuery($query);

    // for each activity insert one target contact
    // if we load all target contacts the performance will suffer a lot for mass-activities;
    $query = "
INSERT INTO {$activityContactTempTable} ( activity_id, contact_id, record_type_id, contact_name, is_deleted, counter )
SELECT     ac.activity_id,
           ac.contact_id,
           ac.record_type_id,
           c.sort_name,
           c.is_deleted,
           count(ac.contact_id)
FROM       {$activityTempTable}
INNER JOIN civicrm_activity a ON ( a.id = {$activityTempTable}.activity_id AND a.activity_type_id = {$bulkActivityTypeID} )
INNER JOIN civicrm_activity_contact ac ON ( ac.activity_id = {$activityTempTable}.activity_id )
INNER JOIN civicrm_contact c ON c.id = ac.contact_id
WHERE ac.record_type_id = %1
GROUP BY ac.activity_id
";
    $params = array(1 => array($targetID, 'Integer'));
    CRM_Core_DAO::executeQuery($query, $params);

    // step 3: Combine all temp tables to get final query for activity selector
    // sort by the original sort order, stored in fixed_sort_order
    $query = "
SELECT     {$activityTempTable}.*,
           {$activityContactTempTable}.contact_id,
           {$activityContactTempTable}.record_type_id,
           {$activityContactTempTable}.contact_name,
           {$activityContactTempTable}.is_deleted,
           {$activityContactTempTable}.counter
FROM       {$activityTempTable}
INNER JOIN {$activityContactTempTable} on {$activityTempTable}.activity_id = {$activityContactTempTable}.activity_id
ORDER BY    fixed_sort_order
        ";

    $dao = CRM_Core_DAO::executeQuery($query);

    //CRM-3553, need to check user has access to target groups.
    $mailingIDs = CRM_Mailing_BAO_Mailing::mailingACLIDs();
    $accessCiviMail = (
      (CRM_Core_Permission::check('access CiviMail')) ||
      (CRM_Mailing_Info::workflowEnabled() &&
        CRM_Core_Permission::check('create mailings'))
    );

    //get all campaigns.
    $allCampaigns = CRM_Campaign_BAO_Campaign::getCampaigns(NULL, NULL, FALSE, FALSE, FALSE, TRUE);
    $values = array();
    while ($dao->fetch()) {
      $activityID = $dao->activity_id;
      $values[$activityID]['activity_id'] = $dao->activity_id;
      $values[$activityID]['source_record_id'] = $dao->source_record_id;
      $values[$activityID]['activity_type_id'] = $dao->activity_type_id;
      $values[$activityID]['activity_type'] = $dao->activity_type;
      $values[$activityID]['activity_date_time'] = $dao->activity_date_time;
      $values[$activityID]['status_id'] = $dao->status_id;
      $values[$activityID]['subject'] = $dao->subject;
      $values[$activityID]['campaign_id'] = $dao->campaign_id;

      if ($dao->campaign_id) {
        $values[$activityID]['campaign'] = $allCampaigns[$dao->campaign_id];
      }

      if (!CRM_Utils_Array::value('assignee_contact_name', $values[$activityID])) {
        $values[$activityID]['assignee_contact_name'] = array();
      }

      if (!CRM_Utils_Array::value('target_contact_name', $values[$activityID])) {
        $values[$activityID]['target_contact_name'] = array();
      }

      // if deleted, wrap in <del>
      if ( $dao->is_deleted ) {
        $dao->contact_name = "<del>{$dao->contact_name}</dao>";
      }

      if ($dao->record_type_id == $sourceID  && $dao->contact_id) {
        $values[$activityID]['source_contact_id'] = $dao->contact_id;
        $values[$activityID]['source_contact_name'] = $dao->contact_name;
      }

      if (!$bulkActivityTypeID || ($bulkActivityTypeID != $dao->activity_type_id)) {
        // build array of target / assignee names
        if ($dao->record_type_id == $targetID && $dao->contact_id) {
          $values[$activityID]['target_contact_name'][$dao->contact_id] = $dao->contact_name;
        }
        if ($dao->record_type_id == $assigneeID && $dao->contact_id) {
          $values[$activityID]['assignee_contact_name'][$dao->contact_id] = $dao->contact_name;
        }

        // case related fields
        $values[$activityID]['case_id'] = $dao->case_id;
        $values[$activityID]['case_subject'] = $dao->case_subject;
      }
      else {
        $values[$activityID]['recipients'] =  ts('(%1 contacts)', array(1 => $dao->counter));
        $values[$activityID]['mailingId'] = false;
        if (
          $accessCiviMail &&
          ($mailingIDs === TRUE || in_array($dao->source_record_id, $mailingIDs))
        ) {
          $values[$activityID]['mailingId'] = true;
        }
      }
    }

    return $values;
  }

  /**
   * function to get the activity sql clause to pick activities
   *
   * @param array   $input            array of parameters
   *    Keys include
   *    - contact_id  int            contact_id whose activities we want to retrieve
   *    - admin       boolean        if contact is admin
   *    - caseId      int            case ID
   *    - context     string         page on which selector is build
   *    - count       boolean        are we interested in the count clause only?
   *    - activity_type_id int|string the activity types we want to restrict by
   *
   * @return int   count of activities
   *
   * @access public
   * @static
   */
  static function getActivitySQLClause($input) {
    $params = array();
    $sourceWhere = $targetWhere = $assigneeWhere = $caseWhere = 1;

    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
    $sourceID = CRM_Utils_Array::key('Activity Source', $activityContacts);
    $assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);
    $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);

    $config = CRM_Core_Config::singleton();
    if (!CRM_Utils_Array::value('admin', $input, FALSE)) {
      $sourceWhere   = ' ac.contact_id = %1 and ac.record_type_id = "'.$targetID.'"';
      $caseWhere     = ' civicrm_case_contact.contact_id = %1 ';

      $params = array(1 => array($input['contact_id'], 'Integer'));
    }

    $commonClauses = array(
      "civicrm_option_group.name = 'activity_type'",
      "civicrm_activity.is_deleted = 0",
      "civicrm_activity.is_current_revision =  1",
      "civicrm_activity.is_test= 0",
    );

    if ($input['context'] != 'activity') {
      $commonClauses[] = "civicrm_activity.status_id = 1";
    }


    if (isset($input['activity_date_relative']) ||
        (!empty($input['activity_date_low']) || !empty($input['activity_date_high']))
    ) {
      list($from, $to) = CRM_Utils_Date::getFromTo(
        CRM_Utils_Array::value('activity_date_relative', $input, 0),
        CRM_Utils_Array::value('activity_date_low', $input),
        CRM_Utils_Array::value('activity_date_high', $input)
      );
      $commonClauses[] = sprintf('civicrm_activity.activity_date_time BETWEEN "%s" AND "%s" ', $from, $to);
    }

    if (!empty($input['activity_status_id'])) {
      $commonClauses[] = sprintf("civicrm_activity.status_id IN (%s)", $input['activity_status_id']);
    }


    //Filter on component IDs.
    $components = CRM_Activity_BAO_Activity::activityComponents();
    if (!empty($components)) {
      $componentsIn = implode(',', array_keys($components));
      $commonClauses[] = "( civicrm_option_value.component_id IS NULL OR civicrm_option_value.component_id IN ( $componentsIn ) )";
    }
    else {
      $commonClauses[] = "civicrm_option_value.component_id IS NULL";
    }

    // activity type ID clause
    if (!empty($input['activity_type_id'])) {
      if (is_array($input['activity_type_id'])) {
        foreach ($input['activity_type_id'] as $idx => $value) {
          $input['activity_type_id'][$idx] = CRM_Utils_Type::escape($value, 'Positive');
        }
        $commonClauses[] = "civicrm_activity.activity_type_id IN ( " . implode(",", $input['activity_type_id']) . " ) ";
      }
      else {
        $activityTypeID = CRM_Utils_Type::escape($input['activity_type_id'], 'Positive');
        $commonClauses[] = "civicrm_activity.activity_type_id = $activityTypeID";
      }
    }

    // exclude by activity type clause
    if (!empty($input['activity_type_exclude_id'])) {
      if (is_array($input['activity_type_exclude_id'])) {
        foreach ($input['activity_type_exclude_id'] as $idx => $value) {
          $input['activity_type_exclude_id'][$idx] = CRM_Utils_Type::escape($value, 'Positive');
        }
        $commonClauses[] = "civicrm_activity.activity_type_id NOT IN ( " . implode(",", $input['activity_type_exclude_id']) . " ) ";
      }
      else {
        $activityTypeID = CRM_Utils_Type::escape($input['activity_type_exclude_id'], 'Positive');
        $commonClauses[] = "civicrm_activity.activity_type_id != $activityTypeID";
      }
    }

    $commonClause = implode(' AND ', $commonClauses);

    $includeCaseActivities = FALSE;
    if (in_array('CiviCase', $components)) {
      $includeCaseActivities = TRUE;
    }


    // build main activity table select clause
    $sourceSelect = '';

    $activityContacts = CRM_Activity_BAO_ActivityContact::buildOptions('record_type_id', 'validate');
    $sourceID = CRM_Utils_Array::key('Activity Source', $activityContacts);
    $sourceJoin = "
INNER JOIN civicrm_activity_contact ac ON ac.activity_id = civicrm_activity.id
INNER JOIN civicrm_contact contact ON ac.contact_id = contact.id
";

    if (!$input['count']) {
      $sourceSelect = ',
                civicrm_activity.activity_date_time,
                civicrm_activity.source_record_id,
                civicrm_activity.status_id,
                civicrm_activity.subject,
                contact.sort_name as source_contact_name,
                civicrm_option_value.value as activity_type_id,
                civicrm_option_value.label as activity_type,
                null as case_id, null as case_subject,
                civicrm_activity.campaign_id as campaign_id
            ';

      $sourceJoin .= "
LEFT JOIN civicrm_activity_contact src ON (src.activity_id = ac.activity_id AND src.record_type_id = {$sourceID} AND src.contact_id = contact.id)
";
    }

    $sourceClause = "
            SELECT civicrm_activity.id as activity_id
            {$sourceSelect}
            from civicrm_activity
            left join civicrm_option_value on
                civicrm_activity.activity_type_id = civicrm_option_value.value
            left join civicrm_option_group on
                civicrm_option_group.id = civicrm_option_value.option_group_id
            {$sourceJoin}
            where
                    {$sourceWhere}
                AND $commonClause
        ";

    // Build case clause
    // or else exclude Inbound Emails that have been filed on a case.
    $caseClause = '';

    if ($includeCaseActivities) {
      $caseSelect = '';
      if (!$input['count']) {
        $caseSelect = ',
                civicrm_activity.activity_date_time,
                civicrm_activity.source_record_id,
                civicrm_activity.status_id,
                civicrm_activity.subject,
                contact.sort_name as source_contact_name,
                civicrm_option_value.value as activity_type_id,
                civicrm_option_value.label as activity_type,
                null as case_id, null as case_subject,
                civicrm_activity.campaign_id as campaign_id';
      }

      $caseClause = "
                union all

                SELECT civicrm_activity.id as activity_id
                {$caseSelect}
                from civicrm_activity
                inner join civicrm_case_activity on
                    civicrm_case_activity.activity_id = civicrm_activity.id
                inner join civicrm_case on
                    civicrm_case_activity.case_id = civicrm_case.id
                inner join civicrm_case_contact on
                    civicrm_case_contact.case_id = civicrm_case.id and {$caseWhere}
                left join civicrm_option_value on
                    civicrm_activity.activity_type_id = civicrm_option_value.value
                left join civicrm_option_group on
                    civicrm_option_group.id = civicrm_option_value.option_group_id
                {$sourceJoin}
                where
                        {$caseWhere}
                    AND $commonClause
                        and  ( ( civicrm_case_activity.case_id IS NULL ) OR
                           ( civicrm_option_value.name <> 'Inbound Email' AND
                             civicrm_option_value.name <> 'Email' AND civicrm_case_activity.case_id
                             IS NOT NULL )
                         )
            ";
    }

    $returnClause = " {$sourceClause} {$caseClause} ";

    return array($returnClause, $params);
  }


}

<?php

/**
 * MailContents
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class MailContents extends BaseMailContents {
	
	var $object_type_name ;
		
	public function __construct() {
		parent::__construct ();
		$this->object_type_name = 'mail';
	}
	
	public static function getMailsFromConversation(MailContent $mail) {
		$conversation_id = $mail->getConversationId();
		if ($conversation_id == 0 || $mail->getIsDraft()) return array($mail);
		return self::findAll(array(
			"conditions" => "`conversation_id` = '$conversation_id' AND `account_id` = " . $mail->getAccountId() . " AND `state` <> 2",
			"order" => "`received_date` DESC"
		));
	}
	
	public static function getMailIdsFromConversation($mail, $check_permissions = false) {
		$conversation_id = $mail->getConversationId();
		if ($conversation_id == 0) return array($mail->getId());
		
		// TODO FENG2 Permissions
		/*
		if ($check_permissions) {
			$permissions = " AND " . permissions_sql_for_listings(self::instance(), ACCESS_LEVEL_READ, logged_user());
		} else {
			$permissions = "";
		}
		*/ 
		$permissions = '' ;
		$sql = "SELECT `object_id` AS `id`  FROM `". TABLE_PREFIX ."mail_contents` WHERE `conversation_id` = '$conversation_id'  AND `account_id` = " . $mail->getAccountId() . " AND `state` <> 2 $permissions";
		$rows = DB::executeAll($sql);
		if (is_array($rows)){
			$result = array();
			foreach ($rows as $row){
				$result[] = $row['id'];
			}
			return $result;
		} else { 
			return array($mail->getId());
		}
	}
	
	public static function getLastMailIdInConversation($conversation_id, $check_permissions = false) {
		if ($conversation_id == 0) return 0;
		if ($check_permissions) {
			// TODO: Feng 2  
			$permissions = "" ;
			// $permissions = " AND " . permissions_sql_for_listings(self::instance(), ACCESS_LEVEL_READ, logged_user());
		} else {
			$permissions = "";
		}
		$sql = "
			SELECT `object_id` FROM `". TABLE_PREFIX ."mail_contents` mc
			INNER JOIN ". TABLE_PREFIX ."objects o on o.id =  mc.object_id
			WHERE `conversation_id` = '$conversation_id' AND `state` <> 2 $permissions 
			ORDER BY `received_date` DESC 
			LIMIT 0,1";
		$rows = DB::executeAll($sql);
		if (is_array($rows) && count($rows) > 0) {
			return $rows[0]['object_id'];
		} else {
			return 0;
		}
	}
	
	public static function countMailsInConversation($mail = null, $include_trashed = false) {
		if (!$mail instanceof MailContent || $mail->getConversationId() == 0) return 0;
		$conversation_id = $mail->getConversationId();
		$deleted = ' AND `is_deleted` = false';
		if (!$include_trashed) $deleted .= ' AND `trashed_by_id` = 0';
		$sql = "
			SELECT count(distinct(object_id)) AS total  FROM `". 
			TABLE_PREFIX ."mail_contents` m INNER JOIN `".TABLE_PREFIX ."objects` o on m.object_id=o.id
			WHERE `conversation_id` = '$conversation_id' $deleted AND `account_id` = " . $mail->getAccountId() . " AND `state` <> 2";

		$rows = DB::executeOne($sql);
		return ( $rows['total'] ) ;
		
	}
	
	public static function countUnreadMailsInConversation($mail = null, $include_trashed = false) {
		if (!$mail instanceof MailContent || $mail->getConversationId() == 0) return 0;
		$conversation_id = $mail->getConversationId();
		$unread_cond = "AND NOT o.id IN 
		( SELECT `rel_object_id` FROM `" . TABLE_PREFIX . "read_objects` `t` WHERE `contact_id` = " . logged_user()->getId() . " AND `t`.`is_read` = '1')";
		$deleted = ' AND `is_deleted` = false';
		if (!$include_trashed) $deleted .= ' AND `trashed_by_id` = 0';
		$sql = "
			SELECT count(o.id) AS total
		 	FROM ". TABLE_PREFIX ."mail_contents mc 
		 	INNER JOIN ". TABLE_PREFIX ."objects o ON o.id = mc.object_id 
		 	WHERE `conversation_id` = '$conversation_id' $deleted 
		 	AND `account_id` = " . $mail->getAccountId() . " 
		 	AND `state` <> 2 $unread_cond";
		$row = DB::executeOne($sql);
		//return 4 ;
		return $row['total'];
	}
	
	public static function conversationHasAttachments($mail = null, $include_trashed = false) {
		if (!$mail instanceof MailContent || $mail->getConversationId() == 0) return false;
		$conversation_id = $mail->getConversationId();
		$deleted = ' AND `is_deleted` = false';
		if (!$include_trashed) $deleted .= ' AND `trashed_by_id` = 0';
		$sql = "SELECT `has_attachments` FROM ". TABLE_PREFIX ."mail_contents mc 
		INNER JOIN ". TABLE_PREFIX ."objects o ON o.id = mc.object_id 
		WHERE `conversation_id` = '$conversation_id' $deleted AND `account_id` = " . $mail->getAccountId();
		$rows = DB::executeAll($sql);
		foreach ($rows as $row) {
			if (array_var($row, 'has_attachments')) return true;
		}
		return false;;
	}
	
	public static function getNextConversationId($account_id) {
		$sql = "INSERT INTO `".TABLE_PREFIX."mail_conversations` () VALUES ()";
		DB::execute($sql);
		return DB::lastInsertId();
	}
	 
	public static function getWorkspaceString($ids = '?') {
		return " `id` IN (SELECT `object_id` FROM `" . TABLE_PREFIX . "workspace_objects` WHERE `object_manager` = 'MailContents' AND `workspace_id` IN ($ids)) ";
	}
	
	public static function getNotClassifiedString() {
		return " NOT EXISTS(SELECT `object_id` FROM `" . TABLE_PREFIX . "workspace_objects` WHERE `object_manager` = 'MailContents' AND `object_id` = `id`) ";
	}
	
	static function mailRecordExists($account_id, $uid, $folder = null, $is_deleted = null) {
		if (!$uid) return false;
		$folder_cond = is_null($folder) ? '' : " AND `imap_folder_name` = " . DB::escape($folder);
		$del_cond = is_null($is_deleted) ? "" : " AND `is_deleted` = " . DB::escape($is_deleted ? true : false);
		$conditions = "`account_id` = " . DB::escape($account_id) . " AND `uid` = " . DB::escape($uid) . $folder_cond . $del_cond;
		
		$rows = DB::executeAll("SELECT object_id FROM `".TABLE_PREFIX."mail_contents` WHERE $conditions limit 1");
		return count($rows) > 0;
	}
	
	static function getUidsFromAccount($account_id, $folder = null) {
		$uids = array();
		$folder_cond = $folder == null ? "" : "AND `imap_folder_name` = " . DB::escape($folder);
		$sql = "SELECT `uid` FROM `".TABLE_PREFIX."mail_contents` WHERE `account_id` = $account_id $folder_cond";
		$rows = DB::executeAll($sql);
		if (is_array($rows)) {
			foreach ($rows as $row) {
				$uids[] = $row['uid'];
			}
		}
		return $uids;
	}

	/**
	 * Return mails that belong to specific project
	 *
	 * @param Project $project
	 * @return array
	 */
	static function getProjectMails(Project $project, $start = 0, $limit = 0) {
		$condstr = self::getWorkspaceString();
		return self::findAll(array(
			'conditions' => array($condstr, $project->getId()),
			'offset' => $start,
			'limit' => $limit,
		));
	} // getProjectMails

	function delete($condition) {
		if(isset($this) && instance_of($this, 'MailContents')) {
			// Delete contents from filesystem
			$sql = "SELECT `content_file_id` FROM ".self::instance()->getTableName(true)." WHERE $condition";
			$rows = DB::executeAll($sql);
				
			if (is_array($rows)) {
				$count = 0;$err=0;
				foreach ($rows as $row) {
					if (isset($row['content_file_id']) && $row['content_file_id'] != '') {
						try {
							FileRepository::deleteFile($row['content_file_id']);
							$count++;
						} catch (Exception $e) {
							$err++;
							//Logger::log($e->getMessage());
						}
					}
				}
			}
			
			return parent::delete($condition);
		} else {
			return MailContents::instance()->delete($condition);
		}
	}
	
	/**
	 * Returns a list of emails according to the requested parameters
	 *
	 * @param string $tag
	 * @param array $attributes
	 * @param Project $project
	 * @return array
	 */
	function getEmails($account_id = null, $state = null, $read_filter = "", $classif_filter = "", $context = null, $start = null, $limit = null, $order_by = 'received_date', $dir = 'ASC', $archived = false) {
		$mailTablePrefix = "e";
		
		$accountConditions = "";
		// Check for accounts
                $accountConditions = '';
		if (isset($account_id) && $account_id > 0) { //Single account
			$accountConditions = " AND $mailTablePrefix.account_id = " . DB::escape($account_id);
/*		} else {
			// show emails from other accounts
			$macs = MailAccountContacts::instance()->getByContact(logged_user());
			$acc_ids = array();
			foreach ($macs as $mac) $acc_ids[] = $mac->getAccountId();
			if (count($acc_ids) == 0) $acc_ids[] = 0;
			$accountConditions = " AND $mailTablePrefix.account_id IN (" . implode(",", $acc_ids) . ")";
*/
		}
					
		// Check for unclassified emails
                $classified = '';
		if ($classif_filter != '' && $classif_filter != 'all') {
			if ($classif_filter == 'unclassified') $classified = "AND NOT ";
			else $classified = "AND ";
			$classified .= "o.id IN (SELECT object_id FROM ".TABLE_PREFIX."object_members)";
		} else {
			if (isset($account_id) && $account_id > 0) {
				$acc_cond = "IN ($account_id)";
			} else {
				$macs = MailAccountContacts::instance()->getByContact(logged_user());
				$acc_ids = array(0);
				foreach ($macs as $mac) $acc_ids[] = $mac->getAccountId();
				$acc_cond = "IN (".implode(",", $acc_ids).")";
			}
			$classified = "AND (e.account_id $acc_cond OR o.id IN (SELECT object_id FROM ".TABLE_PREFIX."object_members))";
		}

		// Check for draft, junk, etc. emails
		if ($state == "draft") {
			$stateConditions = " $mailTablePrefix.state = '2'";
		} else if ($state == "sent") {
			$stateConditions = " $mailTablePrefix.state IN ('1','3','5')";
		} else if ($state == "received") {
			$stateConditions = " $mailTablePrefix.state IN ('0','5')";
		} else if ($state == "junk") {
			$stateConditions = " $mailTablePrefix.state = '4'";
		} else if ($state == "outbox") {
			$stateConditions = " $mailTablePrefix.state >= 200";
		} else {
			$stateConditions = "";
		}
		
		// Check read emails
		if ($read_filter != "" && $read_filter != "all") {
			if ($read_filter == "unread") {
				$read = "AND NOT ";
				$subread = "AND NOT mc.";
			} else {
				$read = "AND ";
				$subread = "AND mc."; 
			}
			$read2 = "id IN (SELECT rel_object_id FROM " . TABLE_PREFIX . "read_objects t WHERE contact_id = " . logged_user()->getId() . "  AND t.is_read = '1')";
			$read .= $read2;
			$subread .= $read2;
		} else {
			$read = "";
			$subread = "";
		}

		
		
		// Conversations not allowed yet
		//if (user_config_option('show_emails_as_conversations')) {
		//	$state_conv_cond_1 = $state != 'received' ? " $stateConditions AND " : " m.state <> '2' AND ";
		//	$state_conv_cond_2 = $state != 'received' ? " AND (mc.state = '1' OR mc.state = '3' OR mc.state = '5') " : " AND mc.state <> '2' ";
		//	$archived_by_id = $archived ? "AND o.archived_by_id != 0" : "AND o.archived_by_id = 0";
		//	$trashed_by_id = "AND o.trashed_by_id = 0";
		//	$conversation_cond = "AND IF(m.conversation_id = 0, $stateConditions, $state_conv_cond_1 NOT EXISTS (SELECT * FROM ".TABLE_PREFIX."mail_contents mc WHERE m.conversation_id = mc.conversation_id AND m.account_id = mc.account_id AND m.received_date < mc.received_date $archived_by_id AND mc.is_deleted = 0 $trashed_by_id $subread $state_conv_cond_2))";
		//	$box_cond = "AND IF(EXISTS(SELECT * FROM ".TABLE_PREFIX."mail_contents mc WHERE m.conversation_id = mc.conversation_id AND m.object_id <> o.id AND m.account_id = mc.account_id $archived_by_id AND mc.is_deleted = 0 $trashed_by_id AND $stateConditions), TRUE, $stateConditions)";
		//} else {
			$conversation_cond = "";
			$box_cond = "AND $stateConditions";
		//}

		/*return self::findByContext(array('limit' => $limit, 'offset' => $start, 'order' => "$order_by $dir",
			'extra_conditions' => "$accountConditions $classified $read $conversation_cond $box_cond")); */

		return self::instance()->listing(array(
			'limit' => $limit, 
			'start' => $start, 
			'order' => $order_by,
			'order_dir' => $dir,
			'extra_conditions' => "$accountConditions $classified $read $conversation_cond $box_cond",
			'count_results' => false
		));
		
		
		
	}
	
	function getByMessageId($message_id) {
		return self::findOne(array('conditions' => array('`message_id` = ?', $message_id)));
	}
	
	function countUserInboxUnreadEmails() {
		$tp = TABLE_PREFIX;
		$uid = logged_user()->getId();
		$sql = "SELECT count(*) `c` FROM `{$tp}mail_contents` `a`, `{$tp}read_objects` `b` WHERE `b`.`rel_object_manager` = 'MailContents' AND `b`.`rel_object_id` = `a`.`id` AND `b`.`user_id` = '$uid' AND `b`.`is_read` = '1' AND `a`.`trashed_on` = '0000-00-00 00:00:00' AND `a`.`is_deleted` = 0 AND `a`.`archived_by_id` = 0 AND (`a`.`state` = '0' OR `a`.`state` = '5') AND " . permissions_sql_for_listings(MailContents::instance(), ACCESS_LEVEL_READ, logged_user(), null, '`a`');
		$rows = DB::executeAll($sql);
		$read = $rows[0]['c'];
		$sql = "SELECT count(*) `c` FROM `{$tp}mail_contents` `a` WHERE `a`.`trashed_on` = '0000-00-00 00:00:00' AND `a`.`is_deleted` = 0 AND `a`.`archived_by_id` = 0 AND (`a`.`state` = '0' OR `a`.`state` = '5') AND " . permissions_sql_for_listings(MailContents::instance(), ACCESS_LEVEL_READ, logged_user(), null, '`a`');
		$rows = DB::executeAll($sql);
		$all = $rows[0]['c'];
		return $all - $read;
	}
	
	
	/*
	 * Same that getContentObjects but reading from sahring table 
	 */
	static function findByContext( $options = array () ) {
		
		// Initialize method result
		$result = new stdClass();
		$result->total = 0 ;
		$result->objects = array() ;
		
		// Read arguments and Init Vars
		$limit = array_var($options,'limit');
		$offset = array_var($options,'offset');
		$trashed = array_var($options,'trashed');
		$archived = array_var($options,'archived');
		$members = active_context_members(false);
		$type_id = self::instance()->getObjectTypeId();
		 
		$uid = logged_user()->getId() ;
		if ($limit > 0) {
			$limit_sql = "LIMIT ".($offset ? "$offset, " : "")."$limit";
		} else {
			$limit_sql = '' ;
		}
		
		$member_conditions = count($members) > 0 ? "id IN (SELECT object_id FROM ".TABLE_PREFIX."object_members WHERE member_id IN (".implode(',', $members)."))" : "true";
		$trashed_conditions = "AND o.trashed_on " . ($trashed ? ">" : "=") . " 0";
		$archived_conditions = "AND o.archived_on " . ($archived ? ">" : "=") . " 0";
		$extra_conditions = array_var($options, 'extra_conditions', "");
		
		// Build Main SQL
	    $template_sql = "
	    	SELECT <selection> FROM ".TABLE_PREFIX."objects o
	    	INNER JOIN ".TABLE_PREFIX."mail_contents m ON m.object_id = o.id
	    	WHERE 
	    		o.id IN ( 
	    			SELECT object_id FROM ".TABLE_PREFIX."sharing_table
	    			WHERE group_id  IN (
		     			SELECT permission_group_id FROM ".TABLE_PREFIX."contact_permission_groups WHERE contact_id = $uid
					)
				) 
				AND $member_conditions
				AND o.object_type_id = $type_id
				AND m.is_deleted = 0 $trashed_conditions $archived_conditions $extra_conditions";
		
	    $count_sql = str_replace_first("<selection>", "COUNT(distinct(o.id)) as total", $template_sql);
	    $sql = str_replace_first("<selection>", "distinct(o.id)", $template_sql) . " $limit_sql";
	    
	    // count all emails
	    $res = DB::execute($count_sql);
	    $result->total = array_var($res->fetchRow(), 'total');
	    
	    if ($result->total == 0) {
	    	return $result;
	    }
	    
		// Execute query and build the resultset	
	    $rows = DB::executeAll($sql);
	    $mail_ids = array();
	    foreach ($rows as $row) {
	    	$mail_ids[] = $row['id'];
		}
		
		$result->objects = MailContents::findAll(array(
			"conditions" => "object_id IN (".implode(",", $mail_ids).")",
			"order" => array_var($options, 'order')
		));
		
		return $result;
	}
        
        /**
         * search according to the conditions of mail rules
         * @param string $condition
         * @return object 
         */
        function getConditionsRules($condition){
		return MailContents::findAll(array(
			'conditions' => $condition,
			'join' => array(
				'table' => MailDatas::instance()->getTableName(),
				'jt_field' => 'id',
				'e_field' => 'object_id',
			)
		));
	}

}

<?php

/**
 * MailContents
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class MailContents extends BaseMailContents {
	private static $rel_object_id;
	public static function getMailsFromConversation(MailContent $mail) {
		$conversation_id = $mail->getConversationId();
		if ($conversation_id == 0 || $mail->getIsDraft()) return array($mail);
		return self::findAll(array(
			"conditions" => "`conversation_id` = '$conversation_id' AND `account_id` = " . $mail->getAccountId() . " AND `state` <> 2 AND ". permissions_sql_for_listings(self::instance(), ACCESS_LEVEL_READ, logged_user()),
			"order" => "`received_date` DESC"
		));
	}
	
	public static function getMailIdsFromConversation($mail, $check_permissions = false) {
		$conversation_id = $mail->getConversationId();
		if ($conversation_id == 0) return array($mail->getId());
		if ($check_permissions) {
			$permissions = " AND " . permissions_sql_for_listings(self::instance(), ACCESS_LEVEL_READ, logged_user());
		} else {
			$permissions = "";
		}
		$sql = "SELECT `id` FROM `". TABLE_PREFIX ."mail_contents` WHERE `conversation_id` = '$conversation_id'  AND `account_id` = " . $mail->getAccountId() . " AND `state` <> 2 $permissions";
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
			$permissions = " AND " . permissions_sql_for_listings(self::instance(), ACCESS_LEVEL_READ, logged_user());
		} else {
			$permissions = "";
		}
		$sql = "SELECT `id` FROM `". TABLE_PREFIX ."mail_contents` WHERE `conversation_id` = '$conversation_id' AND `state` <> 2 $permissions ORDER BY `received_date` DESC LIMIT 0,1";
		$rows = DB::executeAll($sql);
		if (is_array($rows) && count($rows) > 0) {
			return $rows[0]['id'];
		} else {
			return 0;
		}
	}
	
	public static function countMailsInConversation($mail = null, $include_trashed = false) {
		if (!$mail instanceof MailContent || $mail->getConversationId() == 0) return 0;
		$conversation_id = $mail->getConversationId();
		$deleted = ' AND `is_deleted` = false';
		if (!$include_trashed) $deleted .= ' AND `trashed_on` = ' . DB::escape(EMPTY_DATETIME);
		$sql = "SELECT `id` FROM `". TABLE_PREFIX ."mail_contents` WHERE `conversation_id` = '$conversation_id' $deleted AND `account_id` = " . $mail->getAccountId() . " AND `state` <> 2 AND ".permissions_sql_for_listings(self::instance(), ACCESS_LEVEL_READ, logged_user());
		$rows = DB::executeAll($sql);
		return (is_array($rows) ? count($rows) : 0);
		
		
	}
	
	public static function countUnreadMailsInConversation($mail = null, $include_trashed = false) {
		if (!$mail instanceof MailContent || $mail->getConversationId() == 0) return 0;
		$conversation_id = $mail->getConversationId();
		$permissions = " AND " . permissions_sql_for_listings(self::instance(), ACCESS_LEVEL_READ, logged_user());
		//if the variable is not set, make the query and set it
		//seba
		if( !isset(self::$rel_object_id)){
			$sql = "SELECT `rel_object_id` FROM `" . TABLE_PREFIX . "read_objects` `t` WHERE `user_id` = " . logged_user()->getId() . " AND `t`.`rel_object_manager` = 'MailContents' AND `t`.`is_read` = '1'";
			$rows = DB::executeAll($sql);
			if (count($rows)== 0) self::$rel_object_id = "0";
			else{
				foreach ($rows as $row){
						if (self::$rel_object_id != "") self::$rel_object_id .= ",";
						self::$rel_object_id .= $row['rel_object_id'];						
				}
			}
		}
		//$unread_cond = "AND NOT `id` IN (SELECT `rel_object_id` FROM `" . TABLE_PREFIX . "read_objects` `t` WHERE `user_id` = " . logged_user()->getId() . " AND `t`.`rel_object_manager` = 'MailContents' AND `t`.`is_read` = '1')";
		$unread_cond = "AND NOT `id` IN (".self::$rel_object_id.")";
		$deleted = ' AND `is_deleted` = false';
		if (!$include_trashed) $deleted .= ' AND `trashed_on` = ' . DB::escape(EMPTY_DATETIME);
		$sql = "SELECT `id` FROM `". TABLE_PREFIX ."mail_contents` WHERE `conversation_id` = '$conversation_id' $deleted AND `account_id` = " . $mail->getAccountId() . " AND `state` <> 2 $unread_cond".$permissions;
		$rows = DB::executeAll($sql);
		return (is_array($rows) ? count($rows) : 0);
	}
	
	public static function conversationHasAttachments($mail = null, $include_trashed = false) {
		if (!$mail instanceof MailContent || $mail->getConversationId() == 0) return false;
		$permissions = " AND " . permissions_sql_for_listings(self::instance(), ACCESS_LEVEL_READ, logged_user());
		$conversation_id = $mail->getConversationId();
		$deleted = ' AND `is_deleted` = false';
		if (!$include_trashed) $deleted .= ' AND `trashed_on` = ' . DB::escape(EMPTY_DATETIME);
		$sql = "SELECT `has_attachments` FROM `". TABLE_PREFIX ."mail_contents` WHERE `conversation_id` = '$conversation_id' $deleted AND `account_id` = " . $mail->getAccountId().$permissions;
		$rows = DB::executeAll($sql);
		foreach ($rows as $row) {
			if (array_var($row, 'has_attachments')) return true;
		}
		return false;
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
		$mail = self::findOne(array('conditions' => $conditions, 'include_trashed' => true));
		return $mail instanceof MailContent;
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
	function getEmails($tag = null, $account_id = null, $state = null, $read_filter = "", $classif_filter = "", $project = null, $start = null, $limit = null, $order_by = 'received_date', $dir = 'ASC', $archived = false, $count = false) {
		// Check for accounts
		$accountConditions = "";
		if (isset($account_id) && $account_id > 0) { //Single account
			$accountConditions = " AND `account_id` = " . DB::escape($account_id);
		}
					
		// Check for unclassified emails
		if ($classif_filter != '' && $classif_filter != 'all') {
			if ($classif_filter == 'unclassified') $classified = "AND NOT ";
			else $classified = "AND ";			
			$classified .= "`id` IN (SELECT `object_id` FROM `".TABLE_PREFIX."workspace_objects` WHERE `object_manager` = 'MailContents')";
		} else {
			$classified = "";
		}

		// Check for drafts emails
		if ($state == "draft") {
			$stateConditions = " `state` = '2'";
		} else if ($state == "sent") {
			$stateConditions = " (`state` = '1' OR `state` = '3' OR `state` = '5')";
		} else if ($state == "received") {
			$stateConditions = " (`state` = '0' OR `state` = '5')";
		} else if ($state == "junk") {
			$stateConditions = " `state` = '4'";
		} else if ($state == "outbox") {
			$stateConditions = " `state` >= 200";
		} else {
			$stateConditions = "";
		}

		// Check read emails
		if ($read_filter != "" && $read_filter != "all") {
			if ($read_filter == "unread") {
				$read = "AND NOT ";
				$subread = "AND NOT `mc`.";
			} else {
				$read = "AND ";
				$subread = "AND `mc`."; 
			}
			//if the variable is not set, make the query and set it
			if( !isset(self::$rel_object_id)){
				$sql = "SELECT `rel_object_id` FROM `" . TABLE_PREFIX . "read_objects` `t` WHERE `user_id` = " . logged_user()->getId() . " AND `t`.`rel_object_manager` = 'MailContents' AND `t`.`is_read` = '1'";
				$rows = DB::executeAll($sql);
				if (count($rows)== 0) self::$rel_object_id = "0";
				else{
					foreach ($rows as $row){
							if (self::$rel_object_id != "") self::$rel_object_id .= ",";
							self::$rel_object_id .= $row['rel_object_id'];						
					}
				}
			}
		
			//$read2 = "`id` IN (SELECT `rel_object_id` FROM `" . TABLE_PREFIX . "read_objects` `t` WHERE `user_id` = " . logged_user()->getId() . " AND `t`.`rel_object_manager` = 'MailContents' AND `t`.`is_read` = '1')";
			$read2 = "`id` IN (".self::$rel_object_id.")";
			$read .= $read2;
			$subread .= $read2;
		} else {
			$read = "";
			$subread = "";
		}

		//Check for tags
		if (!isset($tag) || $tag == '' || $tag == null) {
			$tagstr = ""; // dummy condition
			$subtagstr = "";
		} else {
			$tagstr = "AND (SELECT count(*) FROM `" . TABLE_PREFIX . "tags` WHERE `" .
					TABLE_PREFIX . "mail_contents`.`id` = `" . TABLE_PREFIX . "tags`.`rel_object_id` AND `" .
					TABLE_PREFIX . "tags`.`tag` = " . DB::escape($tag) . " AND `" . TABLE_PREFIX . "tags`.`rel_object_manager` ='MailContents' ) > 0 ";
			$subtagstr = "AND (SELECT count(*) FROM `" . TABLE_PREFIX . "tags` WHERE " .
					"`mc`.`id` = `" . TABLE_PREFIX . "tags`.`rel_object_id` AND `" .
					TABLE_PREFIX . "tags`.`tag` = " . DB::escape($tag) . " AND `" . TABLE_PREFIX . "tags`.`rel_object_manager` ='MailContents' ) > 0 ";
		}

		$permissions = ' AND ( ' . permissions_sql_for_listings(MailContents::instance(), ACCESS_LEVEL_READ, logged_user(), ($project instanceof Project ? $project->getId() : 0)) .')';
		
		//Check for projects (uses accountConditions
		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesQuery(!$archived);
			$projectConditions = " AND " . self::getWorkspaceString($pids);
		} else {
			$projectConditions = "";
		}

		if ($archived) $archived_cond = "AND `archived_by_id` <> 0";
		else $archived_cond = "AND `archived_by_id` = 0";

		$state_conv_cond_1 = $state != 'received' ? " $stateConditions AND " : " `state` <> '2' AND ";
		$state_conv_cond_2 = $state != 'received' ? " AND (`mc`.`state` = '1' OR `mc`.`state` = '3' OR `mc`.`state` = '5') " : " AND `mc`.`state` <> '2' ";
		
		if (user_config_option('show_emails_as_conversations')) {
			$archived_by_id = $archived ? "AND `mc`.`archived_by_id` != 0" : "AND `mc`.`archived_by_id` = 0";
			$trashed_by_id = "AND `mc`.`trashed_on` = " . DB::escape(EMPTY_DATETIME);
			$conversation_cond = "AND IF(`conversation_id` = 0, $stateConditions, $state_conv_cond_1 NOT EXISTS (SELECT * FROM `".TABLE_PREFIX."mail_contents` `mc` WHERE `".TABLE_PREFIX."mail_contents`.`conversation_id` = `mc`.`conversation_id` AND `".TABLE_PREFIX."mail_contents`.`account_id` = `mc`.`account_id` AND `".TABLE_PREFIX."mail_contents`.`received_date` < `mc`.`received_date` $archived_by_id AND `mc`.`is_deleted` = 0 $trashed_by_id $subtagstr $subread $state_conv_cond_2))";
			$box_cond = "AND IF(EXISTS(SELECT * FROM `".TABLE_PREFIX."mail_contents` `mc` WHERE `".TABLE_PREFIX."mail_contents`.`conversation_id` = `mc`.`conversation_id` AND `".TABLE_PREFIX."mail_contents`.`id` <> `mc`.`id` AND `".TABLE_PREFIX."mail_contents`.`account_id` = `mc`.`account_id` $archived_by_id AND `mc`.`is_deleted` = 0 $trashed_by_id AND $stateConditions), TRUE, $stateConditions)";
		} else {
			$conversation_cond = "";	
			$box_cond = "AND $stateConditions";
		}

		$conditions = "`is_deleted` = 0 $archived_cond $projectConditions $accountConditions $tagstr $classified $read $permissions $conversation_cond $box_cond";

		if ($count) {
			return self::count($conditions);
		} else {
			if (!defined('EMAIL_PERFORMANCE_WORKAROUND') || !EMAIL_PERFORMANCE_WORKAROUND) {
				return self::paginate(array('conditions' => $conditions, 'order' => "$order_by $dir"), config_option('files_per_page'), $start / $limit + 1);
			} else {
				/* COMPLEX WORKAROUND FOR PERFORMANCE */
				$conditions = "`is_deleted` = 0 $archived_cond $projectConditions $tagstr $classified $read $accountConditions";
				
				$page = (integer) ($start / $limit) + 1;
				$order = "$order_by $dir";
				$count = null;
				if (defined('INFINITE_PAGING') && INFINITE_PAGING) $count = 10000000;
				$pagination = new DataPagination($count ? $count : self::count($conditions), $limit, $page);
				$ids = self::findAll(array(
					'conditions' => $conditions,
					'id' => true,
				));
				
				$ids = array_reverse($ids);
				$ret_ids = array();
				$offset = 0;
				$block_len = $pagination->getItemsPerPage();
				while (count($ret_ids) < $pagination->getLimitStart() + $pagination->getItemsPerPage() && $offset < count($ids)) {
					$tmp_ids = array();
					for ($i=$offset; $i<count($ids) && $i<$offset + $block_len; $i++) {
						$tmp_ids[] = $ids[$i];
					}
					
					$tmp_ids = self::findAll(array(
						'conditions' => "`id` IN (".implode(",", $tmp_ids).") $permissions $conversation_cond $box_cond",
						'order' => $order,
						'id' => true,
					));
					
					$ret_ids = array_merge($ret_ids, $tmp_ids);
					
					$offset += $block_len;
				}
				$ids = array();
				for ($i=$pagination->getLimitStart(); $i<count($ret_ids); $i++) {
					$ids[] = $ret_ids[$i];
				}
				
				$objects = array();
				foreach ($ids as $id) {
					$objects[] = self::findById($id);
				}
	      		return array($objects, $pagination);
			}
		}
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

} // MailContents

?>
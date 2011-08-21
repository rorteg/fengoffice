<?php

/**
 * ApplicationLog class
 * Generated on Tue, 07 Mar 2006 12:19:49 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ApplicationLog extends BaseApplicationLog {

	/**
	 * Return user who made this acction
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getTakenBy() {
		return Users::findById($this->getTakenById());
	} // getTakenBy

	/**
	 * Return taken by display name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getTakenByDisplayName() {
		$taken_by = $this->getTakenBy();
		return $taken_by instanceof User ? $taken_by->getDisplayName() : lang('n/a');
	} // getTakenByDisplayName

	/**
	 * Returns true if this application log is made today
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isToday() {
		$now = DateTimeValueLib::now();
		$created_on = $this->getCreatedOn();

		// getCreatedOn and similar functions can return NULL
		if(!($created_on instanceof DateTimeValue)) return false;

		return $now->getDay() == $created_on->getDay() &&
		$now->getMonth() == $created_on->getMonth() &&
		$now->getYear() == $created_on->getYear();
	} // isToday

	/**
	 * Returnst true if this application log was made yesterday
	 *
	 * @param void
	 * @return boolean
	 */
	function isYesterday() {
		$created_on = $this->getCreatedOn();
		if(!($created_on instanceof DateTimeValue)) return false;

		$day_after = $created_on->advance(24 * 60 * 60, false);
		$now = DateTimeValueLib::now();

		return $now->getDay() == $day_after->getDay() &&
		$now->getMonth() == $day_after->getMonth() &&
		$now->getYear() == $day_after->getYear();
	} // isYesterday

	/**
	 * Return project
	 *
	 * @access public
	 * @param void
	 * @return Project
	 */
	function getProject() {
		return Projects::findById($this->getProjectId());
	} // getProject

	/**
	 * Return text message for this entry. If is lang formed as 'log' + action + manager name
	 *
	 * 'log add projectmessages'
	 *
	 * Object name is passed as a first param so it can be used in a message
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getText() {
		$code = strtolower('log ' . ($this->getAction()) . ' ' . $this->getRelObjectManager());
		$data = $this->getActionData();
		if ($data)
			$code = $code . ' data';
		return lang($code, clean($this->getObjectName()), $this->getActionData());
	} // getText
	
	function getActionData() {
		$result = $this->getLogData();
		
		if ($this->getLogData() != ''){
			switch($this->getAction()){
				case ApplicationLogs::ACTION_LINK: 
					$split = explode(':',$this->getLogData());
					$obj = get_object_by_manager_and_id($split[1], $split[0]);
					if ($obj && $obj->canView(logged_user())){
						$ico_class = '';
						switch($split[0]){
							case 'ProjectMessages': $ico_class = 'ico-message';break;
							case 'ProjectTasks': $ico_class = 'ico-task';break;
							case 'ProjectMilestones': $ico_class = 'ico-milestone';break;
							case 'Contacts': $ico_class = 'ico-contact';break;
							case 'ProjectFiles': $ico_class = 'ico-file';break;
							case 'ProjectFileRevisions': $ico_class = 'ico-file';break;
							case 'ProjectEvents': $ico_class = 'ico-event';break;
							default:break;
						}
						$result = '<a class="internalLink coViewAction ' . $ico_class . '" href="' . $obj->getViewUrl() . '">' .  clean($obj->getObjectName()) . '</a>';
					}
					break;
				case ApplicationLogs::ACTION_UNLINK: 
					$split = explode(':',$this->getLogData());
					$obj = get_object_by_manager_and_id($split[1], $split[0]);
					if ($obj && $obj->canView(logged_user())){
						$ico_class = '';
						switch($split[0]){
							case 'ProjectMessages': $ico_class = 'ico-message';break;
							case 'ProjectTasks': $ico_class = 'ico-task';break;
							case 'ProjectMilestones': $ico_class = 'ico-milestone';break;
							case 'Contacts': $ico_class = 'ico-contact';break;
							case 'ProjectFiles': $ico_class = 'ico-file';break;
							case 'ProjectFileRevisions': $ico_class = 'ico-file';break;
							case 'ProjectEvents': $ico_class = 'ico-event';break;
							default:break;
						}
						$result = '<a class="internalLink coViewAction ' . $ico_class . '" href="' . $obj->getViewUrl() . '">' .  clean($obj->getObjectName()) . '</a>';
					}
					break;
				case ApplicationLogs::ACTION_TAG:
					$result =  clean($this->getLogData());
					break;
				default: break;
			}
		}
		
		return $result;
	}

	/**
	 * Return object connected with this action
	 *
	 * @access public
	 * @param void
	 * @return ApplicationDataObject
	 */
	function getObject() {
		return get_object_by_manager_and_id($this->getRelObjectId(), $this->getRelObjectManager());
	} // getObject

	/**
	 * This function will try load related object and return its YRL. If object is not found '' is retuned
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		$object = $this->getObject();
		return $object instanceof ApplicationDataObject ? $object->getObjectUrl() : null;
	} // getObjectMessage

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		$object = $this->getObject();
		return $object instanceof ApplicationDataObject ? $object->getObjectTypeName() : null;
	} // getObjectTypeName

	function getActivityData() {
		$user = Users::findById($this->getCreatedById());
		$object = get_object_by_manager_and_id($this->getRelObjectId(), $this->getRelObjectManager());
		if (!$user) return false;
		
		$icon_class = "";
		if ($object instanceof ProjectFile) {
			$path = explode("-", str_replace(".", "_", str_replace("/", "-", $object->getTypeString())));
			$acc = "";
			foreach ($path as $p) {
				$acc .= $p;
				$icon_class .= ' ico-' . $acc;
				$acc .= "-";
			}			
		}
		if ($object){
			$object_link = '<a style="font-weight:bold" href="' . $object->getObjectUrl() . '">&nbsp;'.
			'<span style="padding: 1px 0 3px 18px;" class="db-ico ico-unknown ico-' . $object->getObjectTypeName() . $icon_class . '"/>'.clean($object->getObjectName()).'</a>';
		}
		else{
			$object_link = clean($this->getObjectName()).'&nbsp;'.lang('object is deleted');
		}			
		switch ($this->getAction()) {
			case ApplicationLogs::ACTION_EDIT :
			case ApplicationLogs::ACTION_ADD :
			case ApplicationLogs::ACTION_DELETE :
			case ApplicationLogs::ACTION_TRASH :
			case ApplicationLogs::ACTION_UNTRASH :
			case ApplicationLogs::ACTION_OPEN :
			case ApplicationLogs::ACTION_CLOSE :
			case ApplicationLogs::ACTION_ARCHIVE :
			case ApplicationLogs::ACTION_UNARCHIVE :
			case ApplicationLogs::ACTION_READ :				
			case ApplicationLogs::ACTION_DOWNLOAD :				
			case ApplicationLogs::ACTION_CHECKIN :
			case ApplicationLogs::ACTION_CHECKOUT :
				if ($object)
					return lang('activity ' . $this->getAction(), lang('the '.$object->getObjectTypeName()), $user->getDisplayName(), $object_link);
				else
					return lang('activity ' . $this->getAction(), lang('the '.$this->getRelObjectManager()), $user->getDisplayName(), $object_link);
			case ApplicationLogs::ACTION_SUBSCRIBE :
			case ApplicationLogs::ACTION_UNSUBSCRIBE :
				$user_ids = explode(",", $this->getLogData());
				if (count($user_ids) < 8) {
					$users_str = "";
					foreach ($user_ids as $usid) {
						$su = Users::findById($usid);
						if ($su instanceof User)
							$users_str .= '<a style="font-weight:bold" href="'.$su->getObjectUrl().'">&nbsp;<span style="padding: 0 0 3px 18px;" class="db-ico ico-unknown ico-user"/>'.clean($su->getObjectName()).'</a>, ';
					}
					if (count($user_ids) == 1) {
						$users_text = substr(trim($users_str), 0, -1);
					} else {
						$users_text = lang('x users', count($user_ids), ": $users_str");
					} 
				} else {
					$users_text = lang('x users', count($user_ids), "");
				}
				if ($object)
					return lang('activity ' . $this->getAction(), lang('the '.$object->getObjectTypeName()), $user->getDisplayName(), $object_link, $users_text);
				else
					return lang('activity ' . $this->getAction(), lang('the '.$this->getRelObjectManager()), $user->getDisplayName(), $object_link, $users_text);
			case ApplicationLogs::ACTION_COMMENT :
				if ($object)
					return lang('activity ' . $this->getAction(), lang('the '.$object->getObjectTypeName()), $user->getDisplayName(), $object_link, $this->getLogData());
				else
					return lang('activity ' . $this->getAction(), lang('the '.$this->getRelObjectManager()), $user->getDisplayName(), $object_link, $this->getLogData());
			case ApplicationLogs::ACTION_LINK :
			case ApplicationLogs::ACTION_UNLINK :
				$exploded = explode(":", $this->getLogData());
				$linked_object = get_object_by_manager_and_id($exploded[1], $exploded[0]);
				if ($linked_object instanceof ApplicationDataObject ) {
					$icon_class = "";
					if ($linked_object instanceof ProjectFile) {
						$path = explode("-", str_replace(".", "_", str_replace("/", "-", $linked_object->getTypeString())));
						$acc = "";
						foreach ($path as $p) {
							$acc .= $p;
							$icon_class .= ' ico-' . $acc;
							$acc .= "-";
						}			
					}
					$linked_object_link = '<a style="font-weight:bold" href="' . $linked_object->getObjectUrl() . '">&nbsp;<span style="padding: 1px 0 3px 18px;" class="db-ico ico-unknown ico-'.$linked_object->getObjectTypeName() . $icon_class . '"/>'.clean($linked_object->getObjectName()).'</a>';
				} else $linked_object_link = '';
				if ($object)
					return lang('activity ' . $this->getAction(), lang('the '.$object->getObjectTypeName()), $user->getDisplayName(), $object_link, $linked_object instanceof ApplicationDataObject ? lang('the '.$linked_object->getObjectTypeName()) : '', $linked_object_link);
				else
					return lang('activity ' . $this->getAction(), lang('the '.$this->getRelObjectManager()), $user->getDisplayName(), $object_link);					
			case ApplicationLogs::ACTION_LOGIN :
			case ApplicationLogs::ACTION_LOGOUT :
				return lang('activity ' . $this->getAction(), $user->getDisplayName());					
			case ApplicationLogs::ACTION_MOVE :
				$exploded = explode(";", $this->getLogData());
				$to_str = "";
				$from_str = "";
				foreach ($exploded as $str) {
					if (str_starts_with($str, "from:")) {
						$wsids_csv = str_replace("from:", "", $str);
						$wsids = array_intersect(explode(",", logged_user()->getActiveProjectIdsCSV()), explode(",", $wsids_csv));
						if (is_array($wsids) && count($wsids) > 0) {
							$from_str = '<span class="project-replace">' . implode(",", $wsids) . '</span>';
						}
					} else if (str_starts_with($str, "to:")) {
						$wsids_csv = str_replace("to:", "", $str);
						$wsids = array_intersect(explode(",", logged_user()->getActiveProjectIdsCSV()), explode(",", $wsids_csv));
						if (is_array($wsids) && count($wsids) > 0) {
							$to_str = '<span class="project-replace">' . implode(",", $wsids) . '</span>';
						}						
					}
				}
				if($object){
					if ($from_str != "" && $to_str != "") {						
						return lang('activity ' . $this->getAction() . ' from to', lang('the '.$object->getObjectTypeName()), $user->getDisplayName(), $object_link, $from_str, $to_str);
					} else if ($from_str != "") {
						return lang('activity ' . $this->getAction() . ' from', lang('the '.$object->getObjectTypeName()), $user->getDisplayName(), $object_link, $from_str);
					} else if ($to_str != "") {
						return lang('activity ' . $this->getAction() . ' to', lang('the '.$object->getObjectTypeName()), $user->getDisplayName(), $object_link, $to_str);
					} else {
						return lang('activity ' . $this->getAction() . ' no ws', lang('the '.$object->getObjectTypeName()), $user->getDisplayName(), $object_link);
					}
				}else{
					if ($from_str != "" && $to_str != "") {
						return lang('activity ' . $this->getAction() . ' from to', lang('the '.$this->getRelObjectManager()), $user->getDisplayName(), $object_link, $from_str, $to_str);
					} else if ($from_str != "") {
						return lang('activity ' . $this->getAction() . ' from', lang('the '.$this->getRelObjectManager()), $user->getDisplayName(), $object_link, $from_str);
					} else if ($to_str != "") {
						return lang('activity ' . $this->getAction() . ' to', lang('the '.$this->getRelObjectManager()), $user->getDisplayName(), $object_link, $to_str);
					} else {
						return lang('activity ' . $this->getAction() . ' no ws', lang('the '.$this->getRelObjectManager()), $user->getDisplayName(), $object_link);
					}					
				}			
			case ApplicationLogs::ACTION_COPY :				
				$to_str = "";
				$wsids_csv = str_replace("to:", "", $this->getLogData());
				$wsids = array_intersect(explode(",", logged_user()->getActiveProjectIdsCSV()), explode(",", $wsids_csv));
				if (is_array($wsids) && count($wsids) > 0) {
					$to_str = '<span class="project-replace">' . implode(",", $wsids) . '</span>';
				}
				if($object){
					if ($to_str != "") {
						return lang('activity ' . $this->getAction() . ' to', lang('the '.$object->getObjectTypeName()), $user->getDisplayName(), $object_link, $to_str);
					} else {
						return lang('activity ' . $this->getAction(), lang('the '.$object->getObjectTypeName()), $user->getDisplayName(), $object_link);
					}
				}else{
					if ($to_str != "") {
						return lang('activity ' . $this->getAction() . ' to', lang('the '.$this->getRelObjectManager()), $user->getDisplayName(), $object_link, $to_str);
					} else {
						return lang('activity ' . $this->getAction(), lang('the '.$this->getRelObjectManager()), $user->getDisplayName(), $object_link);
					}
				}			
			case ApplicationLogs::ACTION_TAG :
				if($object)					
					return lang('activity ' . $this->getAction(), lang('the '.$object->getObjectTypeName()), $user->getDisplayName(), $object_link, $this->getLogData());
				else
					return lang('activity ' . $this->getAction(), lang('the '.$this->getRelObjectManager()), $user->getDisplayName(), $object_link, $this->getLogData());
			default: return false;
		}
		return false;
	}
} // ApplicationLog

?>
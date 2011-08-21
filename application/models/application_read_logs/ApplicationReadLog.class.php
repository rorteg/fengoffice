<?php

/**
 * ApplicationReadLog class
 * Generated on Tue, 07 Mar 2006 12:19:49 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ApplicationReadLog extends BaseApplicationReadLog {

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

		$object = $this->getObject();
		
		return lang($code, clean($object->getTitle()));
	} // getText
	
	function getLogData() {
      return "";
    } // getLogData()
	
	function getActionData() {
		$result = $this->getLogData();
		
		if ($this->getLogData() != ''){
			switch($this->getAction()){
				case ApplicationReadLogs::ACTION_LINK: 
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
				case ApplicationReadLogs::ACTION_UNLINK: 
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
				case ApplicationReadLogs::ACTION_TAG:
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

} // ApplicationReadLog

?>
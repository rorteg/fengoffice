<?php

/**
 * Application logs manager class
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ApplicationReadLogs extends BaseApplicationReadLogs {

	const ACTION_READ         = 'read';
	const ACTION_DOWNLOAD     = 'download';
		
	public static function getWorkspaceString($ids = '?') {
		if (is_array($ids)) $ids = implode(",", $ids);
		return " `id` IN (SELECT `object_id` FROM `" . TABLE_PREFIX . "workspace_objects` WHERE `object_manager` = 'ApplicationReadLogs' AND `workspace_id` IN ($ids))";
	}
	
	/**
	 * Create new log entry and return it
	 *
	 * Delete actions are automatically marked as silent if $is_silent value is not provided (not NULL)
	 *
	 * @param ApplicationDataObject $object
	 * @param Project $project
	 * @param DataManager $manager
	 * @param boolean $save Save log object before you save it
	 * @return ApplicationReadLog
	 */
	static function createLog(ApplicationDataObject $object, $workspaces, $action = null, $is_private = false, $is_silent = null, $save = true, $log_data = '') {
		if(is_null($action)) {
			$action = self::ACTION_READ;
		} // if
		if(!self::isValidAction($action)) {
			throw new Error("'$action' is not valid log action");
		} // if

		try {
			Notifier::notifyAction($object, $action, $log_data);
		} catch (Exception $ex) {
			
		}
		
		$manager = $object->manager();
		if(!($manager instanceof DataManager)) {
			throw new Error('Invalid object manager');
		} // if

		$log = new ApplicationReadLog();

		if (logged_user() instanceof User) {
			$log->setTakenById(logged_user()->getId());
		} else {
			$log->setTakenById(0);
		}
		$log->setRelObjectId($object->getObjectId());
		$log->setRelObjectManager(get_class($manager));
		$log->setAction($action);
		
		if($save) {
			$log->save();
		} // if
		
		if ($save) {
			if ($workspaces instanceof Project) {
				$wo = new WorkspaceObject();
				$wo->setObject($log);
				$wo->setWorkspace($workspaces);
				$wo->save();
			} else if (is_array($workspaces)) {
				foreach ($workspaces as $w) {
					if ($w instanceof Project) {
						$wo = new WorkspaceObject();
						$wo->setObject($log);
						$wo->setWorkspace($w);
						$wo->save();
					}
				}
			}
		}

		return $log;
	} // createLog

	/**
	 * Check if specific action is valid
	 *
	 * @param string $action
	 * @return boolean
	 */
	static function isValidAction($action) {
		static $valid_actions = null;

		if(!is_array($valid_actions)) {
			$valid_actions = array(
			self::ACTION_READ,
			self::ACTION_DOWNLOAD
			); // array
		} // if

		return in_array($action, $valid_actions);
	} // isValidAction

	/**
	 * Return entries related to specific object
	 *
	 * If $include_private is set to true private entries will be included in result. If $include_silent is set to true
	 * logs marked as silent will also be included. $limit and $offset are there to control the range of the result,
	 * usually we don't want to pull the entire log but just the few most recent entries. If NULL they will be ignored
	 *
	 * @param ApplicationDataObject $object
	 * @param boolean $include_private
	 * @param boolean $include_silent
	 * @param integer $limit
	 * @param integer $offset
	 * @return array
	 */
	static function getObjectLogs($object, $limit = null, $offset = null) {

		return self::findAll(array(
        'conditions' => array('`rel_object_id` = (?) AND `rel_object_manager` = (?)', $object->getId(),get_class($object->manager())),
        'order' => '`created_on` DESC',
        'limit' => $limit,
        'offset' => $offset,
		)); // findAll
	} // getObjectLogs

} // ApplicationReadLogs

?>
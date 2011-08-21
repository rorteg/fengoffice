<?php

final class ZipImport {

	private $user;

	/**
	 * Name of the temp directory created for the workspace structure.
	 *
	 * @var unknown_type
	 */
	private $randomDirName;

	/**
	 * Name of the Workspace that will be the parent of the imported one.
	 *
	 * @var unknown_type
	 */
	private $parentWorkspace;

	/**
	 * Constructor
	 *
	 * @param Output $output
	 * @return acInstallation
	 */
	function __construct($parentWS) {
		$this->parentWorkspace = $parentWS;
		$this->randomDirName = '' . rand(1000, 10000);
		mkdir(TEMP_PATH . DIRECTORY_SEPARATOR . $this->randomDirName, 0777);
	} // __construct
	
	function setDirectory($dir) {
		$this->randomDirName = $dir;
	}

	function initUser($id) {
		$this->user = Users::findById($id);
		if ($this->user != null)
		CompanyWebsite::instance()->setLoggedUser($this->user);
		else {
			ImportLogger::instance()->logError("User not found: id=$id");
			die("User not found: id=$id");
		}
	}

	function extractToTmpDir($zip_path) {
		if (!isset($zip_path)) {
			ImportLogger::instance()->logError('Missing parameter: zip_path');
			print 'Missing parameter: zip_path';
			return false;
		} // if
		if (!file_exists($zip_path)) {
			ImportLogger::instance()->logError('Missing parameter: zip_path');
			print 'File not found: ' . $zip_path;
			return false;
		} else {

			print "Extracting from $zip_path ... ";
				
			$zip = zip_open($zip_path);
			if ($zip) {
				while ($zip_entry  = zip_read($zip)) {
					$completePath = TEMP_PATH . DIRECTORY_SEPARATOR . $this->randomDirName . DIRECTORY_SEPARATOR . dirname(zip_entry_name($zip_entry));
					$completeName = TEMP_PATH . DIRECTORY_SEPARATOR . $this->randomDirName . DIRECTORY_SEPARATOR . zip_entry_name($zip_entry);

					// Build complete path
					if(!file_exists($completePath)) {
						$tmp = TEMP_PATH;
						foreach(explode(DIRECTORY_SEPARATOR, $completePath) AS $k) {
							$tmp .= $k . DIRECTORY_SEPARATOR;
							if(!file_exists($tmp) ) {
								mkdir($tmp, 0777);
							} // if
						} // if
					} // if

					// Extract files and directories
					if (zip_entry_open($zip, $zip_entry, "r")) {
						if ($fd = @fopen($completeName, 'w+')) {
							fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
							fclose($fd);
						} else {
							// Empty directory
							mkdir($completeName, 0777);
						} // if
						zip_entry_close($zip_entry);
					} // if
				} // while

				zip_close($zip);
			} // if
		} // if

		print "Complete\r\n";
		return true;
	} //extractToTmpDir

	function deleteTmpDir() {
		print "Deleting temp files ... ";

		$path = TEMP_PATH . DIRECTORY_SEPARATOR . $this->randomDirName . DIRECTORY_SEPARATOR;
		$this->removeDir($path);
	} // deleteTmpDir

	function removeDir($dir) {
		if(!$dh = opendir($dir)) {
			return;
		} // if
		while ($obj = readdir($dh)) {
			if (is_dir($dir . DIRECTORY_SEPARATOR . $obj) && $obj != '.' && $obj != '..') {
				$this->removeDir($dir . DIRECTORY_SEPARATOR . $obj);
			} else {
				chmod($dir . DIRECTORY_SEPARATOR . $obj, 0777);
				unlink($dir . DIRECTORY_SEPARATOR . $obj);
			}
		} // while
		closedir($dh);
		rmdir($dir);
	} // removeDir

	function makeWorkSpaces($dir_path) {
		try {
			if ($dir_path == null)
				$path = TEMP_PATH . DIRECTORY_SEPARATOR . $this->randomDirName . DIRECTORY_SEPARATOR;
			else $path = $dir_path;
			if (!str_ends_with($path, DIRECTORY_SEPARATOR)) $path .= DIRECTORY_SEPARATOR;
			if ($dirHandler = opendir($path)) {
				$parents = array();
				$ws = Projects::findById($this->parentWorkspace);
				for ($i = 1; $i <= 10; $i++) {
					$pid = $ws->getPID($i);
					if ($pid != 0 && $pid != $ws->getId()) $parents[] = $pid;
					else break;
				}
				$this->createWorkSpaces($path, $dirHandler, $parents, $this->parentWorkspace);
				closedir($dirHandler);
			} // if
		} catch (Exception $e) {
			print $e->getMessage();
		}
	} // makeWorkSpaces

	function createWorkSpaces($path, $dirHandler, $ws_parent_ids, $ws_parent_id) {
		while ($node = readdir($dirHandler)) {
			$node = trim($node);
			if ($node != "." && $node != "..") {
				if (is_dir($path . $node)) {
					$exploded = explode(DIRECTORY_SEPARATOR, $node);
					if ($cant = count($exploded)) {
						$workspace_name = $exploded[$cant - 1];
					}

					if (!in_array($ws_parent_id, $ws_parent_ids)) {
						$ws_parent_ids[] = $ws_parent_id;
					}
					$actual_ws = Projects::getByName($workspace_name);
					if (!$actual_ws instanceof Project) {
						$ws_id = $this->createWorkspace($workspace_name, $ws_parent_ids);					
					} else {
						$ws_id = $actual_ws->getId();
					}
					
					if ($dh = opendir($path . $node)) {
						$this->createWorkSpaces($path . $node . DIRECTORY_SEPARATOR, $dh, $ws_parent_ids, $ws_id);
						closedir($dh);
					}
				} else {
					if ($ws_parent_id != null) {
						$this->uploadDocument($node, $ws_parent_id, $path);
					} // if
				} // if
			} // if
		} // while
	} // createWorkSpaces


	/********************************************************************************************************************/
	/*			WORKSPACE CREATION			*/
	/********************************************************************************************************************/

	function createWorkspace($ws_name, $parentWS_ids = null) {

		try {
			DB::beginWork();

			$color = rand(0, 24);
			$project_data = array('name' => $ws_name, 'description' => '', 'show_description_in_overview' => false, 'color' => $color);
			$project = new Project();
			$project->setFromAttributes($project_data);
			$project->save();
				
			$permission_columns = ProjectUsers::getPermissionColumns();
			$auto_assign_users = owner_company()->getAutoAssignUsers();
				
			// We are getting the list of auto assign users. If current user is not in the list
			// add it. He's creating the project after all...
			if(is_array($auto_assign_users)) {
				$auto_assign_logged_user = false;
				foreach($auto_assign_users as $user) {
					if($user->getId() == logged_user()->getId()) $auto_assign_logged_user = true;
				} // if
				if(!$auto_assign_logged_user) $auto_assign_users[] = logged_user();
			} else {
				$auto_assign_users[] = logged_user();
			} // if
				
			$project->clearUsers();
			foreach($auto_assign_users as $user) {
				$project_user = new ProjectUser();
				$project_user->setProjectId($project->getId());
				$project_user->setUserId($user->getId());
				if(is_array($permission_columns)) {
					foreach($permission_columns as $permission) $project_user->setColumnValue($permission, true);
				} // if
				$project_user->save();
			} // foreach

			$this->setParents($project, $parentWS_ids);
			$id_parent = $project->getPID($project->getDepth() - 1);
			$proj_id = $project->getId();
			
			ImportLogger::instance()->log("Workspace created: $proj_id $ws_name [$id_parent]");
			print "Workspace created: $proj_id $ws_name [$id_parent]\r\n";
				
			DB::commit();
		} catch (Exception $e) {
			print "ERROR: $e\r\n";
			DB::rollback();
		}

		return $proj_id;
	} // createWorkspace

	function setParents($project, $ws_parent_ids) {

		if (isset($ws_parent_ids) && is_array($ws_parent_ids) && count($ws_parent_ids)) {
			$k = 1;
			foreach ($ws_parent_ids as $id) {
				$project->setPID($k, $id);
				$k++;
			}
			$project->setPID(count($ws_parent_ids) + 1, $project->getId());
		}

		$project->save();
	}

	/********************************************************************************************************************/
	/*			DOCUMENTS UPLOAD			*/
	/********************************************************************************************************************/

	function uploadDocument($doc_name, $ws_id, $path) {
		if (str_starts_with($doc_name, "~")) {
			return;
		}
		try {
			DB::beginWork();
			$project = Projects::findById($ws_id);

			//$file = ProjectFiles::findOne(array("conditions" => "`filename` = '$doc_name'"));
			$files = ProjectFiles::getAllByFilename($doc_name, $ws_id);
			if (is_array($files) && count($files) > 0) $file = $files[0];
			else $file = null;
			if (!$file instanceof ProjectFile ) {
				$file = new ProjectFile();
				$file->setFilename($doc_name);
				$file->setIsVisible(true);
				$file->setIsPrivate(false);
				$file->setIsImportant(false);
				$file->setCommentsEnabled(true);
				$file->setAnonymousCommentsEnabled(false);
				//$file->setCreatedOn(new DateTimeValue(time()) );
			}
			
			$sourcePath = $path . $doc_name;

			$handle = fopen($sourcePath, "r");
			$size = filesize($sourcePath);
			$file_content = fread($handle, $size);
			fclose($handle);
			
			$file_dt['name'] = $file->getFilename();
			$file_dt['size'] = strlen($file_content);
			$file_dt['tmp_name'] = $sourcePath; //TEMP_PATH . DIRECTORY_SEPARATOR . rand() ;
			$extension = trim(get_file_extension($sourcePath));
			
			$file_dt['type'] = Mime_Types::instance()->get_type($extension);
			if(!trim($file_dt['type'])) {
				$file_dt['type'] = 'text/html';
			}

			$file->save();
			$file->removeFromAllWorkspaces();
			$file->addToWorkspace($project);
			
			$old_revs = $file->getRevisions();
			foreach ($old_revs as $rev) {
				$rev->delete();
			}
			
			$revision = $file->handleUploadedFile($file_dt, true, '');
			
			$file_date = new DateTimeValue(filemtime($sourcePath));

			$revision->setCreatedOn($file_date);
			$revision->setUpdatedOn($file_date);
			$revision->save();
			
			$file->setCreatedOn($file_date);
			$file->setUpdatedOn($file_date);
			
			$file->save();
			
			$ws = $file->getWorkspaces();
			ApplicationLogs::createLog($file, $ws, ApplicationLogs::ACTION_ADD);
			
			ImportLogger::instance()->log("   File: $doc_name [$ws_id]");
			print "   File: $doc_name [$ws_id]\r\n";
			
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			ImportLogger::instance()->logError("$e\r\n**************************************************");
			print "\r\n\r\nERROR: $e\r\n";
		}
	} // uploadDocument


}


?>

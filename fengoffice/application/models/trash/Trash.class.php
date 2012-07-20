<?php
class Trash {
	function purge_trash() {
		Env::useHelper("permissions");
		$days = config_option("days_on_trash", 0);
		$count = 0;
		if ($days > 0) {
			$date = DateTimeValueLib::now()->add("d", -$days);
			
			$objects = Objects::findAll(array("conditions" => array("`trashed_by_id` > 0 AND `trashed_on` < ?", $date), "limit" => 100));
			
			foreach ($objects as $object) {
		    	
				$concrete_object = Objects::findObject($object->getId());
		    	
		    	if (!$concrete_object instanceof ContentDataObject) continue;
		    	if ($concrete_object instanceof MailContent && $concrete_object->getIsDeleted() > 0) continue;
		    	
				try {
					DB::beginWork();
					
					if ($concrete_object instanceof MailContent) {
						$concrete_object->delete(false);
					} else {
						$concrete_object->delete();
					}
					ApplicationLogs::createLog($concrete_object, ApplicationLogs::ACTION_DELETE);
					
					DB::commit();
					$count++;
				} catch (Exception $e) {
					DB::rollback();
					Logger::log("Error delting object in purge_trash: " . $e->getMessage(), Logger::ERROR);
				}
			}
		}
		return $count;
	}
}
?>
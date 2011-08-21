<?php
chdir(dirname(__FILE__));
define("CONSOLE_MODE", true);
define('PUBLIC_FOLDER', 'public');
include "init.php";
include APPLICATION_PATH . "/cron_functions.php";

header("Content-type: text/plain");

session_commit(); // we don't need sessions
@set_time_limit(0); // don't limit execution of cron, if possible

$events = CronEvents::getDueEvents();

foreach ($events as $event) {
	if (!$event->getEnabled()) continue;
	$errordate = DateTimeValueLib::now()->add("m", 30);
	/* setting this date allows to rerun the event in 30 minutes if a fatal error occurs
	   during its execution, which would prevent the event from being rescheduled */
	$event->setDate($errordate);
	$event->save();
	$function = $event->getName();
	try {
		$function();
	} catch (Error $e) {
		echo $e->getMessage();
	}
	
	if ($event->getRecursive()) {
		try {
			DB::beginWork();
			$nextdate = DateTimeValueLib::now()->add("m", $event->getDelay());
			$event->setDate($nextdate);
			$event->save();
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			echo $e->getMessage();
		}
	}
}

?>
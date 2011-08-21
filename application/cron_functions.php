<?php

function check_mail() {
	_log("Checking email...");
	MailUtilities::getmails(null, $err, $succ, $errAcc, $received);
	_log("$received emails fetched.");
}

function purge_trash() {
	_log("Purging trash...");
	$count = Trash::purge_trash();
	_log("$count objects deleted.");
}

function check_upgrade() {
	_log("Checking for upgrades...");
	$version_feed = VersionChecker::check(true);
	if (!($version_feed instanceof VersionsFeed)) {
		_log("Error checking for upgrades.");
	} else {
		if ($version_feed->hasNewVersions(product_version())) {
			_log("Found new versions.");
		} else {
			_log("No new versions.");
		}
	}
}

function send_reminders() {
	_log("Sending reminders...");
	Env::useHelper('permissions');
	$sent = 0;
	$ors = ObjectReminders::getDueReminders();
	foreach ($ors as $or) {
		$function = $or->getType();
		try {
			$ret = 0;
			Hook::fire($function, $or, $ret);
			$sent += $ret;
		} catch (Exception $ex) {
			_log("Error sending reminder: " . $ex->getMessage());
		}
	}
	_log("$sent reminders sent.");
}

function send_password_expiration_reminders(){
	$password_expiration_notification = config_option('password_expiration_notification', 0);
	if($password_expiration_notification > 0){
		_log("Sending password expiration reminders...");
		$count = UserPasswords::sendPasswordExpirationReminders();
		_log("$count password expiration reminders sent.");
	}
}

function send_notifications_through_cron() {
	_log("Sending notifications...");
	$count = Notifier::sendQueuedEmails();
	_log("$count notifications sent.");
}

function delete_mails_from_server() {
	try {
		_log("Checking mail accounts to delete mails from server...");
		$count = MailUtilities::deleteMailsFromServerAllAccounts();
		_log("Deleted $count mails from server...");
	} catch (Exception $e) {
		_log("Error deleting mails from server: " . $e->getMessage());
	}
}

function clear_tmp_folder($dir = null) {
	try {
		if (!$dir) $dir = ROOT . "/tmp";
		$handle = opendir($dir);
		$left = 0;
		$deleted = 0;
		while (false !== ($f = readdir($handle))) {
			if ($f != "." && $f != "..") {
				if ($f == "CVS") {
					$left++;
					continue;
				}
				$path = "$dir/$f";
				if (is_file($path)) {
					$mtime = @filemtime($path);
					if ($mtime && (time() - $mtime > 60*60*24*2)) {
						// if temp file older than 2 days
						@unlink($path);
						if (is_file($path)) {
							$left++;
						} else {
							$deleted++;
						}
					} else {
						$left++;
					}
				} else if (is_dir($path)) {
					$deleted += clear_tmp_folder($path);
					if (is_dir($path)) $left++;
				}
			}
		}
		closedir($handle);
		if ($dir == ROOT . "/tmp") _log("$deleted tmp files deleted.");
		else if ($left == 0) @rmdir($dir);

		return $deleted;
	} catch (Exception $e) {
		_log("Error clearing tmp folder: " . $e->getMessage());
	}
}

function _log($message) {
	echo date("Y-m-d H:i:s") . " - $message\n";
}

?>
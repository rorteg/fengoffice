<?php

/**
 * Notifier class has purpose of sending various notification to users. Primary
 * notification method is email
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class Notifier {

	/** Supported transports **/
	const MAIL_TRANSPORT_MAIL = 'mail()';
	const MAIL_TRANSPORT_SMTP = 'smtp';

	/** Secure connection values **/
	const SMTP_SECURE_CONNECTION_NO  = 'no';
	const SMTP_SECURE_CONNECTION_SSL = 'ssl';
	const SMTP_SECURE_CONNECTION_TLS = 'tls';

	/**
	 * Cached value of echange compatible config option
	 *
	 * @var boolean
	 */
	static public $exchange_compatible = null;

	function notifyAction($object, $action, $log_data) {
		if (!$object instanceof ProjectDataObject) {
			return;
		}
		$subscribers = $object->getSubscribers();
		if (!is_array($subscribers) || count($subscribers) == 0) return;
		if ($action == ApplicationLogs::ACTION_ADD) {
			if ($object instanceof Comment) {
				//self::newObjectComment($object, $subscribers);
				// check ProjectDataObject::onAddComment()
			} else {
				self::objectNotification($object, $subscribers, logged_user(), 'new');
			}
		} else if ($action == ApplicationLogs::ACTION_EDIT) {
			self::objectNotification($object, $subscribers, logged_user(), 'modified');
		} else if ($action == ApplicationLogs::ACTION_TRASH) {
			self::objectNotification($object, $subscribers, logged_user(), 'deleted');
		} else if ($action == ApplicationLogs::ACTION_CLOSE) {
			self::objectNotification($object, $subscribers, logged_user(), 'closed');
		} else if ($action == ApplicationLogs::ACTION_SUBSCRIBE) {
			self::objectNotification($object, Users::findByIds(explode(",", $log_data)), logged_user(), 'subscribed');
		}
	}
	function shareObject(ProjectDataObject $object, $people) {
		self::objectNotification($object, $people, logged_user(), 'share');
	}
	
	static function objectNotification($object, $people, $sender, $notification, $description = null, $descArgs = null, $properties = array(), $links = array()) {
		if (!is_array($people) || !count($people)) {
			return; // nothing here...
		} // if
		if ($sender instanceof User) {
			$sendername = $sender->getDisplayName();
			$senderemail = $sender->getEmail();
			$senderid = $sender->getId();
		} else {
			$sendername = owner_company()->getName();
			$senderemail = owner_company()->getEmail();
			if (!is_valid_email($senderemail)) {
				$senderemail = 'noreply@fengoffice.com';
			}
			$senderid = 0;
		}
		
		$type = $object->getObjectTypeName();
		$typename = lang($object->getObjectTypeName());
		$uid = $object->getUniqueObjectId();
		$name = $object instanceof Comment ? $object->getObject()->getObjectName() : $object->getObjectName();
		if (!isset($description)) {
			$description = "$notification notification $type desc";
			$descArgs = array(clean($object->getObjectName()), $sendername);
		}
		if (!isset($descArgs)) {
			$descArgs = array();
		}
		if ($object->columnExists('text') && trim($object->getColumnValue('text'))) {
			$text = escape_html_whitespace(convert_to_links(clean("\n" . $object->getColumnValue('text'))));
			$properties['text'] = $text;
		}
		$second_properties = array();
		//$properties['unique id'] = $uid;
		if ($object->columnExists('description') && trim($object->getColumnValue('description'))) {
			$text = escape_html_whitespace(convert_to_links(clean("\n" . $object->getColumnValue('description'))));
			$properties['description'] = $text;
		}
		if ($object instanceof ProjectFile && $object->getType() == ProjectFiles::TYPE_DOCUMENT) {
			$revision = $object->getLastRevision();
			if (trim($revision->getComment())) {
				$text = escape_html_whitespace(convert_to_links(clean("\n" . $revision->getComment())));
				$properties['revision comment'] = $text;
			}
		}
				
		tpl_assign('object', $object);
		tpl_assign('properties', $properties);
		tpl_assign('second_properties', $second_properties);
		
		$emails = array();
		foreach($people as $user) {
			if ($user->getId() != $senderid && $object->canView($user)) {
				// send notification on user's locale and with user info
				$locale = $user->getLocale();
				Localization::instance()->loadSettings($locale, ROOT . '/language');
				$workspaces = $object->getUserWorkspaces($user);
				$ws = "";
				$plain_ws = "";
				foreach ($workspaces as $w) {
					if ($ws) $ws .= ", ";
					if ($plain_ws) $plain_ws .= ", ";
					$css = get_workspace_css_properties($w->getColor());
					$ws .= "<span style=\"$css\">" . $w->getPath() . "</span>";
					$plain_ws .= $w->getPath();
				}
				$properties['workspace'] = $ws;
				
				tpl_assign('links', $links);
				tpl_assign('properties', $properties);
				tpl_assign('description', langA($description, $descArgs));
				$from = self::prepareEmailAddress($senderemail, $sendername);
				$emails[] = array(
					"to" => array(self::prepareEmailAddress($user->getEmail(), $user->getDisplayName())),
					"from" => self::prepareEmailAddress($senderemail, $sendername),
					"subject" => $subject = lang("$notification notification $type", $name, $uid, $typename, $plain_ws),
					"body" => tpl_fetch(get_template_path('general', 'notifier'))
				);
			}
		} // foreach
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');

		self::queueEmails($emails);
	}
		
	/**
	 * Send new comment notification to message subscribers
	 *
	 * @param Comment $comment
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function newObjectComment(Comment $comment, $all_subscribers) {
		$object = $comment->getObject();
		$subscribers = array();
		foreach($all_subscribers as $subscriber) {
			if ($comment->isPrivate()) {
				if ($subscriber->isMemberOfOwnerCompany()) {
					$subscribers[] = $subscriber;
				} // if
			} else {
				$subscribers[] = $subscriber;
			} // of
		} // foreach
		self::objectNotification($comment, $subscribers, logged_user(), 'new', "new comment posted", array($object->getObjectName()));
	} // newObjectComment
	
	/**
	 * Reset password and send forgot password email to the user
	 *
	 * @param User $user
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function forgotPassword(User $user, $token = null) {
		$administrator = owner_company()->getCreatedBy();

		//$new_password = $user->resetPassword(true);
		tpl_assign('user', $user);
		//tpl_assign('new_password', $new_password);
		tpl_assign('token',$token);
		if (! $administrator instanceof User) return;

		// send email in user's language
		$locale = $user->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		
		self::sendEmail(
			array(self::prepareEmailAddress($user->getEmail(), $user->getDisplayName())),
			self::prepareEmailAddress($administrator->getEmail(), $administrator->getDisplayName()),
			lang('reset password'),
			tpl_fetch(get_template_path('forgot_password', 'notifier')), $type = 'text/html', $encoding = '8bit'
		); // send
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // forgotPassword
	
	/**
	 * Send password expiration notification email to user 
	 *
	 * @param User $user
	 * @param string $expiration_days
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function passwordExpiration(User $user, $expiration_days) {
		tpl_assign('user', $user);
		tpl_assign('exp_days', $expiration_days);

		if (! $user instanceof User) return;
		
		$locale = $user->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		
		self::queueEmail(
			array(self::prepareEmailAddress($user->getEmail(), $user->getDisplayName())),
			self::prepareEmailAddress("noreply@fengoffice.com", "noreply@fengoffice.com"),
			lang('password expiration reminder'),
			tpl_fetch(get_template_path('password_expiration_reminder', 'notifier'))
		); // send
		
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // passwordExpiration

	/**
	 * Send new account notification email to the user whose accout has been created
	 * (welcome message)
	 *
	 * @param User $user
	 * @param string $raw_password
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function newUserAccount(User $user, $raw_password) {
		tpl_assign('new_account', $user);
		tpl_assign('raw_password', $raw_password);

		$sender = $user->getCreatedBy() instanceof User ? $user->getCreatedBy() : owner_company()->getCreatedBy();
		
		$locale = $user->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		
		self::queueEmail(
			array(self::prepareEmailAddress($user->getEmail(), $user->getDisplayName())),
			self::prepareEmailAddress($sender->getEmail(), $sender->getDisplayName()),
			lang('your account created'),
			tpl_fetch(get_template_path('new_account', 'notifier'))
		); // send
		
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // newUserAccount


	/**
	 * Send task due notification to the list of users ($people)
	 *
	 * @param ProjectTask $task Due task
	 * @param array $people
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function objectReminder(ObjectReminder $reminder) {
		$object = $reminder->getObject();
		$context = $reminder->getContext();
		$type = $object->getObjectTypeName();
		$date = $object->getColumnValue($context);
		$several_event_subscribers = false;
		Env::useHelper("format");
		$isEvent = ($object instanceof ProjectEvent) ? true : false;			
			
		if ($reminder->getUserId() == 0) {
			$people = $object->getSubscribers();
			if ($isEvent){
				$several_event_subscribers = true;
				$aux = array();
				foreach ($people as $person){        //grouping people by different timezone
					$time = $person->getTimezone();
					if (isset ($aux["$time"])){
						$aux["$time"][] = $person;
					}else{
						$aux["$time"] = array($person);
					}
				}
				foreach ($aux as $tz => $group){
					$string_date = format_datetime($date, 0, $tz);
					self::objectNotification($object, $group, null, "$context reminder", "$context $type reminder desc", array($object->getObjectName(), $string_date));
				}
			}
		} else {
			$people = array($reminder->getUser());
			if ($isEvent){
				$string_date = format_datetime($date, 0, $reminder->getUser()->getTimezone());
			}else{
				$string_date = $date->format("Y/m/d H:i:s");
			}
		}
		
		if(!$several_event_subscribers) {
			if (!isset($string_date)) $string_date = format_datetime($date);
			self::objectNotification($object, $people, null, "$context reminder", "$context $type reminder desc", array($object->getObjectName(), $string_date));
		}
	} // taskDue
	
	/**
	 * Send event notification to the list of users ($people)
	 *
	 * @param ProjectEvent $event Event
	 * @param array $people
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function notifEvent(ProjectEvent $object, $people, $notification, $sender) {
		if(!is_array($people) || !count($people) || !$sender instanceof User) {
			return; // nothing here...
		} // if
				
		$uid = $object->getUniqueObjectId();
		$name = $object->getObjectName();
		$type = $object->getObjectTypeName();
		$typename = lang($object->getObjectTypeName());
		$description = lang("$notification notification event desc", $object->getObjectName(), $sender->getDisplayName());
		
		$properties= array();
		
		$second_properties = array();
		$second_properties['unique id'] = $uid;
		//$properties['view event'] = str_replace('&amp;', '&', $object->getViewUrl());

		tpl_assign('object', $object);
		tpl_assign('description', $description);
		tpl_assign('properties', $properties);
		
		$emails = array();
		foreach($people as $user) {
			if ($user->getId() != $sender->getId()) {
				// send notification on user's locale and with user info
				$locale = $user->getLocale();
				Localization::instance()->loadSettings($locale, ROOT . '/language');
				$workspaces = $object->getUserWorkspaces($user);
				$ws = "";
				foreach ($workspaces as $w) {
					if ($ws) $ws .= ", ";
					$css = get_workspace_css_properties($w->getColor());
					$ws .= "<span style=\"$css\">" . $w->getPath() . "</span>";
				}
				$properties['workspace'] = $ws;
				$properties['date'] = Localization::instance()->formatDescriptiveDate($object->getStart(), $user->getTimezone());
				if ($object->getTypeId() != 2) {
					$properties['meeting_time'] = Localization::instance()->formatTime($object->getStart(), $user->getTimezone());
				}
		
				$properties['accept or reject invitation help, click on one of the links below'] = '';
			//	$properties['accept invitation'] = get_url('event', 'change_invitation_state', array('at' => 1, 'e' => $object->getId(), 'u' => $user->getId()));
			//	$properties['reject invitation'] = get_url('event', 'change_invitation_state', array('at' => 2, 'e' => $object->getId(), 'u' => $user->getId()));
				$links = array(
					array('img' => get_image_url("/16x16/complete.png"), 'text' => lang('accept invitation'), 'url' => get_url('event', 'change_invitation_state', array('at' => 1, 'e' => $object->getId(), 'u' => $user->getId()))),
					array('img' => get_image_url("/16x16/del.png"), 'text' => lang('reject invitation'), 'url' => get_url('event', 'change_invitation_state', array('at' => 2, 'e' => $object->getId(), 'u' => $user->getId()))),
				);
				tpl_assign('links', $links);
				
				tpl_assign('properties', $properties);
				tpl_assign('second_properties', $second_properties);
				
				$from = self::prepareEmailAddress($sender->getEmail(), $sender->getDisplayName());
				$emails[] = array(
					"to" => array(self::prepareEmailAddress($user->getEmail(), $user->getDisplayName())),
					"from" => self::prepareEmailAddress($sender->getEmail(), $sender->getDisplayName()),
					"subject" => $subject = lang("$notification notification $type", $name, $uid, $typename, $ws),
					"body" => tpl_fetch(get_template_path('general', 'notifier'))
				);
			}
		} // foreach
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		self::queueEmails($emails);
	} // notifEvent
	
	 /** Send event notification to the list of users ($people)
	 *
	 * @param ProjectEvent $event Event
	 * @param array $people
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function notifEventAssistance(ProjectEvent $event, EventInvitation $invitation, $from_user, $invs = null) {
		if ((!$event instanceof ProjectEvent) || (!$invitation instanceof EventInvitation) 
			|| (!$event->getCreatedBy() instanceof User) || (!$from_user instanceof User)) {
			return;
		}
		
		tpl_assign('event', $event);
		tpl_assign('invitation', $invitation);
		tpl_assign('from_user', $from_user);

		$assist = array();
		$not_assist = array();
		$pending = array();
		
		if (isset ($invs)){
			foreach ($invs as $inv){
				if ($inv->getUserId() == ($from_user->getId())) continue;
				$decision = $inv->getInvitationState();
				$user_name = Users::findById($inv->getUserId())->getDisplayName();
				if ($decision == 1){
					$assist[] = ($user_name);
				}else if ($decision == 2){
					$not_assist[] = ($user_name);
				}else{
					$pending[] = ($user_name);
				}
			}
		}

		tpl_assign('assist', $assist);
		tpl_assign('not_assist', $not_assist);
		tpl_assign('pending', $pending);
		
		$people = array($event->getCreatedBy());
		$recepients = array();
		foreach($people as $user) {
			$locale = $user->getLocale();
			Localization::instance()->loadSettings($locale, ROOT . '/language');
			$date = Localization::instance()->formatDescriptiveDate($event->getStart(), $user->getTimezone());
			if ($event->getTypeId() != 2) $date .= " " . Localization::instance()->formatTime($event->getStart(), $user->getTimezone());
			$workspaces = implode(", ", $event->getUserWorkspacePaths($user));
			
			// GET WS COLOR
			
			$workspace_color = $event->getWorkspaceColorsCSV(logged_user()->getWorkspacesQuery());
			
			tpl_assign('workspace_color', $workspace_color);			
			tpl_assign('workspaces', $workspaces);
			tpl_assign('date', $date);
			self::queueEmail(
				array(self::prepareEmailAddress($user->getEmail(), $user->getDisplayName())),
				self::prepareEmailAddress($from_user->getEmail(), $from_user->getDisplayName()),
				lang('event invitation response') . ': ' . $event->getSubject(),
				tpl_fetch(get_template_path('event_inv_response_notif', 'notifier'))
			); // send
		} // foreach
		
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // notifEvent

	// ---------------------------------------------------
	//  Milestone
	// ---------------------------------------------------

	/**
	 * Milestone has been assigned to the user
	 *
	 * @param ProjectMilestone $milestone
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	function milestoneAssigned(ProjectMilestone $milestone) {
		if($milestone->isCompleted()) {
			return true; // milestone has been already completed...
		} // if
		if(!($milestone->getAssignedTo() instanceof User)) {
			return true; // not assigned to user
		} // if

		// GET WS COLOR
		$workspace_color = $milestone->getWorkspaceColorsCSV(logged_user()->getWorkspacesQuery());

		tpl_assign('milestone_assigned', $milestone);
		tpl_assign('workspace_color', $workspace_color);
		
		if (! $milestone->getCreatedBy() instanceof User) return;
		
		$locale = $milestone->getAssignedTo()->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		if ($milestone->getDueDate() instanceof DateTimeValue) {
			$date = Localization::instance()->formatDescriptiveDate($milestone->getDueDate(), $milestone->getAssignedTo()->getTimezone());
			tpl_assign('date', $date);
		}
		
		return self::queueEmail(
			array(self::prepareEmailAddress($milestone->getAssignedTo()->getEmail(), $milestone->getAssignedTo()->getDisplayName())),
			self::prepareEmailAddress($milestone->getCreatedBy()->getEmail(), $milestone->getCreatedByDisplayName()),
			lang('milestone assigned to you', $milestone->getName(), $milestone->getProject() instanceof Project ? $milestone->getProject()->getName() : ''),
			tpl_fetch(get_template_path('milestone_assigned', 'notifier'))
		); // send
		
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // milestoneAssigned

	/**
	 * Task has been assigned to the user
	 *
	 * @param ProjectTask $task
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	function taskAssigned(ProjectTask $task) {
		if($task->isCompleted()) {
			return true; // task has been already completed...
		} // if
		if(!($task->getAssignedTo() instanceof User)) {
			return true; // not assigned to user
		} // if
		
		
		// GET WS COLOR
		$workspace_color = $task->getWorkspaceColorsCSV(logged_user()->getWorkspacesQuery());

		tpl_assign('task_assigned', $task);
		tpl_assign('workspace_color', $workspace_color);

		$locale = $task->getAssignedTo()->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		
		if ($task->getDueDate() instanceof DateTimeValue) {
			$date = Localization::instance()->formatDescriptiveDate($task->getDueDate(), $task->getAssignedTo()->getTimezone());
			tpl_assign('date', $date);
		}

		self::queueEmail(
			array(self::prepareEmailAddress($task->getAssignedTo()->getEmail(), $task->getAssignedTo()->getDisplayName())),
			self::prepareEmailAddress($task->getUpdatedBy()->getEmail(), $task->getUpdatedByDisplayName()),
			lang('task assigned to you', $task->getTitle(), $task->getProject() instanceof Project ? $task->getProject()->getName() : ''),
			tpl_fetch(get_template_path('task_assigned', 'notifier'))
		); // send
		
		$locale = logged_user() instanceof User ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // taskAssigned



	// ---------------------------------------------------
	//  Util functions
	// ---------------------------------------------------

	/**
	 * This function will prepare email address. It will return $name <$email> if both
	 * params are presend and we are not in exchange compatibility mode. In other case
	 * it will just return email
	 *
	 * @param string $email
	 * @param string $name
	 * @return string
	 */
	static function prepareEmailAddress($email, $name = null) {
		if(trim($name) && !self::getExchangeCompatible()) {
			return trim($name) . ' <' . trim($email) . '>';
		} else {
			return trim($email);
		} // if
	} // prepareEmailAddress

	/**
	 * Returns true if exchange compatible config option is set to true
	 *
	 * @param void
	 * @return boolean
	 */
	static function getExchangeCompatible() {
		if(is_null(self::$exchange_compatible)) {
			self::$exchange_compatible = config_option('exchange_compatible', false);
		} // if
		return self::$exchange_compatible;
	} // getExchangeCompatible

	/**
	 * Send an email using Swift (send commands)
	 *
	 * @param string to_address
	 * @param string from_address
	 * @param string subject
	 * @param string body, optional
	 * @param string content-type,optional
	 * @param string content-transfer-encoding,optional
	 * @return bool successful
	 */
	static function sendEmail($to, $from, $subject, $body = false, $type = 'text/plain', $encoding = '8bit') {
		$ret = false;
		if (config_option('notification_from_address')) {
			$from = config_option('notification_from_address');
		}
		Hook::fire('notifier_email_body', $body, $body);
		Hook::fire('notifier_email_subject', $subject, $subject);
		Hook::fire('notifier_send_email', array(
			'to' => $to,
			'from' => $from,
			'subject' => $subject,
			'body' => $body,
			'type' => $type,
			'encoding' => $encoding,
		), $ret);
		if ($ret) return true;
		
		Env::useLibrary('swift');

		$mailer = self::getMailer();
		if(!($mailer instanceof Swift_Mailer)) {
			throw new NotifierConnectionError();
		} // if

		$smtp_address = config_option("smtp_address");
		if (config_option("mail_transport") == self::MAIL_TRANSPORT_SMTP && $smtp_address) {
			$pos = strrpos($from, "<");
			if ($pos !== false) {
				//$sender_address = trim(substr($from, $pos + 1), "> ");
				$sender_name = trim(substr($from, 0, $pos));
			} else {
				$sender_name = "";
			}
			$from = array($smtp_address => $sender_name);
		} else {
			$pos = strrpos($from, "<");
			if ($pos !== false) {
				$sender_name = trim(substr($from, 0, $pos));
				$sender_address = str_replace(array("<",">"),array("",""), trim(substr($from, $pos, strlen($from)-1)));
			} else {
				$sender_name = "";
				$sender_address = $from;
			}
			$from = array($sender_address => $sender_name);
		}

		//Create the message
		$message = Swift_Message::newInstance($subject)
		  ->setFrom($from)
		  ->setBody($body)
		  ->setContentType($type)
		;
				
		$message->setContentType($type);
		$to = MailUtilities::prepareEmailAddresses(implode(",", $to));
		foreach ($to as $address) {
			$message->addTo(array_var($address, 0), array_var($address, 1));
		}
		$result = $mailer->send($message);
		
		return $result;
	} // sendEmail
	
	static function queueEmail($to, $from, $subject, $body = false, $type = 'text/html', $encoding = '8bit') {
		$cron = CronEvents::getByName('send_notifications_through_cron');
		if ($cron instanceof CronEvent && $cron->getEnabled()) {
			$qm = new QueuedEmail();
			if (!is_array($to)) {
				$to = array($to);
			}
			$qm->setTo(implode(";", $to));
			$qm->setFrom($from);
			$qm->setSubject($subject);
			$qm->setBody($body);
			$qm->save();
		} else {
			self::sendEmail($to, $from, $subject, $body, $type, $encoding);
		}
	}
	
	static function queueEmails($emails) {
		foreach ($emails as $email) {
			self::queueEmail(
				array_var($email, 'to'),
				array_var($email, 'from'),
				array_var($email, 'subject'),
				array_var($email, 'body'),
				array_var($email, 'type', 'text/html'),
				array_var($email, 'encoding', '8bit')
			);
		}
	}
	
	static function sendQueuedEmails() {
		$date = DateTimeValueLib::now();
		$date->add("d", -2);
		$emails = QueuedEmails::getQueuedEmails($date);
		if (count($emails) <= 0) return 0;
		
		Env::useLibrary('swift');
		$mailer = self::getMailer();
		if(!($mailer instanceof Swift_Mailer)) {
			throw new NotifierConnectionError();
		} // if
		$fromSMTP = config_option("mail_transport", self::MAIL_TRANSPORT_MAIL) == self::MAIL_TRANSPORT_SMTP && config_option("smtp_authenticate", false);
		$count = 0;
		foreach ($emails as $email) {
			try {
				
				$body = $email->getBody();
				$subject = $email->getSubject();
				Hook::fire('notifier_email_body', $body, $body);
				Hook::fire('notifier_email_subject', $subject, $subject);
				
				if ($fromSMTP && config_option("smtp_address")) {
					$pos = strrpos($email->getFrom(), "<");
					if ($pos !== false) {
						$sender_name = trim(substr($email->getFrom(), 0, $pos));
					} else {
						$sender_name = "";
					}
					$from = array(config_option("smtp_address") => $sender_name);
				} else {
					$pos = strrpos($email->getFrom(), "<");
					if ($pos !== false) {
						$sender_name = trim(substr($email->getFrom(), 0, $pos));
						$sender_address = str_replace(array("<",">"),array("",""), trim(substr($email->getFrom(), $pos, strlen($email->getFrom())-1)));
					} else {
						$sender_name = "";
						$sender_address = $email->getFrom();
					}
					$from = array($sender_address => $sender_name);
				}
				$message = Swift_Message::newInstance($subject)
				  ->setFrom($from)
				  ->setBody($body)
				  ->setContentType('text/html')
				;
				
				$to = MailUtilities::prepareEmailAddresses(implode(",", explode(";", $email->getTo())));
				foreach ($to as $address) {
					$message->addTo(array_var($address, 0), array_var($address, 1));
				}
				$result = $mailer->send($message);

				$email->delete();
				$count++;
			} catch (Exception $e) {
				Logger::log('There has been a problem when sending the Queued emails. Problem:'.$e->getTraceAsString());
			}
		}
		return $count;
	}

	/**
	 * This function will return SMTP connection. It will try to load options from
	 * config and if it fails it will use settings from php.ini
	 *
	 * @param void
	 * @return Swift
	 */
	static function getMailer() {
		$mail_transport_config = config_option('mail_transport', self::MAIL_TRANSPORT_MAIL);

		// Emulate mail() - use NativeMail
		if($mail_transport_config == self::MAIL_TRANSPORT_MAIL) {
			return Swift_Mailer::newInstance(Swift_MailTransport::newInstance());
			// Use SMTP server
		} elseif($mail_transport_config == self::MAIL_TRANSPORT_SMTP) {

			// Load SMTP config
			$smtp_server = config_option('smtp_server');
			$smtp_port = config_option('smtp_port', 25);
			$smtp_secure_connection = config_option('smtp_secure_connection', self::SMTP_SECURE_CONNECTION_NO);
			$smtp_authenticate = config_option('smtp_authenticate', false);
			if($smtp_authenticate) {
				$smtp_username = config_option('smtp_username');
				$smtp_password = config_option('smtp_password');
			} // if

			switch($smtp_secure_connection) {
				case self::SMTP_SECURE_CONNECTION_SSL:
					$transport = 'ssl';
					break;
				case self::SMTP_SECURE_CONNECTION_TLS:
					$transport = 'tls';
					break;
				default:
					$transport = null;
			} // switch
			
			$mail_transport = Swift_SmtpTransport::newInstance($smtp_server, $smtp_port, $transport);		
			$smtp_authenticate = $smtp_username != null;
			if($smtp_authenticate) {
				$mail_transport->setUsername($smtp_username);
				$mail_transport->setPassword($smtp_password);
			}
			return Swift_Mailer::newInstance($mail_transport);
			
			// Somethings wrong here...
		} else {
			return null;
		} // if
	} // getMailer

	function sendReminders() {
		include_once "application/cron_functions.php";
		send_reminders();
	}
	
} // Notifier

?>


    About Feng Office 1.7.5
    =========================

    Feng Office is a free and open source Web Office, project management
    and collaboration tool, licensed under the Affero GPL 3 license.

    visit:
        * http://www.fengoffice.com/
        * http://fengoffice.com/web/forums/
        * http://fengoffice.com/web/wiki/
        * http://sourceforge.net/projects/opengoo

    contact:
        * contact@fengoffice.com


    System requirements
    ===================

    Feng Office requires a web server, PHP (5.0 or greater) and MySQL (InnoDB
    support recommended). The recommended web server is Apache.

    Feng Office is not PHP4 compatible and it will not run on PHP versions prior
    to PHP5.

    Recommended:

    PHP 5.2+
    MySQL 4.1+ with InnoDB support
    Apache 2.0+

        * PHP    : http://www.php.net/
        * MySQL  : http://www.mysql.com/
        * Apache : http://www.apache.org/

    Alternatively, if you just want to test Feng Office and you don't care about security
    issues with your files, you can download XAMPP, which includes all that is needed
    by Feng Office (Apache, PHP 5, MySQL) in a single download.
    You can configure MySQL to support InnoDB by commenting or removing
    the line 'skip-innodb' in the file '<INSTALL_DIR>/etc/my.cnf'.

        * XAMPP  : http://www.apachefriends.org/en/xampp


    Installation
    ============

    1. Download Feng Office - http://fengoffice.com/web/community/
    2. Unpack and upload to your web server
    3. Direct your browser to the public/install directory and follow the installation
    procedure

    You should be finished in a matter of minutes.
    
    4. Some functionality may require further configuration, like setting up a cron job.
    Check the wiki for more information: http://fengoffice.com/web/wiki/doku.php/setup
    
    WARNING: Default memory limit por PHP is 8MB. As a new Feng Office install consumes about 10 MB,
    administrators could get a message similar to "Allowed memory size of 8388608 bytes exhausted".
    This can be solved by setting "memory_limit=32" in php.ini.    


    Upgrade instructions
    ====================
    
    1. Backup you current installation (important!)
    2. Download Feng Office 1.7 - http://fengoffice.com/web/community/
    3. Unpack into your Feng Office installation, overwriting your previous files and folders,
    	but keeping your config and upload folders.
    5. Go to <your_feng>/public/upgrade in your browser and choose to upgrade
    	from your current version to 1.7
    6. Refresh your browser or clear its cache to load new javascript, css and images.   

    
	Open Source Libraries 
	=====================
	
	The following open source libraries and applications have been adapted to work with Feng Office:
	- ActiveCollab 0.7.1 - http://www.activecollab.com
	- ExtJs - http://www.extjs.com
	- Reece Calendar - http://sourceforge.net/projects/reececalendar
	- Swift Mailer - http://www.swiftmailer.org
	- Open Flash Chart - http://teethgrinder.co.uk/open-flash-chart
	- Slimey - http://slimey.sourceforge.net
	- FCKEditor - http://www.fckeditor.net
	- JSSoundKit - http://jssoundkit.sourceforge.net
	- PEAR - http://pear.php.net


	Changelog
	=========

	Since 1.7.5
	-----------
    bugfix: Problem when upload an image from CKEditor
    bugfix: Missing validation for some filenames when upload an image.
    bugfix: Restore password does not work properly.
    bugfix: The system was creating a new user for receiving attachments
    bugfix: Error with searchable_objects
    bugfix: Solved issue with Spanish lang files
    bugfix: Issue when unarchive a file
    bugfix: Issue related with subscriptions
    bugfix: Unworked pending tasks in report

    usability: Changed layout when create a new system user.
    usability: Enhancements related to workspace panel
    usability: Was simplified the user creation from the new task form
    usability: Improvements to getting started wizard

    system: Resolved issues about SSL and APC implementation
    system: Issue when update the system	
    

	Since 1.7.4.1
	-------------
    bugfix: Issue with mail queries
    bugfix: Added config option for user of other companies    
    bugfix: Resolved issue arround reports
    bugfix: Issues with wizard (v1.0)
    bugfix: Issue with timezone on calendar.    
    
    usability: Implemented lang support for getting started wizard
        
	Since 1.7.4
	------------
	bugfix: Bug when creating contact from user.
	bugfix: Fixed security issues.
	bugfix: Issue with js config option in task module.
	bugfix: Send button dissapears when sending email.
	bugfix: Improved <show all> query for linked objects.
	bugfix: Incompatibility with IE9.
	bugfix: Language improvements.
	bugfix: Warnings of combine.php.
	bugfix: Issue when adding/edditing company.
	bugfix: Issue with reports.
	bugfix: Issue in Date Format Config Handler.
	bugfix: Solved issue when viewing different emails in different browser tabs.
	bugfix: Email forward is not included in conversation.
	
	usability: Disable edit workspace button if user does not have permissions, "add workspace" text added.
	usability: New wizard to get started using Feng Office.
	
	system: Check for mb_string library when installing.
	system: Images spriting.
	system: Use of static.fengoffice.com for static content.
	
	Since 1.7.3.3
	------------
	bugfix: Security issue with cookies.
	bugfix: Issue in combine.php.
	bugfix: Issue with report conditions with date comparisons.
	bugfix: Fixed langs for es_es and es_la.
	bugfix: Logs were not written for subtasks when trashing, untrashing, archiving and unarchiving tasks.
	bugfix: Permissions issue at users history log.
	bugfix: Tasks reports shows priority codes instead of showing prioriy names.
	bugfix: Fixed: reminder didn't use timezone in some cases.
	bugfix: Class name removed from search results.
	bugfix: File revisions in search results sometimes shows html code.
	bugfix: When reporting objects with list custom properties only the first value was shown.
	bugfix: When composing an email, fields to, cc and bcc not always select the email from the list if clicking it.
	bugfix: Event creator appears twice in the email when other user accepted the invitation.
	bugfix: Email signature does not change when composing an email and changing the from address.
	bugfix: Issue with upload control when attaching a file that already exists in the system.
	bugfix: Bug with CKEditor and Chrome when adding an image to a document.
	bugfix: Checkbox were not cleared when reloading contact list.
	bugfix: Issue when having more than one tab with Feng Office and viewing emails in each tab. 
	bugfix: Templates with assigned tasks are not shown properly at tasks widgets.
	bugfix: Date format config handler is not initializing correctly for some values.
	bugfix: Escape characters for report results.
	bugfix: Issue with calendar reports.
	bugfix: Issue with tasks widget in dashboard.
	bugfix: Issue with milestone due dates in listing.
	bugfix: Issue with conversation list permissions when viewing an email.
	bugfix: LinkedObjectManager issue fixed.
	
	system: CKEditor upgraded to version 3.5.1
	
	feature: Added APC cache for config options and other frequent objects.
	feature: Added languages: Bulgarian, Suomi and Svenska.
	feature: Upgraded languages: cs_cz, de_de, el_gr, it_it, ja_jp, nb_no, nl_nl, pl_pl, zh_cn.
	feature: Improved the message for emails in outbox when logging in.
	feature: Added some indexes and improved parts of the code to improve performance.
	feature: Added more checks when sending emails.
	feature: Performance improved for reminder and email pollings.
	feature: Show label "Tags" when tags panel is collapsed.
	feature: Javascript injection prevention.
	
	
	Since 1.7.3.2
	------------
	bugfix: Sending emails fixed to avoid sending an email more than one time when receiving 'mysql has gone away' msg in the middle of the process.
	
	
	Since 1.7.3.1
	------------
	bugfix: HtmlPurifier library updated
	
	
	Since 1.7.3
	------------
	bugfix: Email address autocomplete click fix.
	bugfix: Fixed calendar when rendering some evnets (week & day views).
	bugfix: Error when sending notifications through cron.
	bugfix: Improved email parsing for some email encodings.
	bugfix: Improved email list refresh after taking some actions.
	bugfix: Overview - view as list does not order emails properly.
	bugfix: Emails are not ordered properly by 'to' field.
	bugfix: Email permissions when sending.
	bugfix: Email background sending process improved. 
	
	
	Since 1.7.2
	------------
	feature: User config option to show/hide file revisions in search results.
	feature: Config option to send emails with "spam" in subject to the junk folder.
	
	system: CKEditor upgraded to version 3.4.
	
	bugfix: Allow browser's context menu when right clicking email editor.
	bugfix: Cannot send email when in_reply_to_id header is invalid.
	bugfix: Cannot send email when invalid addresses are input.
	bugfix: Deleting emails does not refresh the mail list.
	bugfix: Cannot delete mails from server.
	bugfix: Problems with some attachment filenames.
	bugfix: Tools - mailer fixed.
	
	
	Since 1.7.1
	------------
	bugfix: Cannot attach files from computer when composing an email.
	bugfix: Error when sending mail (with sync sent mails enabled).
	bugfix: Tab with no content was appearing after 1.7.1.
	
	
	Since 1.7
	------------
	feature: Tasks subtypes definition.
	feature: Add/edit email account now allows to set the user asociated to the account.
	feature: Forward email puts forwarded email in the conversation's workspaces.
	feature: Login using email address or username.
	feature: Direct object url in object view.
	feature: Config option to view empty milestones.
	feature: Notify user when there are unsent emails in outbox.
	feature: Multiline email address fields.
	feature: Enclosing search criteria with quotes (") will search for the full coicidence.
	feature: More task status filters in task listing.
	feature: Export all contacts to vcard.
	feature: Save sent emails in email server (through imap).
	feature: Adding object with "All" ws selected, shows workpsace selector opened.
	feature: Config option to put email received replies in the conversation's workspaces.
	feature: Trash and Archived icons position switched.
	feature: Added languages: Turkish, Lithuanian.
	
	system: Swift mailer library updated to version 4.0.6
	
	bugfix: Fixed deprecated calls.
	bugfix: When instantiating a task template task sometimes is asociated to two workspaces.
	bugfix: Error when editing a task of a template.
	bugfix: Draft emails drag & drop not working.
	bugfix: If search criteria length is less than 3 the search does not return all the results. 
	bugfix: CSV and PDF reports export do not work in IE.
	bugfix: Duplicate entry when upgrading to 1.7.
	bugfix: Cannot download files in IE.
	bugfix: "Missing langs" fixed.
	bugfix: Fixed bug at mail listing when using conversations.
	bugfix: Comments with html tags not escaped.
	bugfix: Error when sending event invitations.
	bugfix: PDF report export columns widths fixed.
	bugfix: Date issues in task listing.
	bugfix: HtmlPurifier cache folder changed.
	bugfix: Issue with documents with images in multiple tabs.	
	bugfix: Dates in event invitations does not use user's timezone. 
	bugfix: When updating subscribers list, selected ones are not remembered.
	bugfix: Archived emails are listed when "All" is selected.
	bugfix: Bug when replying some emails.
	bugfix: Creating tasks in a milestone are grouped in "Unclassified" group.
	bugfix: Names too long in monthly and overview calendar.
	bugfix: Some file icons are not shown correctly in dashboard.
	bugfix: Add subtask from "view task" view, does not save assigned to, start and due date.
	bugfix: Error when putting a string in the date value of a milestone.
	bugfix: Missing checkin/checkout logs. 
	bugfix: Charset issue in pdf reports.
	bugfix: Long tags cause filtering malfunction.
	bugfix: Dashboard document widget order is not correct.
	bugfix: Missing activity in user activiy widget.
	bugfix: When filtering by tag, grouping tasks by milestones does not work.
	bugfix: "Apply to subtasks" checkbox is not shown when task has no subtasks.
	bugfix: Event assistance email reformated.
	bugfix: Fixed problem with filenames when downloading some email attachments.
	bugfix: Fixed security issues.
	
	
	Since 1.7-rc3
	------------
	bugfix: Cannot generate pdf from reports.
	bugfix: Upgrade script fixed.

	Since 1.7-rc2
	------------
	bugfix: Double refresh necessary to view mail inbox
	bugfix: Last instance of a repetitive task not showing in calendar when repetition is until a date.
	bugfix: Task end date being incorrectly saved	
	bugfix: Object picker filtering not working correctly	
	bugfix: Email not going into workspace	
	bugfix: Reporting error sorting by Workspace
	bugfix: Solved Error: 'A[C.xtype||D]' is not a constructor when browsing workspaces
	bugfix: One calendar event appearing in wrong date in specific scenario.	
	bugfix: Task dates issue	
	bugfix: Wrong dates when deleting and restoring comments
	bugfix: Birthday dates are always shown in calendar.	
	bugfix: Time slot edit looses billing info	
	bugfix: Using drag + drop does not register in application log	
	bugfix: Parent task picker not working	
	bugfix: Empty trash bug deleted objects incorrectly	
	bugfix: Added color for urgent issues in task view 	
	bugfix: User could accept other users invitation to event.	
	bugfix: Dates issue in comments	
		
	system: Cleared warnings	
	system: Some language fixes
	
	Since 1.7-rc
	------------
	
	usability: Unclassify emails by dragging them to 'All'.
	
	bugfix: Calendar export was exporting events that the logged user was not invited to.
	bugfix: Event invitations: showing "no invitations sent" when user has permissions by group over the event.
	bugfix: Milestone selector loads data two times in task quick add.
	bugfix: Edit company action (at column in the listing) goes to edit contact.
	bugfix: Activity widget does not loads all the information.
	bugfix: ical sync stopped working after 1.7x upgrade.
	bugfix: Search finds workspaces without permissions.
	bugfix: Task dates 1 day before in task list.
	bugfix: 'Failed to upload file' when dragging an email with attachment and classifying it. 
	bugfix: Internal server error when trying to reply email without a configured account.
	bugfix: Bug with group users and subscribers.
	bugfix: Internal server error" when adding/modifying workspaces.
	bugfix: Calendar export timezone offset format for timezones > 0.
	bugfix: Error when listing files created/modified by a deleted user.
	bugfix: Download / trash revision icons are not shown in IE.
	bugfix: Workspace selector isn't drawn in workspace conditions for custom reports.
	bugfix: 'Error when ordering email reports by 'Updated by'
	bugfix: Workspace-group permissions not loaded when editing a workspace
	bugfix: Custom reports don't filter contacts correctly by workspace.
	bugfix: A type with only one custom property of type boolean cannot unset its value.
	bugfix: Custom reports cannot be filtered by more than one tag or workspace condition. Only the last one works.
	bugfix: Add/Edit workspace: Contacts show all fields as 'undefined'
	bugfix: Email auto refresh always loads page 1.
	bugfix: Clicking on 'Send Mail' when a draft is being saved doesn't send the email
	bugfix: Clicking on Search, Administration or Account or performing a search when it is already open doesn't bring it up.
	bugfix: Duplicate email messages.
	bugfix: A due date is allowed to be earlier than a start date in tasks.	
	bugfix: Forgot password doesn't work correctly when password has expired.
	bugfix: Revision required when classifying an email.
	bugfix: SQL error in calendar with MySQL older lower 5.0.
	bugfix: Event invitations are not shown correctly if the invited users are not directly assigned to the event's workspace.
	bugfix: Milestone dates are one day off if timezone is greater than GMT.
	bugfix: Duplicate title column in reports (replaced with an icon and removed in print view).
	
	system: Some language fixes
	system: Cleared non critical logged warnings
	system: Performance tweaks for object picker queries.
	
	
	Since 1.7-beta2
	---------------
	
	usability: Forwarded emails are linked to the original email.

	bugfix: when importing contacts from csv or vcard add them to the selected workspace
	bugfix: improved compatibility with vcard
	bugfix: When sending several emails, an error in one email will not stop the rest of being sent.
	bugfix: removed export buttons for task times report
	bugfix: archived milestones were still being shown
	bugfix: set the can_manage_time permission by default when creating a user
	bugfix: set all permissions when the user type is 'admin'
	bugfix: Error when sending email reminders (function 'html_to_text')
	bugfix: delete references to workspaces when deleting a group
	bugfix: Popup calendar was sometimes not displayed
	bugfix: Object picker doesn't filter correctly by type
	bugfix: Tabs are sometimes switched abruptly when content is loaded into a panel.
	
	system: small performance tweaks for listings
	system: limit the amount of reminders to send at once
	
	
	Since 1.7-beta
	--------------
	
	bugfix: Error when deleting an email from the trash.
	bugfix: Improved the workspace filter's performance
	bugfix: Missing lang: log comment projectfiles data
	bugfix: Tags of objects without workspaces were not being shown.
	bugfix: Permissions are now better considered when listing tags. (type permissions are taken into account)	
	bugfix: Error whgen editting workspace permissions assigned to a group.
	bugfix: Error when adding a subtask from the task's view
	bugfix: Current time marker in 5 day view is one day ahead.	
	bugfix: Task list view shows date one day ahead on some timezones
	usability: Don't ask whether to keep workspaces when dragging an email to a workspace if the email has no workspaces.
	
	Since 1.6.2
	-----------
	
	feature: "Latest activity" widget in dashboard.
	feature: Object views are logged and shown in the object's history.
	feature: HTML email notifications.
	feature: Added a Help panel.
	feature: Added a 5 day view for the calendar.
	feature: Export reports to CSV or PDF.
	feature: Guest Users added (read only users).
	feature: LDAP support.
	
	usability: Added an Urgent priority level for milestones.
	usability: Added an action in the emails toolbar to mark as SPAM all selected emails.		
	usability: Allow to define date properties for milestones in templates.
	usability: When sending linked attachments to an email address not belonging to a user, a guest user is created for that email address.
	usability: Email functions like Trashing, Classifying, Tagging, Archiving, Marking as Read/Unread are now snappier.	
	usability: Email listing is periodically auto refreshed.
	usability: Date format user preference is now chosen from a combobox, to avoid invalid formats.
	usability: Added versioning for file weblinks.
	
	system: Email data is stored on a separate database table to speed up email queries.
	
	bugfix: Some events were shown at the following day in the calendar, even though in the dashboard it was shown in the correct day.	
	bugfix: Some events were not being shown.
	bugfix: Event notifications arrive incorrectly.
	bugfix: A user assigned to a task could edit the task even without write permission for tasks. Now it can only complete it.	
	bugfix: Email content was not being indexed for search.
	bugfix: In some places users from a client company could see users from other client companies even if they shouldn't.	
	bugfix: Online documento content was not being indexed.
	bugfix: Forwarding an email would sometimes remove it from Inbox.	
	bugfix: In search results, dates from the previous year didn't include the year.
	bugfix: Email notifications showed wrong dates.			
	bugfix: IE8 pressing enter on quick add task adds two tasks.		
	bugfix: Other company users are not shown in filters when a user is allowed to assign tasks to external company users.	
	bugfix: Login screen looks bad in IE	
	bugfix: When adding or editing a group, all users are selected always
	bugfix: Sometimes after leaving email compose view it would refresh the whole page			
	bugfix: Drag and drop of tasks (milestones? contacts?) in monthly view not working	
	bugfix: Email To, CC and BCC autocomplete fields should not show any resulsts if there's no text after the last comma			
	bugfix: Repeating event daily X times shows X-1 repetitions.	
	bugfix: User passwords were not being deleted form the database after deleting a user.
	
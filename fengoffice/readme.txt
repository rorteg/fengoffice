
    About Feng Office 2.1 - Beta
    ================================
 
    Feng Office is a Collaboration Platform and Project Management System.
    It is licensed under the Affero GPL 3 license.
 
    For further information, please visit:
        * http://www.fengoffice.com/
        * http://fengoffice.com/web/forums/
        * http://fengoffice.com/web/wiki/
        * http://sourceforge.net/projects/opengoo
 
    Contact the Feng Office team at:
        * contact@fengoffice.com
 
 
    System requirements
    ===================
 
    Feng Office requires a running Web Server, PHP (5.0 or greater) and MySQL (InnoDB
    support recommended). The recommended Web Server is Apache.
 
    Feng Office is not PHP4 compatible and it will not run on PHP versions prior
    to PHP 5.
 
    Recommendations:

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

	Upgrade
	=======
	
	Run the upgrade script 'public/upgrade'. (Please read the notes above)
	
	Notes
	-----
    
    - Set at config/config.php the constant TABLE_PREFIX to 'fo_' before running the upgrade.
   	- After upgrading make sure to run public/upgrade/complete_migration.php to set up object permissions.
    - Custom reports are not yet migrated due to potential incompatibilites between versions. 
   
    Open Source Libraries
    =====================
   
    The following open source libraries and applications have been adapted to work with Feng Office:
    - ActiveCollab 0.7.1 - http://www.activecollab.com
    - ExtJs - http://www.extjs.com
    - jQuery - http://www.jquery.com
    - jQuery tools - http://flowplayer.org/tools/
    - jQuery Collapsible - http://phpepe.com/2011/07/jquery-collapsible-plugin.html
    - jQuery Scroll To - http://flesler.blogspot.com/2007/10/jqueryscrollto.html
    - jQuery ModCoder - http://modcoder.com/
    - H5F (HTML 5 Forms) - http://thecssninja.com/javascript/H5F
    - http://flowplayer.org/tools/
    - Reece Calendar - http://sourceforge.net/projects/reececalendar
    - Swift Mailer - http://www.swiftmailer.org
    - Open Flash Chart - http://teethgrinder.co.uk/open-flash-chart
    - Slimey - http://slimey.sourceforge.net
    - FCKEditor - http://www.fckeditor.net
    - JSSoundKit - http://jssoundkit.sourceforge.net
    - PEAR - http://pear.php.net
    - Gelsheet - http://www.gelsheet.org
 
 
    Changelog
    =========
    
    Since 2.0 RC
 	----------------
 	bugfix: Notifications not sent when no company logo is uploaded.
 	bugfix: Invited people does not show in certain conditions.
 	bugfix: Invited people query performance improvements.
 	bugfix: Email fix when enabling message_id verification.
 	bugfix: Tasks does not save progress when no estimated time is set.
 	bugfix: Templates instantiation does not susbscribe assigned users.
 	bugfix: Total tasks time report fixed date parameters.
 	bugfix: Timeslots permissions fixed, for read/write of any user type.
 	bugfix: Performance improvements in tasks list and tasks widget.
 	bugfix: Subscribers permissions fix.
 	bugfix: Fixed dimension member deletion.
 	bugfix: Fixed events fatal error.
 	bugfix: Added several missing langs (en_us, es_la).
 	bugfix: Events description goes out the event region in calendar views.
 	bugfix: Tasks name and description goes out the widget in dashboard. 
 	bugfix: Several bugfixes in task's module drag & drop.
 	bugfix: Drag & drop: Drag bugfixes when dragging from grids to dimension members.
 	bugfix: Bugfix when adding subworkspaces and error.
 	bugfix: Task reclassiffication bugfix with due date and parent data.
 	bugfix: User task toolbar's selectboxes only show "me" when filtering by anything but assigned to. 
 	bugfix: Repetitive task fixes
 	bugfix: Bugfix in google-calendar import-export cron job and synchronization.
 	bugfix: Task's quick add and quick edit is not subscribing the creator.
 	bugfix: Translation tool now includes plugin's language files.
 	
 	usability: Dimension panels' selected member color now is similar to tabs color.
 	
    
    Since 2.0.0.8
    ----------------
    bugfix: Google Calendar issues solved
	bugfix: 'Executive' users not being able to assign tasks to themseleves at some places
	bugfix: Admins and Superadmins may now unblock blocked documents
	bugfix: Subscriptions and permissions issues solved
	bugfix: Solved some issues when editing objects
	bugfix: Solved issue when classifying emails and then accesing them
	bugfix: Solved issue when adding timeslots
	bugfix: Assigned to alphabetically ordered
	bugfix: Solved issue when editing email accounts
	bugfix: Custom properties were not appearing in weblinks
	bugfix: Solved issue when sending an email
	bugfix: Solved issue where Milestones were showing wrong data
	bugfix: File revisions were not being deleted
	bugfix: Timeslots were not able to be printed
	bugfix: Issues when retrieving passwords solved
	bugfix: Solved issue when deleting timeslots
	bugfix: Solved some permissions issues
	bugfix: Solved issue when adding pictures to documents
	bugfix: Solved issues with paginations
	bugfix: Solved some compatibility issues with IE
	bugfix: People profiles can be closed
	bugfix: Trashed emails not being sent
	bugfix: Repetitive tasks issues solved
	bugfix: Solved workspace quick add issue
	bugfix: Dimension members are now searchable
	 
	usability: Sent mails synchronization reintroduced
	usability: Selecting if repetitive events should be repeated on weekends or workdays
	usability: Templates now take into account custom properties
	usability: Dimension members filtering improvements
	usability: New & improved notifications
	usability: Adavanced search feature
	usability: Added quick task edition, and improved quick task addition
	usability: Improvements when linking objects
	usability: Improvements in task dependencies
	usability: Warning when uploading different file
	usability: Google Docs compatibility through weblinks
	usability: Improved the templates usability
	usability: Workspace widget introduced
	usability: Improvement with estimated time in reports
	usability: Added estimated time information in tasks list
	usability: Deletion from dimension member edition
	usability: Archiving dimension members funciton introduced
	usability: File extension prevention upload
	usability: WYSIWYG text feature for tasks descriptions and notes
	usability: View as list/panel feature reintroduced
	usability: .odt and .fodt files indexing
	 
	system: Improved upgrade procedure
	system: Improved the sharing table
	system: Improved performance when checking emails through IMAP
	system: Improved performance within tasks list
	system: Improved performance when accessing 'Users'
	system: Improved performance with ws colours
	system: Improved performance when loading permissions and dimensions
	system: Improvements within the Plugin system
	system: Major performance improvements at the framework level
	    

 	Since 2.0 RC 1
 	----------------
 	bugfix: Uploading files fom CKEditor.
 	bugfix: Some data was not save creating a company.
 	bugfix: Error produced from documents tab - New Presentation.
 	bugfix: Problems with task dates in some views.
 	bugfix: Fatal error when you post a comment on a task page.
 	bugfix: Generation of task repetitions in new tasks.
 	bugfix: Do not let assign tasks (via drag & drop) to users that doesn't have permissions.
 	usability: Interface localization improvements.
 	system: Performance improvements.


 	Since 2.0 Beta 4
 	----------------
 	bugfix: Extracted files categorization
 	bugfix: When adding workspaces
 	bugfix: Breadcrumbs were not working fine all the time 
 	bugfix: Being able to zip/unzip files
 	security: JS Injection Slimey Fix
 	system: .pdf and .docx files contents search
 	system: Improvement when creating a new user
 	system: Plugin update engine
 	system: Plugin manager console mode 
 	system: Search in file revisions
 	system: Import/Export contacts available again
 	system: Import/Export events available again
 	system: Google Calendar Sync 	
 	system: Improvement on repeating events and tasks
 	system: Cache compatibility (i.e.: with APC)
 	usability: Completing a task closes its timeslots
 	usability: Task progress bar working along the timeslots
 	usability: Being able to change permissions in workspaces when editing
 	 	
 	
 	Since 2.0 Beta 3
 	----------------
 	
 	bugfix: Several changes in the permissions system
 	bugfix: Invalid sql queries fixed
 	bugfix: Issues with archived and trashed objects solved
 	bugfix: Issues with sharing table solved
 	bugfix: Improved IE7 and IE9 compatibility
 	bugfix: Several timeslots issues solved
 	bugfix: IMAP issue solved at Emails module
 	bugfix: Solved issue with templates
 	bugfix: Added missing tooltips at calendar 
 	bugfix: Issue when completing repetitive task solved
 	bugfix: Solved some issues with the Search engine
 	bugfix: Solved issue with timezone autodetection
 	buffix: Solved 'dimension dnx' error creating a workspace
 	usability: Permission control in member forms
 	usability: Disabling a user feature
 	usability: Resfresh overview panel after quick add
 	usability: Langs update/improvement
 	usability: Drag & Drop feature added 	
 	usability: Quick add task added, and improved
 	usability: Slight improvement with notifications
 	usability: Avoid double click at Search button (which caused performance issues)
 	usability: Permissions by group feature added
 	usability: Simple billing feature added
 	system: Security Fixes
 	system: Mail compatibility improved for different email clients 	 
 	system: Feng 2 API updated
 	system: General code cleanup
 	system: Widget Engine
 	system: Performance improvements in custom reports
 	system: Print calendar
 	system: Custom Properties


    Since 2.0 Beta 2
    ----------------
    bugfix: Fixed problem uncompressing files
    bugfix: Loading indicator hidden
 	bugfix: Search in mail contents
 	bugfix: Mail reply js error
 	bugfix: Filter members associated with deleted objects
 	bugfix: Fixed permission error creating a contact
    usability: Contact View Improvements
    usability: Navigation Improvements
    system: Permission system fixes
    system: Performance issues solved. Using permission cache 'sharing table' for listing
    system: Weblinks module migrated
    
 	
    Since 2.0 Beta 1
    ----------------
 
    bugfix: Fixed problem with context trees when editing content objects
    bugfix: Fixed template listing
    bugfix: Fixed issues when instantiating templates with milestones
    bugfix: Fixed issue deleting users from 'people' and 'users' dimension.
    bugfix: Fixed 'core_dimensions' installer
    bugfix: Z-Index fixed in object-picker and header
	usability: Selected rows style in object picker
    system: General code cleanup
    
    
    Since 1.7
    -----------
 
    system: Plugin Support
    system: Search Engine performance improved
    system: Multiple Dimensions - 'Workspaces' and 'Tags' generalization
    system: Database and Models structure changes - Each Content object identified by unique id 
    system: Email removed from core (Available as a plugin)
    system: User Profile System
    feature: PDF Quick View - View uploaded PDF's
    usability: Default Theme improved
    usability: Customizable User Interface
    
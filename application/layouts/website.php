<?php header ("Content-Type: text/html; charset=utf-8", true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
	
	<?php
	// Todo: We are telling IE9 to use IE8 engine for Feng application 
	// This header should be removed when SEncha team releases EXT JS compatibility for IE9 
	echo meta_tag('X-UA-Compatible', 'IE=8', true) ?>

	<!-- script src="http://www.savethedevelopers.org/say.no.to.ie.6.js"></script -->
	<title><?php echo clean(CompanyWebsite::instance()->getCompany()->getName()) . ' - ' . PRODUCT_NAME ?></title>
	<?php echo link_tag(with_slash(ROOT_URL)."favicon.ico", "rel", "shortcut icon") ?>
	<?php echo add_javascript_to_page("og/app.js") // loaded first because it's needed for translating?>
	<?php echo add_javascript_to_page(get_url("access", "get_javascript_translation")); ?>
	<?php //echo add_javascript_to_page(with_slash(ROOT_URL) . 'language/' . Localization::instance()->getLocale() . "/lang.js") ?>
	<?php echo meta_tag('content-type', 'text/html; charset=utf-8', true) ?>
<?php

	$version = product_version();
	if (defined('COMPRESSED_CSS') && COMPRESSED_CSS) {
		echo stylesheet_tag("ogmin.css");
	} else {
		echo stylesheet_tag('website.css');
	}
	$theme = config_option('theme', DEFAULT_THEME);
	if (is_file(PUBLIC_FOLDER . "/assets/themes/$theme/stylesheets/custom.css")) {
		echo stylesheet_tag('custom.css');
	}
	$css = array();
	Hook::fire('autoload_stylesheets', null, $css);
	foreach ($css as $c) {
		echo stylesheet_tag($c);
	}

	if (defined('COMPRESSED_JS') && COMPRESSED_JS) {
		$jss = array("ogmin.js");
	} else {
		$jss = include "javascripts.php";
	}
	Hook::fire('autoload_javascripts', null, $jss);
	if (defined('USE_JS_CACHE') && USE_JS_CACHE) {
		echo add_javascript_to_page(with_slash(ROOT_URL)."public/tools/combine.php?version=$version&type=javascript&files=".implode(',', $jss));
	} else {
		foreach ($jss as $onejs) {
			echo add_javascript_to_page($onejs);
		}
	}
	$ext_lang_file = get_ext_language_file(get_locale());
	if ($ext_lang_file)	{
		echo add_javascript_to_page("extjs/locale/$ext_lang_file");
	}
	echo add_javascript_to_page("ckeditor/ckeditor.js");
	?>
	<?php if (config_option("show_feed_links")) { ?>
		<link rel="alternate" type="application/rss+xml" title="<?php echo clean(owner_company()->getName()) ?> RSS Feed" href="<?php echo logged_user()->getRecentActivitiesFeedUrl() ?>" />
	<?php } ?>
</head>
<body id="body" <?php echo render_body_events() ?>>

<iframe name="_download" style="display:none"></iframe>

<div id="loading">
	<img src="<?php echo get_image_url("layout/loading.gif") ?>" width="32" height="32" style="margin-right:8px;" align="absmiddle"/><?php echo lang("loading") ?>...
</div>

<div id="subWsExpander" onmouseover="clearTimeout(og.eventTimeouts['swst']);" onmouseout="og.eventTimeouts['swst'] = setTimeout('og.HideSubWsTooltip()', 2000);" style="display:none;top:10px;"></div>

<?php echo render_page_javascript() ?>
<?php echo render_page_inline_js() ?>
<?php 
	$use_owner_company_logo = false;
	if (config_option('use_owner_company_logo_at_header') && owner_company()->hasLogo()) {
		$use_owner_company_logo = true; 
	}
	if (GlobalCache::isAvailable()) {
		GlobalCache::update('check_for_popup_reminders_'.logged_user()->getId(), 1);
	}
?>
<!-- header -->
<div id="header">
	<div id="headerContent">
	    <table class="headerLogoAndWorkspace"><tr><td style="width:60px">
			<div id="logodiv" <?php echo ($use_owner_company_logo ? 'style="background-image:url('.owner_company()->getLogoUrl().');"' : '') ?>></div>
		</td><td>
			<div id="wsCrumbsWrapper">
				<table><tr><td>
					<div id="wsCrumbsDiv">
						<div style="font-size:150%;display:inline;">
							<a href="#" style="display:inline;line-height:28px" onclick="og.expandSubWsCrumbs(0)"><?php echo lang('all') ?></a>
						</div>
					</div>
				</td><td>
					<div id="wsTagCrumbs"></div>
				</td></tr></table>
			</div>
		</td></tr></table>
		<div id="userboxWrapper"><?php echo render_user_box(logged_user()) ?></div>
		<div id="searchbox">
			<form name='search_form' class="internalForm" action="<?php echo ROOT_URL . '/index.php' ?>" method="get">
				<table><tr><td>
				<?php
				$search_field_default_value = lang('search') . '...';
				$search_field_attrs = array(
				'onfocus' => 'if (value == \'' . $search_field_default_value . '\') value = \'\'',
				'onblur' => 'if (value == \'\') value = \'' . $search_field_default_value . '\''); ?>
				<?php echo input_field('search_for', $search_field_default_value, $search_field_attrs) ?>
				</td>
				<td id="searchboxSearch">
					<div id="searchboxButton"></div>
					<input style="display:none" id="searchButtonReal" type="submit" />
					<input type="hidden" name="c" value="search" />
					<input type="hidden" name="a" value="search" />
					<input type="hidden" name="current" value="search" />
					<input type="hidden" id="hfVars" name="vars" value="dashboard" />
				</td>
				<td style="padding-left:10px">
					<div id="quickAdd"></div>
				</td>
				</tr></table>
			</form>
		</div>
		<?php Hook::fire('render_page_header', null, $ret) ?>
	</div>
</div>
<!-- /header -->

<!-- footer -->
<div id="footer">
	<div id="copy">
		<?php if(is_valid_url($owner_company_homepage = owner_company()->getHomepage())) { ?>
			<?php echo lang('footer copy with homepage', date('Y'), $owner_company_homepage, clean(owner_company()->getName())) ?>
		<?php } else { ?>
			<?php echo lang('footer copy without homepage', date('Y'), clean(owner_company()->getName())) ?>
		<?php } // if ?>
	</div>
	<?php Hook::fire('render_page_footer', null, $ret) ?>
	<div id="productSignature"><?php echo product_signature() ?></div>
</div>
<!-- /footer -->

<script>

// OG config options
og.hostName = '<?php echo ROOT_URL ?>';
og.sandboxName = <?php echo defined('SANDBOX_URL') ? "'".SANDBOX_URL."'" : 'false'; ?>;
og.maxUploadSize = '<?php echo get_max_upload_size() ?>';
<?php $initialWS = user_config_option('initialWorkspace');
if ($initialWS === "remember") {
	$initialWS = user_config_option('lastAccessedWorkspace', 0);
}
?>
og.initialWorkspace = '<?php echo $initialWS ?>';
<?php $qs = (trim($_SERVER['QUERY_STRING'])) ? "&" . $_SERVER['QUERY_STRING'] : "";  ?>
og.initialURL = '<?php echo ROOT_URL . "/?active_project=$initialWS" . $qs ?>';
<?php if (user_config_option("rememberGUIState")) { ?>
og.initialGUIState = <?php echo json_encode(GUIController::getState()) ?>;
<?php } ?>
<?php if (user_config_option("autodetect_time_zone", null)) {
$now = DateTimeValueLib::now(); ?>
og.usertimezone = og.calculate_time_zone(new Date(<?php echo $now->getYear() ?>,<?php echo $now->getMonth() - 1 ?>,<?php echo $now->getDay() ?>,<?php echo $now->getHour() ?>,<?php echo $now->getMinute() ?>,<?php echo $now->getSecond() ?>));
og.initialURL += '&utz=' + og.usertimezone;
<?php } ?>
og.CurrentPagingToolbar = <?php echo defined('INFINITE_PAGING') && INFINITE_PAGING ? 'og.InfinitePagingToolbar' : 'og.PagingToolbar' ?>;
og.loggedUser = {
	id: <?php echo logged_user()->getId() ?>,
	username: <?php echo json_encode(logged_user()->getUsername()) ?>,
	displayName: <?php echo json_encode(logged_user()->getDisplayName()) ?>,
	isAdmin: <?php echo logged_user()->isAdministrator() ? 'true' : 'false' ?>,
	isGuest: <?php echo logged_user()->isGuest() ? 'true' : 'false' ?>,
	tz: <?php echo logged_user()->getTimezone() ?>
};
og.zipSupported = <?php echo zip_supported() ? 1 : 0 ?>;
og.hasNewVersions = <?php
	if (config_option('upgrade_last_check_new_version', false) && logged_user()->isAdministrator()) {
		echo json_encode(lang('new Feng Office version available', "#", "og.openLink(og.getUrl('administration', 'upgrade'))"));
	} else {
		echo "false";
	}
?>;
og.config = {
	'files_per_page': <?php echo json_encode(config_option('files_per_page', 10)) ?>,
	'time_format_use_24': <?php echo json_encode(config_option('time_format_use_24', 0)) ?>,
	'days_on_trash': <?php echo json_encode(config_option("days_on_trash", 0)) ?>,
	'checkout_notification_dialog': <?php echo json_encode(config_option('checkout_notification_dialog', 0)) ?>,
	'enable_notes_module': <?php echo json_encode(module_enabled("notes")) ?>,
	'enable_email_module': <?php echo json_encode(module_enabled("email", defined('SHOW_MAILS_TAB') && SHOW_MAILS_TAB)) ?>,
	'enable_contacts_module': <?php echo json_encode(module_enabled("contacts")) ?>,
	'enable_calendar_module': <?php echo json_encode(module_enabled("calendar")) ?>,
	'enable_documents_module': <?php echo json_encode(module_enabled("documents")) ?>,
	'enable_tasks_module': <?php echo json_encode(module_enabled("tasks")) ?>,
	'enable_weblinks_module': <?php echo json_encode(module_enabled('weblinks')) ?>,
	'enable_time_module': <?php echo json_encode(module_enabled("time") && can_manage_time(logged_user(), true)) ?>,
	'enable_reporting_module': <?php echo json_encode(module_enabled("reporting")) ?>
};
og.preferences = {
	'rememberGUIState': <?php echo user_config_option('rememberGUIState') ? '1' : '0' ?>,
	'show_unread_on_title': <?php echo user_config_option('show_unread_on_title') ? '1' : '0' ?>,
	'email_polling': <?php echo json_encode(user_config_option('email_polling')) ?> ,
	'email_check_acc_errors': <?php echo json_encode(user_config_option('mail_account_err_check_interval')) ?> ,
	'date_format': <?php echo json_encode(user_config_option('date_format')) ?>,
	'start_monday': <?php echo user_config_option('start_monday') ? '1' : '0' ?>,
	'draft_autosave_timeout': <?php echo json_encode(user_config_option('draft_autosave_timeout')) ?>,
	'drag_drop_prompt': <?php echo json_encode(user_config_option('drag_drop_prompt')) ?>,
	'mail_drag_drop_prompt': <?php echo json_encode(user_config_option('mail_drag_drop_prompt')) ?>
};

Ext.Ajax.timeout = <?php echo get_max_execution_time()*1100 // give a 10% margin to PHP's timeout ?>;

var quickAdd = new og.QuickAdd({renderTo:'quickAdd'});
var searchbutton = new Ext.Button({renderTo:'searchboxButton', text: lang('search'), type:'submit', handler:function(){document.getElementById('searchButtonReal').click()} });

<?php if (!defined('DISABLE_JS_POLLING') || !DISABLE_JS_POLLING) { ?>

og.skipEmailPolling = 0;

// check if feng is running on active tab
active_element = document.activeElement;
window.onblur = function() {
	if (Ext.isIE) {
		if (active_element != document.activeElement) {
			active_element = document.activeElement;
			return;
		}
	}
	og.fengHasBrowserFocus = false;
};

window.onfocus = function() {
	og.fengHasBrowserFocus = true;
};

setInterval(function() {
	og.openLink(og.getUrl('object', 'popup_reminders'), {
		hideLoading: true,
		hideErrors: true,
		preventPanelLoad: true
	});
}, 60000);
<?php } ?>

og.loadEmailAccounts('view');
og.loadEmailAccounts('edit');
og.loggedUserHasEmailAccounts = <?php echo logged_user()->hasEmailAccounts() ? 'true' : 'false' ?>;
og.emailFilters = {};
og.emailFilters.classif = '<?php echo user_config_option('mails classification filter') ?>';
og.emailFilters.read = '<?php echo user_config_option('mails read filter') ?>';
og.emailFilters.account = '<?php echo user_config_option('mails account filter') ?>';
if (og.emailFilters.account != 0 && og.emailFilters.account != '') {
	og.emailFilters.accountName = '<?php
		$acc_id = user_config_option('mails account filter');
		$acc = $acc_id > 0 ? MailAccounts::findById($acc_id) : null; 
		echo ($acc instanceof MailAccount ? mysql_real_escape_string($acc->getName()) : ''); 
	?>';
} else og.emailFilters.accountName = '';
og.lastSelectedRow = {messages:0, mails:0, contacts:0, documents:0, weblinks:0, overview:0, linkedobjs:0, archived:0};

</script>
<?php include_once(Env::getLayoutPath("listeners"));?>
</body>
</html>

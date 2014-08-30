<?php
global $forum_copyright;

// Version: 2.0; Web
$txt['app_web_name'] = 'WebExt Framework';

$forum_copyright .= '<br><small>extended by ' . $txt['app_web_name'] . ' © 2014, Michael "Tekkla" Zorn</small>';

$txt['app_web_framework_config'] = $txt['app_web_name'];

/*****************************************************************************/
/* BASICS
/*****************************************************************************/

// States
$txt['app_web_on'] = 'On';
$txt['app_web_off'] = 'Off';

// Settings
$txt['app_web_config'] = 'Settings';
$txt['app_web_info'] = 'Informations';
$txt['app_web_init'] = 'Init...';

/* ERRORS */
$txt['app_web_error'] = 'Error';
$txt['app_web_error_general'] = 'A general error occured.';
$txt['app_web_error_404'] = 'The requested document does not exist.';
$txt['app_web_error_403'] = 'You are not allowed to access the requested document';
$txt['app_web_error_500'] = 'An internal error occured.';

// Basics
$txt['app_web_noscript'] = '<span style="color: #FF0000; font-size: 16px; border: 1px solid #FF0000; padding: 3px; width: 100%; text-align: center;">DIESE SEITE BENÖTIGT JAVASCRIPT.<br />BITTE AKTIVIERE ES IN DEINEN BRWOSEREINSTELLUNGEN.</span>';
$txt['app_web_next'] = '&gt;&gt;';
$txt['app_web_prev'] = '&lt;&lt;';

$txt['app_web_save'] = 'Save';
$txt['app_web_cancel'] = 'Cancel';

// NED texts
$txt['app_web_delete'] = 'Delete';
$txt['app_web_delete_confirm'] = 'Should data really be deleted?';
$txt['app_web_new'] = 'New';
$txt['app_web_edit'] = 'Edit';

// allow or deny
$txt['app_web_access_allow'] = 'Allowed to selected groups';
$txt['app_web_access_deny'] = 'Denied to selected groups';

/*****************************************************************************/
/* CONFIG
/*****************************************************************************/
$txt['app_web_cfg_headline'] = 'WebExt Framework Settings';

// Contenthandler
$txt['app_web_cfg_default_action'] = 'Default SMF action';
$txt['app_web_cfg_default_action_desc'] = 'Name of default action to use.';
$txt['app_web_cfg_group_global'] = 'Content';
$txt['app_web_cfg_default_app'] = 'Default app';
$txt['app_web_cfg_default_app_desc'] = 'Name of app which is used for pagecontrol';
$txt['app_web_cfg_default_ctrl'] = 'Default Controller';
$txt['app_web_cfg_default_ctrl_desc'] = 'Name of controller to call in default app.';
$txt['app_web_cfg_content_handler'] = 'Contenthandler app';
$txt['app_web_cfg_content_handler_desc'] = 'Name of app which handles the content output.';
$txt['app_web_cfg_menu_handler'] = 'Menuhandler app';
$txt['app_web_cfg_menu_handler_desc'] = 'Name of app which handles menucreation.';

// Minifier
$txt['app_web_cfg_group_minify'] = 'Minify';
$txt['app_web_cfg_css_minify'] = 'CSS Minifier';
$txt['app_web_cfg_css_minify_desc'] = 'This option activates the automatic minify process for all used CSS files. (see <a href="https://code.google.com/p/minify/">https://code.google.com/p/minify/</a>)';
$txt['app_web_cfg_js_minify'] = 'JS Minifier';
$txt['app_web_cfg_js_minify_desc'] = 'This option activates the automatic minify process for all javascripts and files. (see <a href="https://code.google.com/p/minify/">https://code.google.com/p/minify/</a>)';

// Javascript
$txt['app_web_cfg_group_js'] = 'Javascript';
$txt['app_web_cfg_js_html5shim'] = 'html5shim';
$txt['app_web_cfg_js_html5shim_desc'] = 'This option activates use of html5shim. (siehe <a href="https://code.google.com/p/html5shim/">https://code.google.com/p/html5shim/</a>)';
$txt['app_web_cfg_js_selectivizr'] = 'Selectivizr';
$txt['app_web_cfg_js_selectivizr_desc'] = 'This option activates thue use of Selectivizr. (siehe <a href="http://selectivizr.com/">http://selectivizr.com/</a>)';
$txt['app_web_cfg_js_modernizr'] = 'Modernizer';
$txt['app_web_cfg_js_modernizr_desc'] = 'This option activates the use of Modernizr. (siehe <a href="http://modernizr.com/">http://modernizr.com/</a>)';
$txt['app_web_cfg_js_fadeout_time'] = 'Fadeouttime';
$txt['app_web_cfg_js_fadeout_time_desc'] = 'Time (in milliseconds) to use as global fadeout timer.';

// Gestaltung
$txt['app_web_cfg_group_style'] = 'Visuals';
$txt['app_web_cfg_group_style_desc'] = 'Gestaltung';
$txt['app_web_cfg_bootstrap_version'] = 'Bootstrap Version';
$txt['app_web_cfg_bootstrap_version_desc'] = 'Version number of Bootstrap css framework to use. Do not forget to place the corresponding file into frameworks css folder. The filename has to use "bootstrap-version.css" or "bootstrap-versions.min.css" pattern.';
$txt['app_web_cfg_fontawesome_version'] = 'Fontawesome Version';
$txt['app_web_cfg_fontawesome_version_desc'] = 'Version number of Fontawesome css framework to use. Do not forget to place the corresponding file into frameworks css folder. The filename has to use "fontawesome-versionnumber.css" or "fontawesome-version.min.css" pattern.';

// URL Behandlung
$txt['app_web_cfg_group_url'] = 'Url conversion';
$txt['app_web_cfg_url_seo'] = 'SEO Converter';
$txt['app_web_cfg_url_seo_desc'] = 'Actives SEO url converter which searches for urls in pagecontent right before it is send to the browser. Example:  <strong>http://www.forum.tld/index.php?board=1</strong> will be converted to <strong>http://www.forum.tld/board/1</strong>';

/*****************************************************************************/
/* VALIDATORS
/*****************************************************************************/
$txt['app_web_validator_required'] = 'This field has to be set.';
$txt['app_web_validator_empty'] = 'This field is not alloed to be empty.';
$txt['app_web_validator_textrange'] = 'Strings number of chars has to be between %d and %d. The checked string contains %d chars.';
$txt['app_web_validator_textminlength'] = 'The number of chars has to be %d at minimum.';
$txt['app_web_validator_textmaxlength'] = 'The number of chars has to be %d at maximum.';
$txt['app_web_validator_numbermin'] = 'The value is not allowed to be smaller then %d.';

// Dates
$txt['app_web_validator_date_iso'] = 'Date in ISO Format (YYYY-MM-DD) expected.';
$txt['app_web_validator_date'] = 'Please provide proper date.';

// Time
$txt['app_web_validator_time24'] = 'Time in 24h format (HH:II:ss) expected';

// Number
$txt['web_validator_compare'] = 'Comparecheck failed. Checked: $1 $3 $2';

/*****************************************************************************/
/* Models
/*****************************************************************************/
$txt['app_web_model_error_field_not_exist'] = 'Column [%s] does not exist in model [%s].';

/*****************************************************************************/
/* TIMESTRINGS
/*****************************************************************************/
$txt['app_web_time_year'] = 'year';
$txt['app_web_time_years'] = 'years';
$txt['app_web_time_month'] = 'month';
$txt['app_web_time_months'] = 'months';
$txt['app_web_time_week'] = 'week';
$txt['app_web_time_weeks'] = 'weeks';
$txt['app_web_time_day'] = 'day';
$txt['app_web_time_days'] = 'days';
$txt['app_web_time_hour'] = 'hour';
$txt['app_web_time_hours'] = 'hours';
$txt['app_web_time_minute'] = 'minute';
$txt['app_web_time_minutes'] = 'minutes';
$txt['app_web_time_second'] = 'second';
$txt['app_web_time_seconds'] = 'seconds';
?>

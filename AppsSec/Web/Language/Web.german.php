<?php
global $forum_copyright;


// Version: 2.0; Web
$txt['app_web_name'] = 'WebExt Framework';

$forum_copyright .= '<br><small>erweitert durch ' . $txt['app_web_name'] . ' © 2014, Michael "Tekkla" Zorn</small>';

$txt['app_web_framework_config'] = $txt['app_web_name'];

/*****************************************************************************/
/* BASICS
/*****************************************************************************/

// States
$txt['app_web_on'] = 'An';
$txt['app_web_off'] = 'Aus';

// Settings
$txt['app_web_config'] = 'Einstellungen';
$txt['app_web_info'] = 'Informationen';
$txt['app_web_init'] = 'Initialisieren...';

/* ERRORS */
$txt['app_web_error_404'] = '<h2>404</h2>Die angeforderte Seite existiert nicht.';
$txt['app_web_error_403'] = '<h2>403</h2>Keine Zugriffsberechtigung vorhanden.';
$txt['app_web_error_500'] = '<h2>500</h2>Ein interner Fehler ist aufgetreten.';

// Basics
$txt['app_web_next'] = '&gt;&gt;';
$txt['app_web_prev'] = '&lt;&lt;';

$txt['app_web_save'] = 'Speichern';
$txt['app_web_cancel'] = 'Abbruch';

// NED texts
$txt['app_web_delete'] = 'L&ouml;schen';
$txt['app_web_delete_confirm'] = 'Daten wirklich l&ouml;schen?';
$txt['app_web_new'] = 'Neu';
$txt['app_web_edit'] = 'Bearbeiten';

// allow or deny
$txt['app_web_access_allow'] = 'Nur gewählten Gruppen anzeigen';
$txt['app_web_access_deny'] = 'Vor gewählten Gruppen verstecken';

/*****************************************************************************/
/* CONFIG
/*****************************************************************************/
$txt['app_web_cfg_headline'] = 'WebExt Framework Einstellungen';

// Inhalte
$txt['app_web_cfg_group_global'] = 'Inhaltsverarbeitung';
$txt['app_web_cfg_default_app'] = 'Standard App';
$txt['app_web_cfg_default_app_desc'] = 'Name der App, die als Standard beim Seitenaufruf geladen werden soll.';
$txt['app_web_cfg_default_ctrl'] = 'Standard Controller';
$txt['app_web_cfg_default_ctrl_desc'] = 'Name des in der Standard App aufzurufenden Controllers.';
$txt['app_web_cfg_content_handler'] = 'Content Handler App';
$txt['app_web_cfg_content_handler_desc'] = 'Name einer App, an die der auszugebende Content für weitere Aufgaben übergeben wird. Dieser Punkt ist besonders für die Integration von Portal Apps gedacht.';
$txt['app_web_cfg_menu_handler'] = 'Menu Handler App';
$txt['app_web_cfg_menu_handler_desc'] = 'Name einer App, an die die Menubuttons zur weiteren Bearbeitung übergebne werden sollen.';

// Minifier
$txt['app_web_cfg_group_minify'] = 'Minify';
$txt['app_web_cfg_css_minify'] = 'CSS Minifier';
$txt['app_web_cfg_css_minify_desc'] = 'Diese Option aktiviert die automatische Minimierung aller genutzten CSS Files. (siehe <a href="https://code.google.com/p/minify/">https://code.google.com/p/minify/</a>)';
$txt['app_web_cfg_js_minify'] = 'JS Minifier';
$txt['app_web_cfg_js_minify_desc'] = 'Diese Option aktiviert die automatische Minimierung aller genutzten Javascripte und Files. (siehe <a href="https://code.google.com/p/minify/">https://code.google.com/p/minify/</a>)';

// Javascript
$txt['app_web_cfg_group_js'] = 'Javascript';
$txt['app_web_cfg_js_html5shim'] = 'html5shim';
$txt['app_web_cfg_js_html5shim_desc'] = 'Option um html5shim auf der Seite einzusetzen. (siehe <a href="https://code.google.com/p/html5shim/">https://code.google.com/p/html5shim/</a>)';
$txt['app_web_cfg_js_selectivizr']= 'Selectivizr';
$txt['app_web_cfg_js_selectivizr_desc']= 'Option um Selectivizr auf der Seite zu nutzen. (siehe <a href="http://selectivizr.com/">http://selectivizr.com/</a>)';
$txt['app_web_cfg_js_modernizr'] = 'Modernizer Support';
$txt['app_web_cfg_js_modernizr_desc'] = 'Option um den Modernizer auf der Seite zu verwenden. (siehe <a href="http://modernizr.com/">http://modernizr.com/</a>)';
$txt['app_web_cfg_js_fadeout_time'] = 'Fadeoutzeit';
$txt['app_web_cfg_js_fadeout_time_desc'] = 'Zeit in Millisekunden, die im gesamten Framework für Fadeouttimer genutzt werden soll.';

// Gestaltung
$txt['app_web_cfg_group_style'] = 'Gestaltung';
$txt['app_web_cfg_group_style_desc'] = 'Gestaltung';
$txt['app_web_cfg_bootstrap_version'] = 'Bootstrap Version';
$txt['app_web_cfg_bootstrap_version_desc'] = 'Versionsnummer der zu verwendenen Bootstrapversion. Bitte beachten, dass diese Version auch im Framework CSS Verzeichnis mit dem Schema "bootstrap-versionsnummer.css" oder "bootstrap-versionsnummer.min.css" hinterlegt sein muss!';
$txt['app_web_cfg_fontawesome_version'] = 'Fontaweseom Version';
$txt['app_web_cfg_fontawesome_version_desc'] = 'Versionsnummer der zu verwendenden Fontawesome Bibliothek. Auch diese Version muss wie bei Bootstrab im CSS Verzeichnis des Framworks mit dem selben Namensschema hinterlegt sein.';

// URL Behandlung
$txt['app_web_cfg_group_url'] = 'Url Behandlung';
$txt['app_web_cfg_url_seo'] = 'SEO Konverter';
$txt['app_web_cfg_url_seo_desc'] = 'Damit wird vor der Ausgabe der Seite der Content auf URL untersucht und alle nicht durch das Framework generierten URL umgewandelt. Beispielsweise würde aus <strong>http://www.deinforum.tld/index.php?board=1</strong> dann <strong>http://www.deinforum.tld/board/1</strong>';

/*****************************************************************************/
/* VALIDATORS
/*****************************************************************************/
$txt['app_web_validator_required'] = 'Dieses Feld muss gesetzt sein.';
$txt['app_web_validator_empty'] = 'Dieses Feld darf nicht leer sein';
$txt['app_web_validator_textrange'] = 'Der Text darf zwischen %d und %d Zeichen lang sein. Dein Text ist %d Zeichen lang.';
$txt['app_web_validator_textminlength'] = 'Der Text muss mindestens %d Zeichen lang sein';
$txt['app_web_validator_textmaxlength'] = 'Der Text darf maxinaml %d Zeichen lang sein';
$txt['app_web_validator_numbermin'] = 'Der Wert darf nicht kleiner als %d sein';

// Dates
$txt['app_web_validator_date_iso'] = 'Datum im ISO Format (YYYY-MM-DD) erwartet';
$txt['app_web_validator_date'] = 'Es wird ein gültiges Datum erwartet';

// Time
$txt['app_web_validator_time24'] = 'Uhrzeit im 24h Format (HH:II) erwartet';

// Number
$txt['web_validator_compare'] = 'Die Vergleichsprüfung schlug fehl. Geprüft wurde: $1 $3 $2';

/*****************************************************************************/
/* Models
/*****************************************************************************/
$txt['app_web_model_error_field_not_exist'] = 'Die Spalte [%s] existiert nicht im Model [%s].';

/*****************************************************************************/
/* TIMESTRINGS
/*****************************************************************************/
$txt['app_web_time_year'] = 'Jahr';
$txt['app_web_time_years'] = 'Jahre';
$txt['app_web_time_month'] = 'Monat';
$txt['app_web_time_months'] = 'Monate';
$txt['app_web_time_week'] = 'Woche';
$txt['app_web_time_weeks'] = 'Wochen';
$txt['app_web_time_day'] = 'Tag';
$txt['app_web_time_days'] = 'Tage';
$txt['app_web_time_hour'] = 'Stunde';
$txt['app_web_time_hours'] = 'Stunden';
$txt['app_web_time_minute'] = 'Minute';
$txt['app_web_time_minutes'] = 'Minuten';
$txt['app_web_time_second'] = 'Sekunde';
$txt['app_web_time_seconds'] = 'Sekunden';
?>
<?php

namespace Web\Framework\AppsSec\Web;

use Web\Framework\Lib\App;

/**
 *
 * @author
 *         Michael
 *
 */
final class Web extends App
{

	public $secure = true;

	public $lang = true;

	public $config = array(

		// Group: Global
		'default_action' => array(
			'group' => 'global',
			'control' => 'input',
			'default' => 'forum',
		),
		'default_app' => array(
			'group' => 'global',
			'control' => 'input'
		),
		'default_ctrl' => array(
			'group' => 'global',
			'control' => 'input'
		),
		'content_handler' => array(
			'group' => 'global',
			'control' => 'input'
		),
		'menu_handler' => array(
			'group' => 'global',
			'control' => 'input'
		),

		// Group: JS Libs
		'js_html5shim' => array(
			'group' => 'js',
			'control' => 'switch',
			'default' => 0
		),
		'js_modernizr' => array(
			'group' => 'js',
			'control' => 'switch',
			'default' => 0
		),
		'js_selectivizr' => array(
			'group' => 'js',
			'control' => 'switch',
			'default' => 0
		),
		'js_fadeout_time' => array(
			'group' => 'js',
			'control' => 'number',
			'default' => 5000,
		),

		// Group: Minify
		'css_minify' => array(
			'group' => 'minify',
			'control' => 'switch',
			'default' => 0
		),
		'js_minify' => array(
			'group' => 'minify',
			'control' => 'switch',
			'default' => 0
		),

		// Bootstrap
		'bootstrap_version' => array(
			'group' => 'style',
			'control' => 'input',
			'default' => '3.1.1',
			'translate' => false
		),

		'fontawesome_version' => array(
			'group' => 'style',
			'control' => 'input',
			'default' => '4.0.3',
			'translate' => false
		),

		// Logging
		'log' => array(
			'group' => 'logging',
			'control' => 'switch',
			'default' => 0
		),
		'show_log_output' => array(
			'group' => 'logging',
			'control' => 'switch',
			'default' => 1
		),
		'log_db' => array(
			'group' => 'logging',
			'control' => 'switch',
			'default' => 1
		),
		'log_app' => array(
			'group' => 'logging',
			'control' => 'switch',
			'default' => 1
		),
		'log_handler' => array(
			'group' => 'logging',
			'control' => 'select',
			'data' => array(
				'array',
				array(
					'page' => 'Page',
					'fire' => 'FirePHP'
				),
				0
			),
			'default' => 'page',
			'translate' => false
		),

		// Url related
		'url_seo' => array(
			'group' => 'url',
			'control' => 'switch',
			'default' => 0
		),
	);
}
?>
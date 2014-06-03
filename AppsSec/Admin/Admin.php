<?php
namespace Web\Framework\AppsSec\Admin;

// Uses classes
use Web\Framework\Lib\App;
use Web\Framework\Lib\Url;
use Web\Framework\Lib\Txt;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Mainclass for secured Admin app
 * This app handles all admin stuff about the framework and apps
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package Admin (AppSec)
 * @subpackage Main
 */
class Admin extends App
{
    // With ohn languagefile
    public $lang = true;

    // Is secured app
    public $secure = true;

    // Empty config storage
    public $config;

    // Used hooks
    public $hooks = array(
        'integrate_admin_areas' => 'Web::Class::Web\Framework\AppsSec\Admin\Admin::extendAdmincenter',
        'integrate_menu_buttons' => 'Web::Class::Web\Framework\AppsSec\Admin\Admin::addAdminlink'
    );

    // Used routes
    public $routes = array(
        'index' => array(
            'route' => '/',
            'ctrl' => 'admin',
            'action' => 'index'
        ),
        'app_install' => array(
            'route' => '/[a:app_name]/install',
            'ctrl' => 'config',
            'action' => 'install'
        ),
        'app_remove' => array(
            'route' => '/[a:app_name]/remove',
            'ctrl' => 'config',
            'action' => 'remove'
        ),
        'app_config' => array(
            'method' => 'GET|POST',
            'route' => '/[a:app_name]/config',
            'ctrl' => 'config',
            'action' => 'config'
        ),
        'app_reconfig' => array(
            'route' => '/[a:app_name]/reconfig',
            'ctrl' => 'config',
            'action' => 'reconfigure'
        )
    );

    /**
     * Inserts the basic framework menu item into the SMF admin menu
     * @param array $admin_areas
     */
    public static function extendAdmincenter(&$admin_areas)
    {
        $admin_areas['web'] = array(
            'title' => 'Framework',
            'permission' => array(
                'admin_forum'
            ),
            'areas' => array(
                'web_framwork_config' => array(
                    'label' => Txt::get('config', 'admin'),
                    'custom_url' => Url::factory('admin_index')->getUrl()
                )
            )
        );
    }

    /**
     * Inserts admin link into admin menu in menu buttons.
     * @param array $menu_buttons
     */
    public static function addAdminlink(&$menu_buttons)
    {
        if (!isset($menu_buttons['admin']))
            return;

        $menu_buttons['admin']['sub_buttons']['web_admin'] = array(
            'title' => Txt::get('web_framework_config'),
            'href' => Url::factory('admin_index')->getUrl(),
            'show' => true,
            'sub_buttons' => array(),
            'is_last' => false
        );
    }
}
?>

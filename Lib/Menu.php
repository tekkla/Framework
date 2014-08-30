<?php
namespace Web\Framework\Lib;

use Web\Framework\Lib\Abstracts\ClassAbstract;

if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Handles menuhandler actions.
 * Gives r/w access to menu_buttons.
 * Checks for menu handler method in handler app and throws error if method is missing.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
final class Menu extends ClassAbstract
{

	/**
	 * Checks for a set menu handler in framework config
	 * @return boolean
	 */
	public static function hasHandler()
	{
		return Cfg::exists('Web', 'menu_handler');
	}

	/**
	 * Runs menuhandler set in config.
	 * Checks for existance of
	 * menu handler method and throws an error when method is missing.
	 * @throws MethodNotExistsError
	 */
	public static function runHandler()
	{
		// Act only on set menu handler
		if (Cfg::exists('Web', 'menu_handler'))
		{
			// Get an instance of app in which the menu handler is to find
			$app = App::create(Cfg::get('Web', 'menu_handler'));
			
			// Check for missing MenuHandler method. Throws an error if it is missing.
			if (!method_exists($app, 'menuHandler'))
				Throw new Error('Method not found.', 5000, array(
					'menuHandler', 
					Cfg::get('Web', 'menu_handler')
				));
				
				// Run MenuHandler method in app
			$app->menuHandler();
		}
	}

	/**
	 * Set menu buttons to context
	 * @param array $menu_buttons
	 */
	public static function setMenuButtons($menu_buttons)
	{
		Context::setTo('menu_buttons', $menu_buttons);
	}

	/**
	 * Get menu buttons from context
	 * @return array
	 */
	public static function getMenuButtons()
	{
		return Context::getByKey('menu_buttons');
	}

	/**
	 * Ajax refresh of menu
	 */
	public static function refreshMenu()
	{
		// Rebuild the menu structure
		setupMenuContext();
		
		// Start own outputbuffer...
		ob_start();
		
		// ... to get the result of templates menu function
		template_menu();
		
		// Create ajax command to refresh #main_menu
		Ajax::command(array(
			'selector' => '#main_menu', 
			'args' => ob_get_clean(), 
			'fn' => 'html', 
			'type' => 'dom'
		));
	}
}
?>

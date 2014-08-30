<?php
namespace Web\Framework\Lib;

use Web\Framework\Lib\Abstracts\ClassAbstract;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Parent class for all apps
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
class App extends ClassAbstract
{
	/**
	 * Holds all created app instances
	 * @var array
	 */
	private static $instances = array();
	
	/**
	 * List of loaded app names
	 * @var array
	 */
	private static $loaded_apps = array();
	
	/**
	 * List of appnames which are already initialized
	 * @var array
	 */
	private static $init_done = array();
	private static $init_stages = array();
	
	/**
	 * List of secured app, which resides within the framework folder.
	 * @var array
	 */
	private static $secure_apps = array(
		'Admin', 
		'Doc', 
		'Forum', 
		'Web'
	);
	
	/**
	 * List of apps, which can get instances of secured apps.
	 * @var unknown
	 */
	private static $allow_secure_instance = array(
		'Admin', 
		'Doc', 
		'Forum', 
		'Web'
	);
	
	/**
	 * Holds the apps name
	 * @var string
	 */
	protected $name;
	
	/**
	 * Holds the apps internal id.
	 * @var string
	 */
	protected $id;
	
	/**
	 * Secure app flag
	 * @var bool
	 */
	protected $secure = false;
	
	/**
	 * Config object
	 * @var Cfg
	 */
	private $cfg;
	
	/**
	 * Css file flag.
	 * Default: false
	 * @var bool
	 */
	protected $css = false;
	
	/**
	 * Js file flag.
	 * Default: false
	 * @var bool
	 */
	protected $js = false;
	
	/**
	 * Language file flag.
	 * Default: false
	 * @var bool
	 */
	protected $lang = false;
	
	/**
	 * Hooks storage
	 * @var array
	 */
	protected $hooks = array();
	
	/**
	 * Default routes stack
	 * @var array
	 */
	protected $routes = array();

	/**
	 * Get an unique app object
	 * @param string $name
	 * @return App
	 */
	public static function &create($name, $do_init = true)
	{
		// Create app namespace and take care of secured apps.
		$class = !in_array($name, self::$secure_apps) ? '\\Web\\Apps\\' . $name . '\\' . $name : '\\Web\\Framework\\AppsSec\\' . $name . '\\' . $name;
		
		// Generate app id
		$id = uniqid($name . '_');
		
		// Create a new app object and store id with it's unique id in the instance storage
		self::$instances[$id] = new $class($id);
		
		// Init this app instance
		if ($do_init == true)
			self::$instances[$id]->init();
			
			// Return referenc to app object in instance storage
		return self::$instances[$id];
	}

	/**
	 * Get a singleton app object
	 * @param string $name
	 * @param bool $do_init
	 * @return App
	 */
	public static function getInstance($name, $do_init = true)
	{
		// Create app namespace and take care of secured apps.
		$class = !in_array($name, self::$secure_apps) ? '\\Web\\Apps\\' . $name . '\\' . $name : '\\Web\\Framework\\AppsSec\\' . $name . '\\' . $name;
		
		// Check for already existing instance of app
		// and create new instance when none is found
		if (!array_keys(self::$instances, $name))
			self::$instances[$name] = new $class($name);
			
			// Init this app instance
		if ($do_init == true)
			self::$instances[$name]->init();
			
			// Return app instance
		return self::$instances[$name];
	}

	/**
	 * Returns an array with all created app instance.
	 * @deprecated
	 * @return array
	 */
	public static function getInstances()
	{
		return self::$instances;
	}

	/**
	 * Returns a list of loaded apps
	 * @return array
	 */
	public static function getLoadedApps()
	{
		return self::$loaded_apps;
	}

	/**
	 * Private constructor to prevent app object creation by new.
	 */
	private function __construct($id)
	{
		$app_strings = explode('\\', get_called_class());
		
		$this->name = end($app_strings);
		$this->id = $id;
		
		if (!isset(self::$init_stages[$this->name]))
			self::$init_stages[$this->name] = array(
				'config' => false, 
				'routes' => false, 
				'paths' => false, 
				'hooks' => false, 
				'lang' => false, 
				'css' => false, 
				'js' => false, 
				'hooks' => false
			);
			
			// Save app name as loaded. But only of none secured ones.
		if ($this->secure == false)
			self::$loaded_apps[$this->getName()] = $this->getName();
	}

	/**
	 * To prevent cloning this object
	 */
	private function __clone()
	{
	}

	/**
	 * Initializes the app
	 * @throws Error
	 * @return void \Web\Framework\Lib\App
	 */
	protected function init()
	{
		// Config will always be initiated. no matter what else follows.
		$this->initCfg();
		
		// Init paths
		$this->initPaths();
		
		// Apps only need once be initiated
		if (in_array($this->name, self::$init_done))
			return;
			
			// Run init methods
		$this->initRoutes();
		$this->initLang();
		$this->initHooks();
		
		// Init css and js only on non ajax requests
		if (!$this->request->isAjax())
		{
			$this->initCss();
			$this->initJs();
			
			// Finally call a possible headers methods
			if (method_exists($this, 'addHeaders'))
				$this->addHeaders();
		}
		
		// Store our apps name to be initiated
		self::$init_done[] = $this->name;
		
		return $this;
	}

	public function addPermissions(&$permissionGroups, &$permissionList)
	{
		// Init language
		$this->initLang();
		
		// We need the uncamelized name of app
		$name = String::uncamelize($this->name);
		
		foreach ( $this->perms as $group => $perms )
		{
			// Add simple permissiongroup
			$permissionGroups['membergroup']['simple'] = array(
				$name . '_simple_' . $group
			);
			
			// Add classic permissiongroup
			$permissionGroups['membergroup']['classic'] = array(
				$name . '_classic_' . $group
			);
			
			// Add permissions to group
			foreach ( $perms as $perm )
			{
				$permissionList['membergroup'][$name . '_' . $group . '_' . $perm] = array(
					false, 
					$name . '_classic_' . $group, 
					$name . '_simple_' . $group
				);
			}
		}
	}

	/**
	 * Creates an app related model object
	 * @param string $model_name The models name
	 * @return Model
	 */
	public function getModel($model_name = '')
	{
		if (!$model_name)
		{
			$dt = debug_backtrace();
			$parts = array_reverse(explode('\\', $dt[1]['class']));
			$model_name = $parts[0];
		}
		
		return Model::factory($this, $model_name);
	}

	/**
	 * Creates an app related controller object.
	 * @param string $controller_name The controllers name
	 * @return Controller
	 */
	public function getController($controller_name)
	{
		return Controller::factory($this, $controller_name);
	}

	/**
	 * Gives access on the apps config.
	 * Calling only with key returns the set value.
	 * Calling with key and value will set the apps config.
	 * Calling without any parameter will return complete app config
	 * @param string $key
	 * @param string $val
	 * @throws Error
	 * @return void boolean \Web\Framework\Lib\Cfg
	 */
	public function cfg($key = null, $val = null)
	{
		// Getting config
		if (isset($key) && !isset($val))
		{
			if (isset($this->cfg->{$key}))
				return $this->cfg->{$key};
			else
				return false;
		}
		
		// Setting config
		if (isset($key) && isset($val))
		{
			$this->cfg->{$key} = $val;
			return;
		}
		
		// Return complete config
		if (!isset($key) && !isset($val))
			return $this->cfg;
		
		Throw new Error('Values without keys can not be used in app config access');
	}

	/**
	 * Initializes the app config data by getting data from Cfg and adding
	 * config defaultvalues from app $cfg on demand.
	 */
	private function initCfg()
	{
		if (!isset($this->cfg))
			$this->cfg = new Data();
		
		$this->cfg('app', $this->name);
		$this->cfg('app_id', $this->id);
		
		// Copy existing config values to the apps config.
		if (Cfg::exists($this->name))
		{
			$config = Cfg::get($this->getName());
			
			foreach ( $config as $key => $val )
			{
				if (isset($this->config) && isset($this->config[$key]))
					$this->cfg($key, $val);
			}
		}
		
		// Is there a app config definition?
		if (isset($this->config))
		{
			// Check the loaded config against the keys of the default config
			// and set the default value if no cfg value is found
			foreach ( $this->config as $key => $cfg )
			{
				// When there is no config set but a default value defined for the app,
				// the default value will be used then
				if (!$this->cfg($key) && isset($cfg['default']))
					$this->cfg($key, $cfg['default']);
			}
		}
	}

	/**
	 * Initializes the apps paths by creating the paths and writing them into the apps config.
	 * @throws PathError
	 */
	protected function initPaths()
	{
		// Normal app or secure app?
		$app_type = $this->secure === true ? 'appssec' : 'apps';
		
		// Define app dir to look for subdirs
		$dir = Cfg::get('Web', 'dir_' . $app_type) . '/' . $this->name . '/';
		
		// Get dir handle
		$handle = opendir($dir);
		
		// Read dir
		while ( ( $file = readdir($handle) ) !== false )
		{
			// No '.' or '..'
			if ('.' == $file || '..' == $file || strpos($file, '.') === 0)
				continue;
			
			if (is_dir($dir . $file))
			{
				// Is dir and not in the excludelist? Continue if it is so.
				if (isset($this->exlude_dirs) && in_array($file, $this->exclude_dirs))
					continue;
					
					// Add dir and url to app config
				$this->cfg('dir_' . String::uncamelize($file), $dir . $file);
				$this->cfg('url_' . String::uncamelize($file), Cfg::get('Web', 'url_' . $app_type) . '/' . $this->name . '/' . $file);
			}
		}
		
		// Add apps base dir and url to app config
		$this->cfg('dir_app', Cfg::get('Web', 'dir_' . $app_type) . '/' . $this->name);
		$this->cfg('url_app', Cfg::get('Web', 'url_' . $app_type) . '/' . $this->name);
		
		// Cleanup
		closedir($handle);
	}

	/**
	 * Adds possible app specific hooks to the SMF hook system
	 */
	protected function initHooks()
	{
		if (self::$init_stages[$this->name]['hooks'])
			return;
			
			// Integrate possible permissions
		if (isset($this->perms) && !empty($this->perms))
			$this->hooks['integrate_load_permissions'] = 'Web::App::' . $this->name . '::addPermissions';
			
			// Menu entries?
		if (method_exists($this, 'addMenuButtons'))
			$this->hooks['integrate_menu_buttons'] = 'Web::App::' . $this->name . '::addMenuButtons';
			
			// Hooks to be included?
		if ($this->hooks)
		{
			foreach ( $this->hooks as $hook => $function )
				add_integration_function($hook, $function, null, false, false);
		}
		
		self::$init_stages[$this->name]['hooks'] = true;
	}

	/**
	 * Initiates apps language file
	 * @return \Web\Framework\Lib\App
	 */
	private function initLang()
	{
		global $language, $modSettings;
		
		// Init language only once
		if (self::$init_stages[$this->name]['lang'] || $this->lang !== true)
			return;
			
			// Default to the user's language.
		$lang = User::getInfo('language') ? User::getInfo('language') : $language;
		
		// Do we want the English version of language file as fallback?
		if (empty($modSettings['disable_language_fallback']) && $lang != 'english')
			$lang = 'english';
			
			// Create path to lang file
		$lang_file = $this->cfg('dir_language') . '/' . $this->name . '.' . $lang . '.php';
		
		// Try to load lang file or log error when it's missing
		if (file_exists($lang_file))
			template_include($lang_file);
		else
			log_error(sprintf(Txt::get('theme_language_error', 'SMF'), $this->name . '.' . $lang, 'App: ' . $this->name));
			
			// Set flag for initiated lang
		self::$init_stages[$this->name]['lang'] = true;
		
		return $this;
	}

	/**
	 * Initiates apps css
	 * Each app can have it's own css file. If the public property $css is set and true,
	 * at this point the app init is trying to add this css file.
	 * @return \Web\Framework\Lib\App
	 */
	public function initCss()
	{
		// Init css only once
		if (self::$init_stages[$this->name]['css'])
			return;
		
		$css_loaded = false;
		
		// Css flag set that indicates app has a css file?
		if ($this->css)
		{
			if (file_exists($this->cfg('dir_css') . '/' . $this->name . '.css'))
			{
				Css::useLink($this->cfg('url_css') . '/' . $this->name . '.css', true);
				$css_loaded = 'app';
			}
			else
			{
				// Add file when it exists or send error to the error_log
				if (file_exists(Settings::get('theme_dir') . '/css/App' . $this->name . '.css'))
				{
					Css::useLink(Settings::get('theme_url') . '/css/App' . $this->name . '.css', true);
					$css_loaded = 'theme';
				}
				else
				{
					log_error('Apps "' . $this->name . '" css flag is set to true but no css file was found in themes or default themes css app folders.');
				}
			}
		}
		
		// Instead of copying the apps css file into the themes folders the framework offers
		// a simple way to theme an app by only overriding some of the apps default css.
		// Therefor we now have a look for a file named like with AppAppnameTheme.css in the
		// current themes folder. This will only work on included app css file.
		if ($css_loaded == 'app')
		{
			if (file_exists(Settings::get('theme_dir') . '/css/App' . $this->name . 'Theme.css'))
				Css::useLink(Settings::get('theme_url') . '/css/App' . $this->name . 'Theme.css', true);
		}
		
		// Is there an additional css function in or app to run?
		if (method_exists($this, 'addCss'))
			$this->addCss();
			
			// Set flag for initiated css
		self::$init_stages[$this->name]['css'] = true;
		
		return $this;
	}

	/**
	 * Initiates apps javascript
	 * @throws Error
	 * @return \Web\Framework\Lib\App
	 */
	public function initJs()
	{
		// Init js only once
		if (self::$init_stages[$this->name]['js'])
			return;
			
			// Each app can (like css) have it's own javascript file. If you want to have this file included, you have to set the public property $js in
			// your app mainclass. Unlike the css include procedure, the $js property holds also the information where to include the apps .js file.
			// You hve to set this property to "scripts" (included on the bottom of website) or "header" (included in header section of website).
			// the apps js file is stored within the app folder structure in an directory named "js".
		if ($this->js)
		{
			if (!$this->cfg('dir_js'))
				Throw new Error('App "' . $this->name . '" js folder does not exist. Create the js folder in apps folder and add app js file or unset the js flag in your app mainclass.');
			
			if (file_exists($this->cfg('dir_js') . '/' . $this->name . '.js'))
				Javascript::useFile($this->cfg('url_js') . '/' . $this->name . '.js', false);
			else
				log_error('App "' . $this->name . '" Js file does not exist. Either create the js file or remove the js flag in your app mainclass.');
		}
		
		// Js method in app to run?
		if (method_exists($this, 'addJs'))
			$this->addJs();
			
			// Set flag for initated js
		self::$init_stages[$this->name]['js'] = true;
		
		return $this;
	}

	/**
	 * Initiates in app set routes.
	 * @throws Error
	 */
	protected function initRoutes()
	{
		// routes already initiated? Do nothing if so.
		if (self::$init_stages[$this->name]['routes'] == true)
			return;
			
			// No routes set? Set at least index as default route
		if (!$this->routes)
		{
			$this->routes[] = array(
				'name' => $this->name . '_index', 
				'route' => '/', 
				'ctrl' => 'Web', 
				'action' => 'Index'
			);
			return;
		}
		
		// Get uncamelized app name
		$app_name = String::uncamelize($this->name);
		
		// Add routes to request handler
		foreach ( $this->routes as $route )
		{
			// Create route string
			$route['route'] = $route['route'] == '/' ? '/' . $app_name : '/' . ( strpos($route['route'], '../') === false ? $app_name . $route['route'] : str_replace('../', '', $route['route']) );
			
			// Create target
			$route['target'] = array(
				// App not set means app will be set automatic.
				'app' => !isset($route['app']) ? $app_name : $route['app'], 
				'ctrl' => $route['ctrl'], 
				'action' => $route['action']
			);
			
			// The name of the route is set by the key in the routes array.
			// Is the name of type string it will be extended by the current
			// apps name.
			if (isset($route['name']))
				$route['name'] = ( !isset($route['app']) ? $app_name : $route['app'] ) . '_' . $route['name'];
				
				// Publish route
			$this->request->mapRoute($route);
		}
		
		self::$init_stages[$this->name]['routes'] = true;
	}

	/**
	 * Lazy textfunction so you do not have to write the apps name in the wanted textkey
	 * @param string $key The textkey you want to get the text from without need of app name in it.
	 * @see \Web\Framework\Lib\Lib::Txt() <code>
	 *	  <?php
	 *	  class Testapp_Controller_MyController extends Controller
	 *	  {
	 *	  $app = 'Testapp';
	 *
	 *	  public function MyControllerAction()
	 *	  {
	 *	  // use this
	 *	  $mytext = $this->txt('testapp_testtext');
	 *
	 *	  // or lazy
	 *	  $mytext = $this->txt('testtext');
	 *	  }
	 *	  }
	 *	  ?>
	 *	  </code>
	 */
	public function txt($key)
	{
		return Txt::get($key, $this->name);
	}

	/**
	 * Returns the apps config definition.
	 * If app has no definition, this method returns false.
	 * @return boolean
	 */
	public function getConfigDefinition()
	{
		if (isset($this->config))
			return $this->config;
		
		return false;
	}

	/**
	 * Is this app a secured one?
	 * @return boolean
	 */
	public function isSecure()
	{
		return isset($this->secure) && $this->secure === true ? true : false;
	}

	/**
	 * Returns the name of this app.
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the apps id
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Returns loading state of an app
	 * @param string $app_name
	 * @return boolean
	 */
	public static function isLoaded($app_name)
	{
		return in_array($app_name, self::$loaded_apps);
	}
}
?>

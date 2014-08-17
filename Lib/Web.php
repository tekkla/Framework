<?php
namespace Web\Framework\Lib;

use Web\Framework\Lib\Abstracts\SingletonAbstract;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * WebExt mainclass
 * @author Michael "Tekkla" Zorn
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Mainclass
 */
final class Web extends SingletonAbstract
{
    /**
     * Content output storage
     * @var string array
     */
    private $content = '';

    // something to integrate into smf?
    private $hooks = array(
        'integrate_menu_buttons' => 'Web::Class::Web\Framework\Lib\Web::addMenuButtons',
        'integrate_error_types' => 'Web::Class::Web\Framework\Lib\Web::addErrorTypes',
        'integrate_actions' => 'Web::Class::Web\Framework\Lib\Web::addActions',
        'integrate_pre_css_output' => 'Web::Class::Web\Framework\Lib\Css::compile',
        'integrate_pre_javascript_output' => 'Web::Class::Web\Framework\Lib\Javascript::compile'
    );

    /**
     * Starts WebExt
     */
    public function start()
    {
        try
        {
            ## Start WebExt session
            $this->session->init();

            ## Bind hooks
            foreach ( $this->hooks as $hook => $function )
                add_integration_function($hook, $function, '', false, false);

            ## Start with config params to be loaded

            // Load the config
            Cfg::load();

            $config = App::getInstance('Web', false)->getConfigDefinition();

            // Add default values for not set config
            foreach ( $config as $key => $cfg )
            {
                if (!Cfg::exists('Web', $key) && isset($cfg['default']) && $cfg['default'] !== '')
                    Cfg::set('Web', $key, $cfg['default']);
            }

            // Add dirs to config
            $dirs = array(
                // Framework directory
                'fw' => '/Web/Framework',

                // Framwork subdirectories
                'css' => '/Web/Framework/Css',
                'js' => '/Web/Framework/Js',
                'lib' => '/Web/Framework/Lib',
                'html' => '/Web/Framework/Html',
                'tools' => '/Web/Framework/Tools',
                'cache' => '/Web/Cache',

                // Public application dir
                'apps' => '/Web/Apps',

                // Secure application dir
                'appssec' => '/Web/Framework/AppsSec'
            );

            // Write dirs to config storage
            foreach ( $dirs as $key => $val )
                Cfg::set('Web', 'dir_' . $key, BOARDDIR . $val);

            // Add urls to config
            $urls = array(
                'apps' => '/Web/Apps',
                'css' => '/Web/Framework/Css',
                'js' => '/Web/Framework/Js',
                'tools' => '/Web/Framework/Tools',
                'cache' => '/Web/Cache',
                'appssec' => '/Web/Framework/AppsSec'
            );

            // Write urls to config storage
            foreach ( $urls as $key => $val )
                Cfg::set('Web', 'url_' . $key, BOARDURL . $val);

            ## FirePHP integration
            if (Cfg::get('Web', 'log_handler') == 'fire')
                require_once (Cfg::get('Web', 'dir_tools') . '/FirePHPCore/fb.php');

            ## Handling on and without ajax request
            if ($this->request->isAjax())
            {
                ## Init called app
                App::create($this->request->getApp());
            }
            else
            {
                ## Link basic framework css styles

                // Add bootstrap main css file from cdn
                Css::useLink('https://maxcdn.bootstrapcdn.com/bootstrap/' . Cfg::get('Web', 'bootstrap_version') . '/css/bootstrap.min.css');

                // Add existing local user/theme related bootstrap file or load it from cdn
                if (FileIO::exists(Settings::get('theme_dir') . '/css/bootstrap-theme.css'))
                    Css::useLink(Settings::get('theme_url') . '/css/bootstrap-theme.css');
                else
                    Css::useLink('https://maxcdn.bootstrapcdn.com/bootstrap/' . Cfg::get('Web', 'bootstrap_version') . '/css/bootstrap-theme.min.css');

                // Add existing font-awesome font icon css file or load it from cdn
                if (FileIO::exists(Cfg::get('Web', 'dir_css') . '/font-awesome-' . Cfg::get('Web', 'fontawesome_version') . '.min.css'))
                    Css::useLink(Cfg::get('Web', 'url_css') . '/font-awesome-' . Cfg::get('Web', 'fontawesome_version') . '.min.css');
                else
                    Css::useLink('https://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css');

                // Add general WebExt css file
                Css::useLink(Cfg::get('Web', 'url_css') . '/web.css');

                ## Create the js scripts

                // Add Bootstrap javascript from cdn
                Javascript::useFile('https:////maxcdn.bootstrapcdn.com/bootstrap/' . Cfg::get('Web', 'bootstrap_version') . '/js/bootstrap.min.js');

                // Add plugins file
                Javascript::useFile(Cfg::get('Web', 'url_js') . '/plugins.js');

                // Add support only when activated in config
                if (Cfg::get('Web', 'js_modernizr') == 1)
                    Javascript::useModernizr(Cfg::get('Web', 'url_js'));

                // Add support only when activated in config
                if (Cfg::get('Web', 'js_html5shim') == 1)
                    Javascript::useHtml5Shim();

                // Add the lang short notation
                Javascript::useVar('smf_lang_dictionary', Txt::get('lang_dictionary', 'SMF'), true);

                // Add global fadeout time var set in config
                Javascript::useVar('web_fadeout_time', Cfg::get('Web', 'js_fadeout_time'));

                // Add framework js
                Javascript::useFile(Cfg::get('Web', 'url_js') . '/framework.js');

                // Each theme can have it's own WebExt scripts. if present in themes script folder -> use it!
                if (FileIO::exists(Settings::get('theme_dir') . '/scripts/web.js'))
                    Javascript::useFile(Settings::get('theme_url') . '/scripts/web.js');

                ## Initialize the apps

                // Prepare apps dirs
                $dirs = array(
                    Cfg::get('Web', 'dir_appssec'),
                    Cfg::get('Web', 'dir_apps')
                );

                foreach ( $dirs as $dir )
                {
                    if (is_dir($dir))
                    {
                        if (($dh = opendir($dir)) !== false)
                        {
                            while ( ($file = readdir($dh)) !== false )
                            {
                                if ($file == '..' || $file == '.')
                                    continue;

                                App::create($file);

                            }
                            closedir($dh);
                        }
                    }
                }
            }

            ## Process the request
            $this->request->processRequest();
        }

        ## Error handling
        catch (Error $e)
        {
            $e->handle();
        }
    }

    /**
     * Runs WebExt
     */
    public function run()
    {
        try
        {
            // # Run processor
            if (SMF != 'SSI')
            {
                Content::init();

                // Do the magic only on web calls
                if (!$this->request->isWeb())
                    return;

                    // Is there an requested app?
                if (!$this->request->checkApp())
                {
                    // No. Try to find a default app set in config
                    if (Cfg::exists('Web', 'default_app'))
                        $app = Cfg::get('Web', 'default_app');
                        // No default app means that there is nothing to do for us. Let us do SMF and the forum all the work!
                    else
                        redirectexit('action=forum');
                }
                else
                {
                    // Get the requested apps name
                    $app_name = $this->request->getApp();
                }

                // Start with factoring this requested app
                $app = App::create($app_name);

                // Run methods are for apps which have to do work before the
                // the controller and action is called. So call them - if exists.
                if (method_exists($app, 'run'))
                    $app->run();

                // All app wide access check passed. Run controller and process result.
                if ($this->request->isAjax())
                {
                    // Result will be processed as ajax command list
                    $app->getController($this->request->getCtrl())->ajax();
                    $this->content = Ajax::process();
                }
                else
                {
                    // Result will be processed as html
                    $this->content = $app->getController($this->request->getCtrl())->run();

                    // No content created? Check app for onEmpty() event which maybe gives us content.
                    if (empty($this->content) && method_exists($app, 'onEmpty'))
                        $this->content = $this->app->onEmpty();

                    // Append content provided by apps onBefore() event method
                    if (method_exists($app, 'onBefore'))
                        $this->content = $app->onBefore() . $this->content;

                    // Prepend content provided by apps onAfter() event method
                    if (method_exists($app, 'onAfter'))
                        $this->content .= $app->onAfter();
                }

                // All work done, load the web template
                loadTemplate('Web');
            }
        }

        ## Error handling
        catch ( Error $e )
        {
            $e->handle();
        }
    }

    /**
     * Returns the created content.
     * Is a string on ajax and an array on full requests
     * @return Ambigous <string, array>
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Adds actions to SMF action array
     * @param array $actionArray
     */
    public static function addActions(&$actionArray)
    {
        $actionArray['forum'] = array(
            'BoardIndex.php',
            'BoardIndex'
        );

        $actionArray['web'] = array(
            'Web.php',
            'WebDummy'
        );
    }

    /**
     * Hook method to add forum button to menu
     * @param array $menu_buttons List of menu buttons
     */
    public static function addMenuButtons(&$menu_buttons)
    {
        $before = array_slice($menu_buttons, 0, 1);

        $before['forum'] = array(
            'title' => 'Forum',
            'href' => BOARDURL . '/forum/',
            'show' => true,
            'sub_buttons' => array()
        );

        $after = array_slice($menu_buttons, 1);

        $menu_buttons = $before + $after;
    }

    /**
     * Hook method to add WebExt as new errortype
     * @param array $other_error_types List of error types
     */
    public static function addErrorTypes(&$other_error_types)
    {
        $other_error_types[] = 'WebExt';
    }

    /**
     * Method to handle WebExt related hook calls
     * @param string $string WebExt hook definition
     * @return array
     */
    public static function handleHook($string)
    {
        // Extracting basic informations from function string
        $web_hook = explode('::', $string);

        // Is it valid?
        switch ($web_hook[1])
        {
            Case 'App':
                // Getting app obj
                $web_object = App::create($web_hook[2]);
                $web_method = $web_hook[3];
                break;

            Case 'Ctrl':
                $web_object = App::create($web_hook[2])->Controller($web_hook[3]);
                $web_method = 'run';
                break;

            case 'Class':
                $web_object = $web_hook[2];
                $web_method = $web_hook[3];
                break;
        }

        return array($web_object, $web_method);
    }

    public static function getState()
    {
        return self::$state;
    }
}

/**
 * Wrapper function to get an WebExt instance
 * @return Web
 */
function Web()
{
    return Web::getInstance();
}
?>

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
        'integrate_default_action' => 'Web::Class::Web\Framework\Lib\Web::getDefaultAction',
        'integrate_fallback_action' => 'Web::Class::Web\Framework\Lib\Web::getDefaultAction',
        'integrate_menu_buttons' => 'Web::Class::Web\Framework\Lib\Web::addMenuButtons',
        'integrate_actions' => 'Web::Class::Web\Framework\Lib\Web::addActions',
        'integrate_output_error' => 'Web::Class::Web\Framework\Lib\Error::analyzeError',
        'integrate_pre_css_output' => 'Web::Class::Web\Framework\Lib\Css::compile',
        'integrate_pre_javascript_output' => 'Web::Class::Web\Framework\Lib\Javascript::compile'
    );

    /**
     * Initializes the Web framework
     */
    public function init()
    {
        try
        {
            // Start WebExt session
            $this->session->init();

            // Bind hooks
            $this->initHooks();

            // Start with config params to be loaded
            $this->initConfig();

            // FirePHP integration
            if (Cfg::get('Web', 'log_handler') == 'fire')
                require_once (Cfg::get('Web', 'dir_tools') . '/FirePHPCore/fb.php');
        }
        catch ( Error $e )
        {
            echo $e->getComplete();
            exit();
        }
    }

    /**
     * Starts WebExt
     */
    public function start()
    {
        try
        {
            // link basic framework css styles
            // they can be overridden by a web.css file within the themes css folder.
            // the easiest way is to copy this basic css file into the themfolder and
            // then alter it to your needs.
            $this->addWebBasicCss();

            // create the js scripts
            $this->createJsScripts();

            // initialize the apps routes
            $this->preloadApps();

            // process the request
            $this->processRequest();

            // run the processor
            if (SMF != 'SSI')
            {
                Content::init();
                $this->run();
            }
        }
        catch ( Error $e )
        {
            $this->message->danger($e->getComplete());
            redirectexit($e->getRedirectUrl());
        }
    }

    /**
     * Starts the app initialization
     */
    private function preloadApps()
    {
        // On ajax requests only the requested app will be loaded.
        if ($this->request->isAjax())
        {
            App::create($this->request->getApp());
            return;
        }

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

                        Try
                        {
                            App::create($file);
                        }
                        catch ( Error $e )
                        {
                            echo $e->getMessage();
                        }
                    }
                    closedir($dh);
                }
            }
        }
    }

    /**
     * Initiates the basic paths and urls of framework
     */
    private function initConfig()
    {
        // Load the config
        Cfg::load();

        $config = App::getInstance('Web', false)->getConfigDefinition();

        // Add defaul values for not set config
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

            // Application dir
            'apps' => '/Web/Apps',

            // Secure Application dir
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
    }

    /**
     * Init the frameworks hooks
     */
    private function initHooks()
    {
        foreach ( $this->hooks as $hook => $function )
            add_integration_function($hook, $function, false);
    }

    /**
     * Processes the complete request (url and possible sent data)
     */
    private function processRequest()
    {
        // process the request
        $this->request->processRequest();

        // now process possible posted data
        $this->request->processPostData();
    }

    /**
     * The framework has some own css styles.
     */
    function addWebBasicCss()
    {
        // Should not be done on ajax request
        if ($this->request->isAjax())
            return;

        // Add bootstrap main css file
        Css::useBootstrap(Cfg::get('Web', 'bootstrap_version'), Cfg::get('Web', 'url_css'));

        // Add existing user/theme related bootstrap theme cdd file
        Css::useLink(Settings::get('theme_url') . '/css/bootstrap-theme.css');

        // Add font-awesome font icon css
        Css::useFontAwesome(Cfg::get('Web', 'fontawesome_version'), Cfg::get('Web', 'url_css'));

        Css::useLink(Settings::get('theme_url') . '/css/web.css');
    }

    /**
     * This method adds the frameworks javascript stuff
     */
    function createJsScripts()
    {
        // Should not be done on ajax request
        if ($this->request->isAjax())
            return;

        // Add Bootstrap Javascript
        Javascript::useBootstrap(Cfg::get('Web', 'bootstrap_version'));

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

        // each theme can have it's own webscripts. if present in themes script folder -> use it!
        if (FileIO::exists(Settings::get('theme_dir') . '/scripts/web.js'))
            Javascript::useFile(Settings::get('theme_url') . '/scripts/web.js');
    }

    /**
     * Runs the requested app
     */
    public function run()
    {
        // Do the magic only on web calls
        if (!$this->request->isWeb())
            return;

        // Here starts the magic!
        try
        {
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

            // All app wide access check passed. Now create controller object.
            $controller = $app->getController($this->request->getCtrl());

            // Ajax call or full call?
            if ($this->request->isAjax() === true)
            {
                // Run controller as ajax call
                $this->content = $controller->ajax();
            }
            else
            {
                // Normal controller run
                $this->content = $controller->run();

                // No content to show? Has app an onEmpty() method which give us content?
                if (empty($this->content) && method_exists($app, 'onEmpty'))
                    $this->content = $app->onEmpty();

                    // If app function for content onBefore() exist, prepend it to content
                $this->content = (method_exists($app, 'onBefore') ? $app->onBefore() : '') . $this->content;

                // if app function for content onAfter() exist, append it to content
                $this->content .= method_exists($app, 'onAfter') ? $app->onAfter() : '';
            }
        }
        catch ( Error $e )
        {
            if ($this->request->isAjax() === true)
            {
                Ajax::factory('log')->Error($e->getComplete());
                $this->content = Ajax::Process();
            }
            else
                $this->content = $e->getComplete();
        }

        // All work done, load the web template
        loadTemplate('Web');
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
     * Adds actions gto SMF action array
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
     * Returns in framework config set default action
     * @return string
     */
    public static function getDefaultAction()
    {
        // WebExt -- frontpage control is taken by framework
        return Cfg::get('Web', 'default_action');
    }

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

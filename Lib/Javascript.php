<?php
namespace Web\Framework\Lib;

if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Class for managing and creating of javascript objects
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
final class Javascript
{
    /**
     * Javascript output queue
     * @var array
     */
    private static $js = array();

    /**
     * Types can be "file", "script", "block", "ready" or "var".
     * @var string
     */
    private $type;

    /**
     * Header (false) or scripts (true) below body? This is the target for.
     * @var bool
     */
    private $defer = false;

    /**
     * The script to add.
     * This can be an url if its an file or a script block.
     * @var string
     */
    private $script;

    /**
     * Flag for external files.
     * External files wont be minified.
     * @var bool
     */
    private $is_external = false;

    /**
     * For double file use prevention
     * @var array
     */
    private static $files_used = array();

    /**
     * Internal filecounter
     * @var int
     */
    private static $filecounter = 0;

    /**
     * Adds an javascript objectto the content
     *
     * @param Javascript $script
     */
    public static function add(Javascript $script)
    {
        if (!$script instanceof Javascript)
            return;

        self::$js[] = $script;
    }

    /**
     * Compiles the javascript objects and adds them to the $context javascripts
     */
    public static function compile($defered)
    {
        global $context;

        // No need to run when nothing is to do
        if (empty(self::$js))
            return;

        $files = array();
        $blocks = array();
        $ready = array();

        // Include JSMin lib
        if (Cfg::get('Web', 'js_minify'))
            require_once (Cfg::get('Web', 'dir_tools') . '/min/lib/JSMin.php');

            /* @var $script Javascript */
        foreach ( self::$js as $script )
        {
            if ($script->getDefer() != $defered)
                continue;

            switch ($script->getType())
            {
                case 'file' :
                    loadJavascriptFile($script->getScript());
                    break;

                case 'script' :
                    addInlineJavascript(Cfg::get('Web', 'js_minify') ? \JSMin::minify($script->getScript()) : $script->getScript());
                    break;

                case 'block' :
                    $output = PHP_EOL . $script->getScript();
                    break;

                case 'var' :
                    $var = $script->getScript();
                    $context['javascript_vars'][$var[0]] = $var[1];
                    break;

                case 'ready' :
                    $ready[] = Cfg::get('Web', 'js_minify') ? \JSMin::minify($script->getScript()) : $script->getScript();
                    break;
            }
        }

        // Are there files to minify?
        if (Cfg::get('Web', 'js_minify'))
        {
            foreach ( $context['javascript_files'] as $name => $file )
            {
                if ($name == 'web-js-minified-above' || $name == 'web-js-minified-below')
                    continue;

                if (strpos($file['filename'], BOARDURL) !== false)
                {
                    $board_parts = parse_url(BOARDURL);
                    $url_parts = parse_url($file['filename']);

                    if ($board_parts['host'] != $url_parts['host'])
                        continue;

                        // Store filename in minify list
                    if (!in_array('/' . $url_parts['path'], $files))
                        $files[] = '/' . $url_parts['path'];

                    unset($context['javascript_files'][$name]);
                }
            }

            // Are there files to combine?
            if ($files)
            {
                // Insert filelink above or below?
                $side = $defered == true ? 'below' : 'above';

                // Write files to session so min can use it
                cache_put_data('web_min_js_' . $side, $files);

                // Add link to combined js file
                loadJavascriptFile(Cfg::get('Web', 'url_tools') . '/min/g=js-' . $side, null, 'web-js-minified-' . $side);
            }
        }

        // Create $(document).ready()
        if ($ready)
        {
            $script = '$(document).ready(function() {' . PHP_EOL;
            $script .= implode(PHP_EOL, $ready) . PHP_EOL;
            $script .= '});';

            addInlineJavascript(Cfg::get('Web', 'js_minify') ? \JSMin::minify($script) : $script);
        }
    }

    /**
     * Factory method which returns a new css object.
     * @return \Web\Framework\Lib\Javascript
     */
    public static function factory()
    {
        return new self();
    }

    /**
     * Sets the objects type.
     * Select from "file", "script", "ready", "block" or "var".
     * @param string $type
     * @throws Error
     * @return \Web\Framework\Lib\Javascript
     */
    public function setType($type)
    {
        $types = array(
            'file',
            'script',
            'ready',
            'block',
            'var'
        );

        if (!in_array($type, $types))
            Throw new Error('Javascript targets have to be "file", "script", "block", "var" or "ready"');

        $this->type = $type;
        return $this;
    }

    /**
     * Sets the objects external flag.
     * @param bool $bool
     * @return \Web\Framework\Lib\Javascript
     */
    public function setIsExternal($bool)
    {
        $this->is_external = is_bool($bool) ? $bool : false;
        return $this;
    }

    /**
     * Sets the objects script content.
     * @param string $script
     * @return \Web\Framework\Lib\Javascript
     */
    public function setScript($script)
    {
        $this->script = $script;
        return $this;
    }

    /**
     * Returns the objects type.
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /*
     * + Returns the objects external flag state.
     */
    public function getIsExternal()
    {
        return $this->is_external;
    }

    /**
     * Returns the objects script content.
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Sets the objects defer state.
     * @param bool $defer
     * @return \Web\Framework\Lib\Javascript
     */
    public function setDefer($defer)
    {
        $this->defer = is_bool($defer) ? $defer : false;
        return $this;
    }

    /**
     * Returns the objects defer state
     * @return boolean
     */
    public function getDefer()
    {
        return $this->defer;
    }

    /**
     * Adds this object to the output queue
     */
    public function toQueue()
    {
        self::add($this);
    }

    /**
     * Adds a file javascript object to the output queue
     * @param string $url
     * @param bool $defer
     * @param bool $is_external
     */
    public static function useFile($url, $defer = false, $is_external = false)
    {
        if (Request::getInstance()->isAjax())
            Ajax::command(array(
                'type' => 'act',
                'fn' => 'load_script',
                'args' => $url
            ));
        else

            self::add(self::getFile($url, $defer, $is_external));
    }

    /**
     * Register a js file by it's url
     * @param string $url file
     * @param string $target
     * @param bool $minify
     * @param bool $is_external
     */
    public static function getFile($url, $defer = false, $is_external = false)
    {
        // Do not add files already added
        if (in_array($url, self::$files_used))
            Throw new Error('Following url is already set as included js file.<br>' . $url . '<br>List of urls used:<pre>' . print_r(self::$files_used, true) . '</pre>');

        $dt = debug_backtrace();

        self::$files_used[self::$filecounter . '-' . $dt[1]['function']] = $url;

        self::$filecounter++;

        return self::factory()->setType('file')->setScript($url)->setIsExternal($is_external)->setDefer($defer);
    }

    /**
     * Adds an script javascript object to the output queue
     * @param string $script
     * @param bool $defer
     */
    public static function useScript($script, $defer = false)
    {
        self::add(self::createScript($script, $defer));
    }

    /**
     * Creates and returns a script javascript object
     * @param string $script
     * @param bool $defer
     * @return Javascript
     */
    public static function createScript($script, $defer = false)
    {
        return self::factory()->setType('script')->setDefer($defer)->setScript($script);
    }

    /**
     * Html5Shim support with autoadding
     * @param bool $defer
     */
    public static function useHtml5Shim($defer = true)
    {
        self::add(self::getHtml5Shim($defer));
    }

    /**
     * Html5Shim support without autoadding.
     * Returns the javascript object.
     * @param bool $defer
     * @return Javascript
     */
    public static function getHtml5Shim($defer = true)
    {
        return self::factory()->setType('block')->setDefer($defer)->setScript('<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->');
    }

    /**
     * Modernizr support with auto adding.
     * @param string $path
     * @param bool $defer
     */
    public static function useModernizr($path, $defer = false)
    {
        self::add(self::getModernizr($path, $defer));
    }

    /**
     * Modernizr support without auto adding.
     * Returns a javascript object.
     * @param string $path
     * @param bool $defer
     * @return Javascript
     */
    public static function getModernizr($path, $defer = false)
    {
        $url = $path . '/modernizr.min.js';

        FileIO::exists(str_replace(BOARDURL, BOARDDIR, $url), true);

        return self::factory()->setType('file')->setDefer($defer)->setScript($path . '/modernizr.min.js');
    }

    /**
     * Add Selectivizr support with autoadding
     * @param string $path
     * @param string $path_fallback_css
     */
    public static function useSelectivizir($path, $path_fallback_css)
    {
        self::add(self::getSelectivizir($path, $path_fallback_css));
    }

    /**
     * Creates a Selectivizr javascript object
     * @param string $path
     * @param string $path_fallback_css
     * @return Javascript
     */
    public static function getSelectivizir($path, $path_fallback_css)
    {
        $url = $path . '/selectivizr.js';

        FileIO::exists(str_replace(BOARDURL, BOARDDIR, $url), true);

        $script = '
		<!--[if (gte IE 6)&(lte IE 8)]>
		<script type="text/javascript" src="' . $url . '"></script>
		<noscript><link rel="stylesheet" href="' . $path_fallback_css . '" /></noscript>
		<![endif]-->';

        return self::factory()->setType('block')->setDefer(true)->setScript($script);
    }

    /**
     * Adds script as ready javascript object
     * @param unknown $script
     * @param string $defer
     */
    public static function useReady($script, $defer = true)
    {
        self::add(self::getReady($script, $defer));
    }

    /**
     * Creats a ready javascript object
     * @param string $script
     * @param bool $defer
     * @return Javascript
     */
    public static function getReady($script, $defer = true)
    {
        return self::factory()->setType('ready')->setDefer($defer)->setScript($script);
    }

    /**
     * Blocks with complete code.
     * Use this for conditional scripts!
     * @param unknown $content
     * @param string $target
     */
    public static function getBlock($script, $defer = true)
    {
        return self::factory()->setType('block')->setDefer($defer)->setScript($script);
    }

    /**
     * Adds a var javascript object
     * @param string $name
     * @param mixed $value
     * @param bool $is_string
     */
    public static function useVar($name, $value, $is_string = false)
    {
        self::add(self::getVar($name, $value, $is_string));
    }

    /**
     * Creates and returns a var javascript object
     * @param string $name
     * @param mixed $value
     * @param bool $is_string
     * @return Javascript
     */
    public static function getVar($name, $value, $is_string = false)
    {
        if ($is_string == true)
            $value = '"' . $value . '"';

        return self::factory()->setType('var')->setScript(array(
            $name,
            $value
        ));
    }

    /**
     * Autoadds Bootstrap javascript support
     * @param string $version
     * @param string $defer
     */
    public static function useBootstrap($version, $defer = false)
    {
        self::add(self::getBootstrap($version, $defer));
    }

    /**
     * Returns an file script block for the BS js lib
     * @param string $version
     * @param bool $from_cdn
     * @return string
     * @todo Make it an Script object?
     */
    public static function getBootstrap($version, $defer = false)
    {
        $url = Cfg::get('Web', 'url_js') . '/bootstrap-' . $version . '.min.js';

        FileIO::exists(str_replace(BOARDURL, BOARDDIR, $url), true);

        return self::getFile($url);
    }
}
?>

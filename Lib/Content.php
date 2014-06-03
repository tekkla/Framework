<?php

namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Content delivery class
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class Content
{
    /**
     * Storage for above content
     * @var string
     */
    private static $above;

    /**
     * Storage for content
     * @var string
     */
    private static $content;

    /**
     * Storage for below content
     * @var string
     */
    private static $below;

    /**
     * Storage for javascript objects
     * @var array
     */
    private static $javascript = array();

    /**
     * Storage for css obejcts
     * @var array
     */
    private static $css = array();

    /**
     * Inits possible set content handler and adds copyright infos about the framework to context.
     * Content handler is the name of an app. If set, an instance of this app is created and looked
     * for an initContentHandler method to be run.
     */
    public static function init()
    {
        // try to init possible content handler
        if (self::hasContentHandler() && !Request::getInstance()->isAjax())
        {
            // Get instance of content handler app
            $App = App::getInstance(self::getContenHandler());

            // Init method to call exists?
            if (method_exists($App, 'initContentHandler'))
                $App->initContentHandler();
        }

        // Add copyright infos
        Context::addCopyright('WebExt Framework &copy; 2014');
    }

    /**
     * Builds the output and echoes it to the world.
     * Before echoing the content, a set cotent handler is called and - if set in cfg - all URLs
     * will converted into SEO friendly URLs
     * @see Url
     * @throws Error
     */
    public static function build()
    {
        try
        { // Try to run set content handler on non ajax request
            if (self::hasContentHandler() && !Request::getInstance()->isAjax())
            {
                // We need the name of the ContentCover app
                $app_name = self::getContenHandler();

                // Get instance of this app
                $App = App::getInstance($app_name);

                // Check for existing ContenCover method
                if (!method_exists($App, 'runContentHandler'))
                    Throw new Error('You set the app "' . $app_name . '" as content handler but it lacks of method "runContentHandler()". Correct either the config or add the needed method to this app.');

                    // Everything is all right. Run content handler by giving the current content to it.
                self::$content = $App->runContentHandler(self::$content);
            }
        }
        catch ( Error $e )
        {
            // Add error message above content
            self::$content = '<div class="alert alert-danger alert-dismissable">' . $e->getMessage() . '</div>' . self::$content;
        }

        // Combine cached above and below with content
        $content = self::$above . self::$content . self::$below;

        // Experimental SEO url converter...
        if (Cfg::get('Web', 'url_seo'))
            $content = preg_replace_callback('@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))@', function ($match)
            {
                return Url::convertSEF($match);
            }, $content);

            // All is done... echo it to the world!
        echo $content;
    }

    /**
     * Checks for a set config handler in web config
     * @return boolean
     */
    public static function hasContentHandler()
    {
        return Cfg::exists('Web', 'content_handler');
    }

    /**
     * Returns the name of config handler set in web config
     * @return string
     */
    public static function getContenHandler()
    {
        return Cfg::get('Web', 'content_handler');
    }

    /**
     * Caches the above content by getting output buffer content.
     * Adds WebExt related divs before the content.
     * If messages are set, this messages will be shown
     */
    public static function cacheAbove()
    {
        $html = ob_get_contents();

        // Create the
        $html .= '
		<div id="web-status">
			<i class="fa fa-spinner fa-spin"></i>
		</div>
		<div id="web-message">';

        $messages = Message::getMessages();

        if ($messages)
        {
            foreach ( $messages as $msg )
                $html .= PHP_EOL . $msg->build();
        }

        $html .= '
		</div>';

        self::$above = $html;

        ob_clean();
    }

    /**
     * Caches the content by getting output buffer content.
     * Appends WebExt related divs to content.
     */
    public static function cacheContent()
    {
        $html = ob_get_contents();

        // These divs are used for info displays and page control
        $html .= '
    	<div id="web-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>
    	<div id="web-debug"></div>
    	<div id="web-tooltip"></div>
    	<div id="web-scrolltotop"></div>';

        self::$content = $html;

        ob_clean();
    }

    /**
     * Appends the tracked log entries to the content output
     */
    public static function appendLog()
    {
        if (Cfg::get('Web', 'log') && Cfg::get('Web', 'log_handler') == 'page')
        {
            echo Log::getOutput();
            Log::resetLogs();
        }
    }
}
?>

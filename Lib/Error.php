<?php

namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Base class for WebExt errors
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class Error extends \Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message = '', $code = 0, Error $previous = null, $trace = false)
    {
        global $txt;

        if (empty($message))
            $message = 'web_error';

        $message = isset($txt[$message]) ? $txt[$message] : $message;

        if ($trace)
            $message .= print_r(debug_backtrace(), true);

            // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function getComplete()
    {
        $msg = '
    	<div class="alert alert-danger">
    		<p>' . $this->getMessage() . '</p>';

        if (User::isAdmin())
        {
            $msg .= '
    		<hr>
       		<p>In file: ' . $this->getFile() . ' (Line: ' . $this->getLine() . ')</p>
    		<div style="max-height: 250px; overflow-y: scroll;">
    			<pre>' . $this->getTraceAsString() . '</pre>
    		</div>';
        }

        $msg .= '
    	</div>';

        return $msg;
    }

    public function alert($msg, $title = null, $icon = null)
    {
        if (!isset($title))
            $title = Txt::get('web_error_alert_title');

        if (!isset($icon))
            $icon = 'alert';

        $cfg = Cfg::create();

        $html = '
    	<div id="web_alert" title="' . $title . '">
   			<img src="' . $cfg->get('Web', 'url_images') . '/system/icons/' . $icon . '.png">
			<p>' . $msg . '
    	</div>';

        return $html;
    }

    public static function analyzeError($message, $error_type, $error_level, $file, $line)
    {
        if (stripos($message, 'Web') === false)
            return;

        Log::Add($error_level . ': ' . $message . '<br>File: ' . $file . ' (Line: ' . $line . ')', 'Error');

        Throw new Error($message . '<div style="max-height: 250px; overflow-y: scroll;"><pre>' . print_r(debug_backtrace(), true) . '</pre></div>');
    }
}
?>

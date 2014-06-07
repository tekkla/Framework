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
	private $redirectUrl = false;

    /**
     * Constructor
     * @param string $message
     * @param number $code
     * @param Error $previous
     * @param string $trace
     */
    public function __construct($message = '', $code = 0, Error $previous = null)
    {
    	// On empty message the default error txt will be used
    	// and error tracing will be activated
        if (!$message)
            $message = Txt::get('web_error');

        // Errors can habe two different errormessages in form of an array.
        // The first entry with text for admin users
        // The second entry with text for normal useser
        if (is_array($message))
        	$message = User::isAdmin() ? $message[0] : $message[1];

        parent::__construct($message, $code, $previous);
    }

    /**
     * (non-PHPdoc)
     * @see Exception::__toString()
     */
    public function __toString()
    {
        return $this->getComplete();
    }

    /**
     * Returns a Bootstrap formatted error message
     * @return string
     */
    public function getComplete()
    {
        $message = '
    	<div class="alert alert-danger">
    		<p>Code: ' . $this->getCode() . '<br>' . $this->getMessage() . '</p>';

        // Append more informations for admin users
        if (User::isAdmin())
        {
            $message .= '
       		<p>In file: ' . $this->getFile() . ' (Line: ' . $this->getLine() . ')</p>
    		<div style="max-height: 350px; overflow-y: scroll;">
    			<pre>' . $this->getTraceAsString() . '</pre>
    		</div>';
        }

        $message .= '
    	</div>';

        return $message;
    }

    /**
     * Hook on SMF error log
     * @param unknown $message
     * @param unknown $error_type
     * @param unknown $error_level
     * @param unknown $file
     * @param unknown $line
     * @throws Error
     */
    public static function analyzeError($message, $error_type, $error_level, $file, $line)
    {
        if (stripos($message, 'Web') === false)
            return;

        Log::Add($error_level . ': ' . $message . '<br>File: ' . $file . ' (Line: ' . $line . ')', 'Error');

        Throw new Error($message . '<div style="max-height: 250px; overflow-y: scroll;"><pre>' . print_r(debug_backtrace(), true) . '</pre></div>');
    }

    /**
     * Sets an Url to use a redirect target on error handling
     * @package string|Url $url String or Url object
     * @return Error
     */
    protected function setRedirectUrl($url)
    {
    	if ($url instanceof Url)
    		$url = $url->getUrl();

    	$this->redirectUrl = $url;
    	return $this;
    }

    /**
     * Returns the redirect url value
     */
    public function getRedirectUrl()
    {
    	return $this->hasRedirectUrl() ? $this->redirectUrl : false;
    }

    /**
     * Checks for set redirect url
     * @return boolean
     */
    public function hasRedirectUrl()
    {
    	return $this->redirectUrl !== false;
    }
}
?>

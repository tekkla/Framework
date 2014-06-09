<?php
namespace Web\Framework\Lib;

use Web\Framework\Lib\Abstracts\ErrorAbstract;
// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Class for WebExt errors handling
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
final class Error extends \Exception
{
	private $redirectUrl = false;

	private $codes = array(

	    // 0-999 Generic
	    0 => 'General',

	    // 1000-1999 Parameter and Values
	    1000 => 'WrongParameter',
	    1001 => 'MissingParameter',

	    // 2000-2999 Files
	    2000 => 'FileNotFound',
	    2001 => 'FileAlreadyExists',

	    // 3000-3999 DB

	    // 4000-4999 Config

	    // 5000-5999 Object
	    5000 => 'MethodMissing',
	    5001 => 'PropertyMissing',
	    5002 => 'PropertyNotSet',
	    5003 => 'PropertyEmpty',

	    // 6000-6999 Request
	    6000 => 'RouteMissing',
	);

	private $params = array();

	/**
	 * Error handler object
	 * @var ErrorAbstract
	 */
	private $error_handler;

    /**
     * Constructor
     * @param string $message
     * @param number $code
     * @param Error $previous
     * @param string $trace
     */
    public function __construct($message = '', $code = 0, $params = array(), Error $previous = null)
    {
        if (!array_key_exists('code', $this->codes))
            $code = 0;

        $handler_name = 'Web\\Framework\\Lib\\Errors\\' . $this->codes[$code] . 'Error';

        $this->error_handler = new $handler_name($message, $code, $params);
        $this->error_handler->process();

        parent::__construct(
            $this->error_handler->getMessage(),
            $this->error_handler->getCode(),
            $previous
        );
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
        $message = 'Code: ' . $this->getCode() . ' - ' . $this->getMessage();

        // Append more informations for admin users
        if (User::isAdmin())
        {
            $message .= '
       		<p>In file: ' . $this->getFile() . ' (Line: ' . $this->getLine() . ')</p>
    		<div style="max-height: 350px; overflow-y: scroll;">
    			<pre>' . $this->getTraceAsString() . '</pre>
    		</div>';
        }

        return $message;
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
    public function getRedirect()
    {
    	return $this->error_handler->getRedirect();
    }

    /**
     * Checks for set redirect url
     * @return boolean
     */
    public function isRedirect()
    {
    	return $this->error_handler->isRedirect();
    }

    /**
     * Returns the fatal state of the error handler
     * @return boolean
     */
    public function isFatal()
    {
        return $this->error_handler->isFatal();
    }
}
?>

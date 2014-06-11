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

	    0 => 'General',
	    1000 => 'ParameterValue',
	    2000 => 'File',
	    3000 => 'Db',
	    4000 => 'Config',
	    5000 => 'Object',
	    6000 => 'Request',
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
        ksort($this->codes);

        foreach ($this->codes as $error_code => $handler_name)
        {
        	if ($error_code >= $code)
        	    break;
        }

        $handler_class = 'Web\\Framework\\Lib\\Errors\\' . $handler_name . 'Error';

        $this->error_handler = new $handler_class($message, $code, $params);
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
        $message = '<h5>WebExt error code: ' . $this->getCode() . '</h5>';

        $message .= $this->getMessage();

        // Append more informations for admin users
        if (User::isAdmin())
        {
            $message .= '
            <h4>Source</h4>
       		<p>In file: ' . $this->getFile() . ' (Line: ' . $this->getLine() . ')</p>
       		<h4>Trace</h4>
  			<pre>' . $this->getTraceAsString() . '</pre>';
        }

        if ($this->error_handler->inBox())
            $message = '<div style="border: 2px solid darkred; background-color: #eee; padding: 5px; border-radius: 5px; margin: 10px; color: #222;">' . $message . '</div>';

        return $message;
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

    public function getAdminMessage()
    {
        return $this->error_handler->getAdminMessage();
    }

    public function getUserMessage()
    {
        return $this->error_handler->getUseRMessage();
    }

    public function logError()
    {
        return $this->error_handler->logError();
    }

    public function getLogMessage()
    {
    	return $this->error_handler->getLogMessage();
    }

    public static function endHere(Error $e)
    {
        // Usually we will never come this far but reaching this point
        // causes stopping all further actions.
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);

        $html = '
        <html>

        <head>
            <title>Error</title>
            <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
            <style type="text/css">
            * { margin: 0; padding: 0; }
            body { background-color: #aaa; color: #eee; font-family: Sans-Serif; }
            h1 { margin: 3px 0 7px; }
            p, pre { margin-bottom: 7px; }
            pre { padding: 5px; border: 1px solid #333; max-height: 400px; overflow-y: scroll; background-color: #fff; display: block; }
            </style>
        </head>

        <body>' . $e .  '</body>

        </html>';

        die($html);
    }
}
?>

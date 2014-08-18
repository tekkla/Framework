<?php
namespace Web\Framework\Lib\Abstracts;

use Web\Framework\Lib\Security;
use Web\Framework\Lib\Debug;
use Web\Framework\Lib\Log;
use Web\Framework\Lib\Error;
use Web\Framework\Lib\Ajax;
use Web\Framework\Lib\Request;
use Web\Framework\Lib\Session;
use Web\Framework\Lib\Message;
use Web\Framework\AppsSec\Web\Web;
use Web\Framework\Lib\Cfg;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Abstract class for all kind of classes.
 * This class provides some kind of simple DI interface and
 * global methods for access checks, debug and logging.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib\Abstracts
 * @namespace Web\Framework\Lib\Abstracts
 */
abstract class ClassAbstract
{

    private $di = array();

    /**
     * Small solution to provide some DI for the framework classes which extends this class
     * @param string $key Dependency to request
     * @throws Error
     * @return multitype:
     */
    public function __get($key)
    {
        if (!isset($this->di[$key]))
        {
            switch ($key)
            {
                case 'request' :
                    $obj = Request::getInstance();
                    break;

                case 'ajax' :
                    $obj = Ajax::factory();
                    break;

                case 'session' :
                    $obj = Session::getInstance();
                    break;

                case 'message' :
                    $obj = new Message();
                    break;

                // Access FirePHP instance
                case 'fire' :

                    // Load FirePHP classfile only when class not exists
                  	if (!class_exists('FirePHP'))
                   		require_once (Cfg::get('Web', 'dir_tools') . '/FirePHPCore/FirePHP.class.php');

                   		$obj = \FirePHP::getInstance(true);
                   		break;

                default :
                    Throw new Error('Requested DI object does not exist.', 5006, $key);
                    break;
            }

            $this->di[$key] = $obj;
        }

        return $this->di[$key];
    }

    /**
     * Wrapper method for Security::checkAccess()
     * @see Web\Framework\Lib\Security::checkAccess()
     */
    protected function checkAccess($perms, $mode = 'smf', $force = false)
    {
        return Security::checkAccess($perms, $mode, $force);
    }

    /**
     * Wrapper method fo Debug::run()
     * @see Web\Framework\Lib\Debug::run()
     */
    protected function debug($var, $target = 'echo', $type = 'print')
    {
        Debug::run($var, $target, $type);
    }

    /**
     * Wrapper method for Log::add()
     * @see Web\Framework\Lib\Debug::run()
     */
    protected function log($msg, $app = 'Global', $function = 'Info', $check_setting = '', $trace = false)
    {
        Log::add($msg, $app, $function, $check_setting, $trace);
    }

    /**
     * Returns an function/method trace
     * @return string
     */
    protected function trace($ignore=3, $target = 'return')
    {
        return Debug::traceCalls($ignore, $target);
    }
}
?>

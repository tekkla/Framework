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

                case 'fire' :
                    $obj = \FirePHP::getInstance(true);
                    break;

                case 'message' :
                    $obj = new Message();
                    break;

                default :
                    Throw new Error('No DI for "' . $key . '" possible.');
                    break;
            }

            $this->di[$key] = $obj;
        }

        return $this->di[$key];
    }

    public function __isset($key)
    {
        return isset($this->di[$key]);
    }

    protected function checkAccess($perms, $mode = 'smf', $force = false)
    {
        return Security::checkAccess($perms, $mode, $force);
    }

    protected function debug($var, $target = 'echo', $type = 'print')
    {
        Debug::run($var, $target, $type);
    }

    protected function log($msg, $app = 'Global', $function = 'Info', $check_setting = '', $trace = false)
    {
        Log::add($msg, $app, $function, $check_setting, $trace);
    }
}
?>
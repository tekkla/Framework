<?php
namespace Web\Framework\Lib;

use Web\Framework\Lib\Abstracts\ClassAbstract;

if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Class with debugging functions
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class Debug extends ClassAbstract
{
    /**
     * The content to inspect
     * @var mixed
     */
    private $data;

    /**
     * How to inspect the var
     * @var string
     */
    private $mode = 'plain';

    /**
     * How to return the inspection information
     * @var unknown
     */
    private $target = 'console';

    /**
     * Sets the var by reference
     * @param mixed $var
     * @return \Web\Framework\Lib\Debug
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Sets the mode how to inspect the var.
     * Select from 'print' (print_r) or 'dump' (var_dump).
     * @param string $mode
     * @throws NoValidParameterError
     * @return \Web\Framework\Lib\Debug
     */
    public function setMode($mode = 'plain')
    {
        $modes = array(
            'print',
            'dump',
            'plain'
        );

        if (!in_array($mode, $modes))
            Throw new Error('Wrong mode set.', 1000, array(
                $mode,
                $modes
            ));

        $this->mode = $mode;
        return $this;
    }

    /**
     * Sets the target to what the result of var inspection will be send.
     * Select from 'return' or 'echo' or the third option called 'console'
     * which only available on ajax requests.
     * On non ajax requests all 'console' targets will be 'returns'
     * @param string $target
     * @throws NoValidParameterError
     * @return \Web\Framework\Lib\Debug
     */
    public function setTarget($target = 'console')
    {
        $targets = array(
            'return',
            'echo',
            'console'
        );

        if (!in_array($target, $targets))
            Throw new Error('Wrong target set.', 1000, array(
                $target,
                $targets
            ));

        $this->target = $target;

        return $this;
    }

    /**
     * Returns new debug object
     * @return \Web\Framework\Lib\Debug
     */
    public static function factory()
    {
        return new self();
    }

    /**
     * Sends data to FirePHP console
     */
    public static function toConsole($data)
    {
        self::factory()->run(array(
            'data' => $data,
        ));
    }

    /**
     * Var dumps the given var to the given target
     */
    public static function dumpVar($var, $target = '')
    {
        return self::factory()->run(array(
            'data' => $var,
            'target' => $target,
            'mode' => 'dump'
        ));
    }

    /**
     * Light version of debug_backtrace() which only creates and returns a trace of function and method calls.
     * @param number $ignore Numeber of levels to ignore
     * @return string
     */
    public static function traceCalls($ignore = 2, $target = '')
    {
        $trace = '';

        $dt = debug_backtrace();

        foreach ( $dt as $k => $v )
        {
            if ($k < $ignore)
                continue;

            array_walk($v['args'], function (&$item, $key)
            {
                $item = var_export($item, true);
            });

            $trace .= '#' . ($k - $ignore) . ' ' . $v['file'] . '(' . $v['line'] . '): ' . (isset($v['class']) ? $v['class'] . '->' : '') . $v['function'] . "\n";
        }

        return self::factory()->run(array(
            'data' => $trace,
            'target' => $target
        ));
    }

    /**
     * Var dumps the given var to the given target
     * @return string
     */
    public static function printVar($var, $target = '')
    {
        return self::factory()->run(array(
            'data' => $var,
            'target' => $target,
            'mode' => 'print'
        ));
    }

    /**
     * Debugs given data with various output
     * @return void string
     */
    public function run($data = array())
    {
        // Small debug definition parser
        if ($data)
        {
            $properties = array(
                'data',
                'mode',
                'target'
            );

            foreach ( $data as $prop => $val )
                if ($val && property_exists($this, $prop))
                    $this->{$prop} = $val;
        }

        // If var is not set explicit, the calling object will
        // be used for debug output.
        if (!isset($this->data))
            Throw new Error('Data to debug not set.', 1001);

        // Which display mode is requested?
        switch ($this->mode)
        {
            case 'print' :
                $dt = debug_backtrace();
                $output = $this->target == 'echo' ? '<div class="panel panel-info panel-body"><p>Called by: ' . $dt[0]['file'] . ' (' . $dt[0]['line'] . ')</p><pre>' . htmlspecialchars(print_r($this->data, true), ENT_QUOTES) . '</pre></div>' : $this->data;
                break;

            case 'dump' :
                ob_start();
                var_dump($this->data);
                $output = ob_get_clean();
                break;

            default :
                $output = $this->data;
                break;
        }

        // Target 'console' is used for ajax requests and
        // returns the debug content to the browser console
        if ($this->target == 'console')
        {
            // Create the ajax console.log ajax
            $this->fire->log($output);
            return;
        }

        // Echoing debug content and end this
        elseif ($this->target == 'echo')
        {
            echo '<h2>Debug</h2>' . $output;
            return;
        }

        // Falling through here means to return the output
        else
        {
            return $output;
        }
    }
}
?>

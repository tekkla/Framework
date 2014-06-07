<?php

namespace Web\Framework\Lib;

use Web\Framework\Lib\Abstracts\ClassAbstract;
use Web\Framework\Lib\Errors\NoValidParameterError;
use Web\Framework\Lib\Errors\NeededPropertyNotSetError;

// Check for direct file access
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
     * The var to inspect
     * @var mixed
     */
    private $var;

    /**
     * How to inspect the var
     * @var string
     */
    private $mode = 'print';

    /**
     * How to return the inspection information
     * @var unknown
     */
    private $target = 'return';

    /**
     * Sets the var by reference
     * @param mixed $var
     * @return \Web\Framework\Lib\Debug
     */
    public function setVar(&$var)
    {
        $this->var = $var;
        return $this;
    }

    /**
     * Sets the mode how to inspect the var.
     * Select from 'print' (print_r) or 'dump' (var_dump).
     * @param string $mode
     * @throws NoValidParameterError
     * @return \Web\Framework\Lib\Debug
     */
    public function setMode($mode = 'print')
    {
        $modes = array(
            'print',
            'dump'
        );

        if (!in_array($mode, $modes))
            throw new NoValidParameterError($mode, $modes);

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
    public function setTarget($target = 'return')
    {
        $targets = array(
            'return',
            'echo',
            'console'
        );

        if (!in_array($target, $targets))
            throw new NoValidParameterError($target, $targets);

        if ($target=='console' && !$this->request->isAjax())
            $target = 'return';

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
     * Var dumps the given var to the given target
     * @return string
     */
    public static function dumpVar(&$var, $target='return')
    {
        return self::factory()->setVar($var)->setMode('dump')->setTarget($target)->run();
    }

    /**
     * Var dumps the given var to the given target
     * @return string
     */
    public static function printVar(&$var, $target='return')
    {
        return self::factory()->setVar($var)->setMode('print')->setTarget($target)->run();
    }

    /**
     * Debugs a variable or an object with various output
     * @return void string
     */
    public function run()
    {
        // If var is not set explicit, the calling object will
        // be used for debug output.
        if (!isset($this->var))
            Throw new NeededPropertyNotSetError('var');

        switch ($this->mode)
        {
            case 'print' :
                $dt = debug_backtrace();
                $output = $this->target == 'echo' ? '<div class="panel panel-info panel-body"><p>Called by: ' . $dt[0]['file'] . ' (' . $dt[0]['line'] . ')</p><pre>' . htmlspecialchars(print_r($this->var, true), ENT_QUOTES) . '</pre></div>' : $this->var;
                break;

            case 'dump' :
                ob_start();
                var_dump($this->var);
                $output = ob_get_clean();
                break;
        }

        // Target 'console' is used for ajax requests and
        // returns the debug content to the browser console
        if ($this->target == 'console' && $this->request->isAjax())
        {
            // Create the ajax console.log ajax
            Ajax::factory()->log($output);
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

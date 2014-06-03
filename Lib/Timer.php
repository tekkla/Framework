<?php
namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Timer class for time measurement
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
final class Timer
{

    private $start;

    private $end;

    public function start()
    {
        $this->start = microtime(true);
    }

    public function stop()
    {
        $this->end = microtime(true);
        return $this->GetDiff();
    }

    private function getStart()
    {
        if (isset($this->start))
            return $this->start;
        else
            return false;
    }

    private function getEnd()
    {
        if (isset($this->end))
            return $this->end;
        else
            return false;
    }

    public function getDiff()
    {
        return $this->GetEnd() - $this->GetStart();
    }

    public function reset()
    {
        $this->start = microtime(true);
    }
}
?>

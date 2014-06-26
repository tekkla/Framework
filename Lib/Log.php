<?php

namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Logger class
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
final class Log
{
    /**
     * Log message
     * @var string
     */
    private $message = '';

    /**
     * Log type
     * @var string
     */
    private $type = '';

    /**
     * Memory used
     * @var int
     */
    private $memory = 0;

    /**
     * Timestamp
     * @var int
     */
    private $time = 0;

    /**
     * Trace flag for appending traces to log
     * @var boolean
     */
    private $trace = false;

    /**
     * Returns log type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns log message
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns memory value
     * @return int
     */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * Returns timestamp
     * @return number
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Sets log message
     * @param string $message
     * @return \Web\Framework\Lib\Log
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Sets timestamp
     * @param int $time
     * @return \Web\Framework\Lib\Log
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    public function setMemory($memory)
    {
        $this->memory = $memory;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Writes a message to the logfile
     * @param string $msg
     */
    public static function add($msg, $app = 'Global', $function = 'Info', $check_setting = '', $trace = false)
    {
        // Do not log when settingto check is wsitched off
        if (!empty($check_setting) && !Cfg::get('Web', $check_setting))
            return;

            // Logging only when log is activated
        if (!Cfg::get('Web', 'log') || !User::isAdmin())
            return;

            // Debug the message if it is not of type string
        if (is_object($msg))
            $msg = Debug::dumpVar($msg);

            // Start new log entry
        $log = new Log();

        // SSI tag if SSI and not in log
        if (SMF == 'SSI' && strpos($function, 'SSI') === false)
            $function .= ' (SSI)';

            // Trace append requested?
        if ($trace == true)
            $msg .= '<pre>' . print_r(Debug::traceCalls(), true) . '<pre>';

            // Putting all together to the log
        $log->setType($app . '::' . $function);
        $log->setMessage($msg);
        $log->setTime(microtime(true));
        $log->setMemory(FileIO::convFilesize(memory_get_usage()));
        $log->saveLog();
    }

    /**
     * Writes debug backtrace with an individual depth to the log
     * @param number $depth
     */
    public static function trace($depth = 10)
    {
        // Logging only when log is activated
        if (!Cfg::get('Web', 'log') || !User::isAdmin())
            return;

        $log = new Log();

        $dt = debug_backtrace();

        $logs = array();

        for($i = 0; $i < $depth; $i++)
        {
            $key = $i + 1;

            $file = isset($dt[$key]['file']) ? $dt[$key]['file'] . ' => ' : '';
            $line = isset($dt[$key]['line']) ? '[' . $dt[$key]['line'] . ']' : '';

            if ($key == 1)
                $logs[] = '<strong>' . $file . $dt[$key]['function'] . '() ' . $line . '</strong>';
            else
                $logs[] = $file . $dt[$key]['function'] . '() ' . $line . ']';
        }

        // Putting all together to the log
        $log->setMessage(implode('<br>', $logs));
        $log->setType('Trace');
        $log->setTime(microtime(true));
        $log->setMemory(FileIO::convFilesize(memory_get_usage()));
        $log->saveLog();
    }

    /**
     * Adds an error message to the log
     * @param unknown $msg
     */
    public static function error($msg)
    {
        self::Add($msg, 'ERROR');
    }

    /**
     * Adds a notice to the log
     * @param unknown $msg
     */
    public static function notice($msg)
    {
        self::Add($msg, 'NOTICE');
    }

    /**
     * Handles how the log entry is stored.
     * By default the log will be sent to the end of the page output.
     * If set to ON in WebExt config, the output will be send to FirePHP
     * extension of your Brwoser.
     */
    public function saveLog()
    {
        // Write log to file
        if (Cfg::get('Web', 'log_handler') == 'file')
        {
            // @todo: seriously?
        }
        // For ajax request logs and when FirePHP is set as log handler, we use FirePHP for log output!
        elseif (Cfg::get('Web', 'log_handler') == 'fire' || $this->request->isAjax())
        {
            \FB::log($this->message, $this->type);
        }
        // All else goes to session log
        else
        {
            // Still her? Output to session so the output can go to page
            if (empty($_SESSION['web']['logs']))
                $_SESSION['web']['log'] = array();

            $_SESSION['web']['logs'][] = $this;
        }
    }

    /**
     * Returns a formatted html output of created log entries.
     * @return boolean string
     */
    public static function getOutput()
    {
        // No admin? no output wanted? return false!
        if (!User::isAdmin() || !Cfg::get('Web', 'show_log_output'))
            return false;

            // Simple counter
        $log_counter = 0;

        $html = '
		<hr>
		<div class="container">
            <h3>WebExt Logs</h3>
            <div id="web_log" class="clearfix">
                <table class="table table-striped table-bordered table-condensed small">
				    <caption>
				        <thead>
					       <tr>
						      <th width="40">#</th>
						      <th>Info</th>
						      <th>Text</th>
						      <th width="100">Mem</th>
					       </tr>
				        </thead>
				        <tbody>';

             if (empty($_SESSION['web']['logs']))
                    $html .= '
                        <tr>
						  <td colspan="4" class="text-center"><strong>No logs to show.</strong></td>
                        </tr>';

             else
             {
                 /* @var $log Log */
                 foreach ( $_SESSION['web']['logs'] as $log )
                 {
                     $html .= '
                         <tr class="' . ($log_counter % 2 == 0 ? 'odd' : 'even') . '">
                             <td>' . $log_counter . '</td>
                             <td>' . $log->getType() . '</td>
                             <td>' . $log->getMessage() . '</td>
                             <td>' . $log->getMemory() . '</td>
                         </tr>';

                     $log_counter++;
                 }
             }

                    $html .= '
                    </tbody>
                </table>
            </div>
        </div>';

        self::resetLogs();

        return $html;
    }

    public static function resetLogs()
    {
        $_SESSION['web']['logs'] = array();
    }
}
?>

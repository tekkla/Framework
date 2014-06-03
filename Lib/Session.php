<?php
namespace Web\Framework\Lib;

use Web\Framework\Lib\Abstracts\SingletonAbstract;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Basic class for session handling
 * For now it's only useful for init the web tree in session
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class Session extends SingletonAbstract
{
    protected function __construct()
    {
        $this->init();
    }

    public function init()
    {
        if (!isset($_SESSION['web']))
            $_SESSION['web'] = array();
    }
}
?>

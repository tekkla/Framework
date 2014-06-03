<?php
namespace Web\Framework\Lib\Interfaces;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Html interface
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Interfaces
 * @license BSD
 * @copyright 2014 by author
 */
interface HtmlInterface
{
    function build($wrapper=null);
}
?>

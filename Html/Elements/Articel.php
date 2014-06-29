<?php
namespace Web\Framework\Html\Elements;

use Web\Framework\Lib\Abstracts\HtmlAbstract;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Articel html Element
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Elements
 * @license BSD
 * @copyright 2014 by author
 */
class Articel extends HtmlAbstract
{
    protected $element = 'articel';
}
?>

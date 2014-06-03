<?php
namespace Web\Framework\Html\Elements;

use Web\Framework\Lib\Abstracts\HtmlAbstract;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a span html object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
class Span extends HtmlAbstract
{
    /**
     * Factory method
     * @return \Web\Framework\Html\Elements\Span
     */
    public static function factory()
    {
        return new Span();
    }

    /**
     * Constructor
     */
    function __construct()
    {
        $this->setElement('span');
    }
}
?>

<?php
namespace Web\Framework\Html\Elements;

use Web\Framework\Lib\Abstracts\HtmlAbstract;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a paragraph html object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
class Paragraph extends HtmlAbstract
{
    /**
     * Factory pattern
     * @return \Web\Framework\Html\Elements\Paragraph
     */
    public static function factory()
    {
        return new Paragraph();
    }

    /**
     * Constructor
     */
    function __construct()
    {
        $this->setElement('p');
    }
}
?>

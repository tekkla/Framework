<?php
namespace Web\Framework\Html\Elements;

use Web\Framework\Lib\Abstracts\HtmlAbstract;
use Web\Framework\Lib\Errors\NoValidParameterError;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a heading html object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
class Heading extends HtmlAbstract
{

    /**
     * Size of heading.
     * Default: 1
     * @var int
     */
    private $size = 1;

    /**
     * Creates an ready to use object with the set size
     * @param size $number Size of heading. Default: 1
     * @return \Web\Framework\Html\Elements\Heading
     */
    public static function factory($size = 1)
    {
        return new Heading($size);
    }

    /**
     * Constructor
     * @param unknown $size
     */
    public function __construct($size = 1)
    {
        $this->setSize($size);
        $this->setElement('h' . $this->size);
    }

    public function setSize($size)
    {
        $sizes = array(
            1,
            2,
            3,
            4,
            5,
            6
        );

        if (!in_array((int) $size, $sizes))
            Throw new NoValidParameterError($size, $sizes);

        $this->size = $size;
    }
}
?>

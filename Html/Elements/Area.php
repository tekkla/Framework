<?php
namespace Web\Framework\Html\Elements;

use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Abbr Html Object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
class Abbr extends Link
{
    protected $element = 'area';

    /**
     * Sets the coordinates of the area
     * @param string $cords
     * @return \Web\Framework\Html\Elements\Abbr
     */
    public function setCoords($cords)
    {
        $this->attribute['coords'] = $cords;
        return $this;
    }

    /**
     * Sets the shape of the area
     * @param string $shape
     * @throws Error
     * @return \Web\Framework\Html\Elements\Abbr
     */
    public function setShape($shape)
    {
        $shapes = array(
            'default',
            'rect',
            'circle',
            'poly',
        );

        if (!in_array($shape, $shapes))
            Throw new Error('Set shape is not valid.', 1000, array($shape, $shapes));

        $this->attribute['shape'] = $shape;
        return $this;
    }
}
?>

<?php
namespace Web\Framework\Html\Elements;

use Web\Framework\Lib\Abstracts\HtmlAbstract;
use Web\Framework\Lib\Url;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a img html object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
class Img extends HtmlAbstract
{
    /**
     * Factory pattern
     * @param string|Url $src
     * @return \Web\Framework\Html\Elements\Img
     */
    public static function factory($src)
    {
        $obj = new Img();
        $obj->setSrc($src);
        return $obj;
    }

    /**
     * Constructor
     */
    function __construct()
    {
        $this->setElement('img');
    }

    /**
     * Set src attribute
     * @param string|Url $src Src value
     * @return \Web\Framework\Html\Elements\Img
     */
    public function setSrc($src)
    {
        if ($src instanceof Url)
            $src = $src->getUrl();

        $this->addAttribute('src', $src);
        return $this;
    }

    /**
     * Sets alt attribute
     * @param string $alt
     * @return \Web\Framework\Html\Elements\Img
     */
    public function setAlt($alt)
    {
        $this->addAttribute('alt', (string) $alt);
        return $this;
    }

    /**
     * Sets title attribute
     * @param string $title
     * @return \Web\Framework\Html\Elements\Img
     */
    public function setTitle($title)
    {
        $this->addAttribute('title', (string) $title);
        return $this;
    }

    /**
     * Set width attribute
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->addAttribute('width', (int) $width);
        return $this;
    }

    /**
     * Sets height attribute
     * @param int $height
     * @return \Web\Framework\Html\Elements\Img
     */
    public function setHeight($height)
    {
        $this->addAttribute('height', (int) $height);
        return $this;
    }

    /**
     * Sets ismap attribute.
     * @param string $flag
     * @return \Web\Framework\Html\Elements\Img
     */
    public function setIsMap()
    {
        $this->addAttribute('ismap');
        return $this;
    }

    /**
     * Sets the name of map to use
     * @param string $name
     * @return \Web\Framework\Html\Elements\Img
     */
    public function useMap($name)
    {
        $this->addAttribute('usemap', $name);
        return $this;
    }
}
?>

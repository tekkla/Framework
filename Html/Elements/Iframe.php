<?php
namespace Web\Framework\Html\Elements;

use Web\Framework\Lib\Abstracts\HtmlAbstract;
use Web\Framework\Lib\Url;
use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates an iframe html object.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Element
 * @license BSD
 * @copyright 2014 by author
 */
class Iframe extends HtmlAbstract
{
    private $sandbox = array();

    protected $element = 'iframe';

    /**
     * Factory pattern.
     * @param string|Url $src Url of iframe. Accepts url as string and an url object.
     * @return \Web\Framework\Html\Elements\Iframe
     */
    public static function factory($src)
    {
        $obj = new Iframe();
        $obj->setSrc($src);
        return $obj;
    }

    /**
     * Sets src attribute of iframe..
     * @param string|Url $src Url of iframe. Accepts url as string and an url object.
     * @return \Web\Framework\Html\Elements\Iframe
     */
    public function setSrc($src)
    {
        if ($src instanceof Url)
            $src = $src->getUrl();

        $this->attribute['src'] = $src;
        return $this;
    }

    /**
     * Sets the srcdoc attribute
     * @param string $srcdoc Html code to show
     * @return \Web\Framework\Html\Elements\Iframe
     */
    public function setSrcDoc($srcdoc)
    {
        $this->attribute['srcdoc'] = $srcdoc;
        return $this;
    }

    /**
     * Adds a sandbox mode
     * @param string $mode
     * @throws NoValidParameterError
     * @return \Web\Framework\Html\Elements\Iframe
     */
    public function addSandboxMode($mode)
    {
        $modes = array(
            '',
            'allow-forms',
            'allow-same-origin',
            'allow-scripts',
            'allow-top-navigation'
        );

        if (!in_array($mode, $modes))
        	Throw new Error('Wrong sanbox mode.', 1000, array($mode, $modes));

        if (!in_array($mode, $this->sandbox))
            $this->sandbox[] = $mode;

        return $this;
    }

    /**
     * Sets the width attribute
     * @param int $width
     * @return \Web\Framework\Html\Elements\Iframe
     */
    public function setWidth($width)
    {
        $this->attribute['width'] = (int) $width;
        return $this;
    }

    /**
     * Sets the height attribute
     * @param int $height
     * @return \Web\Framework\Html\Elements\Iframe
     */
    public function setHeight($height)
    {
        $this->attribute['height'] = (int) $height;
        return $this;
    }

    /**
     * Sets the iframe to be seamless or not
     * @param string $state
     */
    public function setSeamless($state = true)
    {
        if ($state === true)
            $this->attribute['seamless'] = false;
        else
            $this->removeAttribute('seamless');

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Web\Framework\Lib\Abstracts\HtmlAbstract::build()
     */
    public function build($wrapper = null)
    {
        if ($this->sandbox)
            $this->attribute['sandbox'] = implode(' ', $this->sandbox);

        return parent::build($wrapper);
    }
}
?>

<?php
namespace Web\Framework\Html\Elements;

use Web\Framework\Lib\Abstracts\HtmlAbstract;
use Web\Framework\Lib\Url;
use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * A Html Element
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
class A extends HtmlAbstract
{
    protected $element = 'a';

    /**
     * Factory method
     * @param string|Url $url
     * @return \Web\Framework\Html\Elements\Link
     */
    public static function factory($url = null)
    {
        $obj = new Link();

        if (isset($url))
            $obj->setHref($url);

        return $obj;
    }

    /**
     * Sets an alternate text for the link.
     * Required if the href attribute is present.
     * @param string $alt
     * @return \Web\Framework\Html\Elements\Link
     */
    public function setAlt($alt)
    {
        $this->attribute['alt'] = $alt;
        return $this;
    }

    /**
     * Sets the href attribute.
     * @param string $href
     */
    public function setHref($url)
    {
        if ($url instanceof Url)
            $url->getUrl();

        $this->attribute['href'] = $url;
        return $this;
    }

    /**
     * Sets the language of the target URL
     * @param string $lang_code
     * @return \Web\Framework\Html\Elements\Link
     */
    public function setHrefLang($lang_code)
    {
        $this->attribute['hreflang'] = $lang_code;
        return $this;
    }

    /**
     * Sets the target attribute
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->attribute['target'] = $target;
        return $this;
    }

    /**
     * Sets he relationship between the current document and the target URL
     * @param string $rel
     */
    public function setRel($rel)
    {
        $rels = array(
            'alternate',
            'author',
            'bookmark',
            'help',
            'license',
            'next',
            'nofollow',
            'noreferrer',
            'prefetch',
            'prev',
            'search',
            'tag',
        );

        if (!in_array($rel, $rels))
            throw new Error('Not valid rel attribute', 1000, array($rel, $rels));

        $this->attribute['rel'] = $rel;
        return $this;
    }

    /**
     * Sets that the target will be downloaded when a user clicks on the link
     * @return \Web\Framework\Html\Elements\Link
     */
    public function isDownload()
    {
        $this->attribute['download'] = false;
        return $this;
    }

    /**
     * Sets what media/device the target URL is optimized for
     * @param string $media
     * @return \Web\Framework\Html\Elements\Link
     */
    public function setMedia($media)
    {
        $this->attribute['media'] = $media;
        return $this;
    }

    /**
     * Sets the MIME type of the target URL
     * @param string $media
     * @return \Web\Framework\Html\Elements\Link
     */
    public function setType($type)
    {
        $this->attribute['type'] = $type;
    	return $this;
    }

    /**
     * Build method with href and set alt check
     * @see \Web\Framework\Lib\Abstracts\HtmlAbstract::build()
     */
    public function build()
    {
        if (isset($this->attribute['href']) && (!isset($this->attribute['alt'])))
            $this->attribute['alt'] = '';

        return parent::build();
    }

}
?>

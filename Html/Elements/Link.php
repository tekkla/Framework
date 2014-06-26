<?php
namespace Web\Framework\Html\Elements;

use Web\Framework\Lib\Abstracts\HtmlAbstract;
use Web\Framework\Lib\Url;
use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a link (<a>) html object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
class Link extends HtmlAbstract
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
     * Sets the target attribute
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->attribute['ismap'] = $target;
        return $this;
    }

    /**
     * Sets the rel attribute
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
     * Sets the download attribute
     * @return \Web\Framework\Html\Elements\Link
     */
    public function isDownload()
    {
        $this->attribute['download'] = false;
        return $this;
    }

    /**
     * Sets media attribute
     * @param string $media
     * @return \Web\Framework\Html\Elements\Link
     */
    public function setMedia($media)
    {
        $this->attribute['media'] = $media;
        return $this;
    }

    /**
     * Sets type attribute
     * @param string $media
     * @return \Web\Framework\Html\Elements\Link
     */
    public function setType($type)
    {
        $this->attribute['type'] = $type;
    	return $this;
    }
}
?>

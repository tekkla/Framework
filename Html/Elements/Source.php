<?php
namespace Web\Framework\Html\Elements;

use Web\Framework\Lib\Abstracts\HtmlAbstract;
use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Source Html Element
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Elements
 * @license BSD
 * @copyright 2014 by author
 */
class Source extends HtmlAbstract
{
	protected $element = 'source';

	/**
	 * Sets the type of media resource
	 * @param string $media 
	 * @return \Web\Framework\Html\Elements\Source
	 */
	public function setMedia($media)
	{
		$this->attribute['media'] = $media;
		return $this;
	}

	/**
	 * Sets the URL of the media file
	 * @param string $source 
	 * @return \Web\Framework\Html\Elements\Source
	 */
	public function setSource($source)
	{
		$this->attribute['source'] = $source;
		return $this;
	}

	/**
	 * Sets the MIME type of the media resource
	 * @param string $type 
	 * @return \Web\Framework\Html\Elements\Source
	 */
	public function setType($type)
	{
		$this->attribute['type'] = $type;
		return $this;
	}

	public function build()
	{
		if (!isset($this->attribute['source']))
			Throw new Error('No mediasource set.', 1000);
		
		if (!isset($this->attribute['type']))
			Throw new Error('No media type set.', 1000);
		
		return parent::build();
	}
}
?>

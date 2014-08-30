<?php
namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Url shortener class
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class ShortenURL
{

	public function factory($content, $type = 'text')
	{
		$obj = new ShortenURL();

		switch ($type)
		{
			case 'text' :
				return $obj->shortenUrls($content);

			case 'url' :
				return $obj->getTinyUrl($content);
		}
	}

	/**
	 * Transforms given url into tiny url
	 * @param string $url
	 * @return string
	 */
	private function getTinyUrl($url)
	{
		$ch = curl_init();

		$timeout = 5;

		curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url=' . $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

		$url = curl_exec($ch);
		curl_close($ch);

		return $url;
	}

	/**
	 * Transforms all urls in content into bbcode urls [url=THEURL]TitleOfPageBehindUrl[/url]
	 * @todo This method currently does not ignore urls of img, css or already BBCed urls.
	 *	   It's important to take care of this.
	 * @param string $content
	 * @return string
	 */
	private function shortenUrls($content)
	{
		$url_pattern = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';

		preg_match_all($url_pattern, $content, $urls);

		if (Cfg::exists('web', 'url_shortener_exclude'))
			$exclude_pattern = '#' . implode('|', Cfg::Get('web', 'url_shortener_exclude')) . '#';

		foreach ( $urls[0] as $url )
		{
			// modify not exluded urls
			if (isset($exclude_pattern) && preg_match($exclude_pattern, $url))
				continue;

			if (Cfg::Get('web', 'url_shortener_use') == 1)
			{
				// get pagetitle
				$doc = new \DOMDocument();
				@$doc->loadHTMLFile($url);

				$xpath = new \DOMXPath($doc);
				$inner = $xpath->query('//title')->item(0)->nodeValue . "";
			}

			if (!$inner)
			{
				$url_parts = parse_url($url);
				$inner = $url_parts['host'];
			}

			// shorten url
			if (Cfg::Get('web', 'url_shortener_tiny') == 1)
				$url = $this->getTinyUrl($url);

			$content = str_replace($url, '[url=' . $url . ']' . $inner . '[/url]', $content);
		}

		return $content;
	}
}
?>

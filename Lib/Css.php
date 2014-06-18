<?php
namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Class for managing and creating of css objects
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
final class Css
{
    /**
     * Storage of css objects
     * @var unknown
     */
    private static $css = array();

    /**
     * Type of css object
     * @var string
     */
    private $type;

    /**
     * Css object content
     * @var string
     */
    private $content;

    /**
     * Adds a css object to the output queue
     * @param Css $css
     */
    public static function add(Css $css)
    {
        self::$css[] = $css;
    }

    /**
     * Compiles the objects in the output queue and adds them to SMFs $context.
     * Optional: If set in framework config, multiple css files will be
     * combined into one minified css file
     */
    public static function compile()
    {
        global $context;

        $files = array();
        $inline = array();

        /* @var $css Css */
        foreach ( self::$css as $css )
        {
            switch ($css->getType())
            {
                case 'file' :
                    loadCSSFile($css->getCss());
                    break;

                case 'inline' :
                    $inline[] = $css->getCss();
                    break;
            }
        }

        // create script for minifier
        if (Cfg::get('Web', 'css_minify'))
        {
            foreach ( $context['css_files'] as $name => $file )
            {
            	if (strpos($file['filename'], BOARDURL) !== false)
                {
                    $board_parts = parse_url(BOARDURL);
                    $url_parts = parse_url($file['filename']);

                    // Do not try to minify ressorces from external host
                    if ($board_parts['host'] != $url_parts['host'])
                        continue;

                    // Store filename in minify list
                    $files[] = '/' . $url_parts['path'];

                    // Remove this file from $context
                    unset($context['css_files'][$name]);
                }
            }

            if ($files)
            {
                cache_put_data('web_min_css', $files);

                // cache_put_data('web_css', $files);
                loadCSSFile(Cfg::get('Web', 'url_tools') . '/min/g=css', null, 'web-css-minified');
            }
        }

        // Are there inline css?
        if ($inline)
            $context['css_header'] .= implode(PHP_EOL, $inline);
    }

    /**
     * Factory method which returns a new css object
     * @return \Web\Framework\Lib\Css
     */
    public static function factory()
    {
        return new self();
    }

    /**
     * Sets objects type.
     * Type can be "file" or "inline".
     * @param string $type
     * @throws Error
     * @return \Web\Framework\Lib\Css
     */
    public function setType($type)
    {
        $types = array(
            'file',
            'inline'
        );

        if (!in_array($type, $types))
            Throw new Error('Css type must be "inline" or "file".');

        $this->type = $type;
        return $this;
    }

    /**
     * Sets objects css content
     * @param string $value
     * @return \Web\Framework\Lib\Css
     */
    public function setCss($value)
    {
        $this->content = $value;
        return $this;
    }

    /**
     * Get objects type (file or inline)
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get objects css content
     * @return string
     */
    public function getCss()
    {
        return $this->content;
    }

    /**
     * Adds this object tot the output queue
     * @return \Web\Framework\Lib\Css
     */
    public function toQueue()
    {
        self::add($this);
        return $this;
    }

    /**
     * Adss link css object to the outut queue
     * @param string $url
     */
    public static function useLink($url)
    {
        self::add(self::getLink($url));
    }

    /**
     * Creates and returns a link css object.
     * @param string $url
     * @return Css
     */
    public static function getLink($url)
    {
        return self::factory()->setType('file')->setCss($url);
    }

    /**
     * Adds an inline css object to the output queue
     * @param string $styles
     */
    public static function useInline($styles)
    {
        self::add(self::getInline($styles));
    }

    /**
     * Creates and returns an inline css object
     * @param string $styles
     * @return \Web\Framework\Lib\Css
     */
    public static function getInline($styles)
    {
        return self::factory()->setType('inline')->setCss($styles);
    }

    /**
     * Add bootstrab css object to the output queue
     * @param string $version
     * @param string $path
     */
    public static function useBootstrap($version, $path)
    {
        self::add(self::getBootstrap($version, $path));
    }

    /**
     * Creates and returns a bootstrap css object
     * @param string $version
     * @param string $path
     */
    public static function getBootstrap($version, $path)
    {
        return self::factory()->setType('file')->setCss($path . '/bootstrap-' . $version . '.min.css');
    }

    /**
     * Adds a fonteawesome css object to the output queue
     * @param string $version
     * @param string $path
     */
    public static function useFontAwesome($version, $path)
    {
        self::add(self::getFontAwesome($version, $path));
    }

    /**
     * Creates and returns a fontawesome css object
     * @param string $version
     * @param string $path
     * @return Css
     */
    public static function getFontAwesome($version, $path)
    {
        return self::factory()->setType('file')->setCss($path . '/font-awesome-' . $version . '.min.css');
    }
}
?>

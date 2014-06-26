<?php
namespace Web\Framework\Html\Controls;

use Web\Framework\Lib\Error;
use Web\Framework\Lib\Url;
use Web\Framework\Html\Elements\Icon;
use Web\Framework\Html\Elements\Link;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates an UiButton control
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Helper
 * @license BSD
 * @copyright 2014 by author
 */
final class UiButton extends Link
{
    /**
     * Static instance counter
     * @var int
     */
    private static $instance_count = 0;

    /**
     * Buttontype
     * @var string
     */
    private $type = 'text';

    /**
     *
     * @var bool
     */
    private $modal = false;

    /**
     * Accessmode
     * @var string
     */
    private $mode = 'full';

    /**
     * Link title
     * @var string
     */
    private $title = '';

    /**
     * img object
     * @var Icon
     */
    public $icon = false;

    /**
     * button text
     * @var string
     */
    private $text = '';

    /**
     * link object
     * @var Link
     */
    public $link = false;

    /**
     * Url object library
     * @var Url
     */
    public $url = false;

    /**
     * Factory method
     * @return UiButton
     */
    public static function factory($mode = null, $type = null, $app = null, $ctrl = null, $func = null, $params = null)
    {
        $obj = new UiButton();

        if (isset($mode))
            $obj->setMode($mode);

        if (isset($type))
            $obj->setType($type);

        if (isset($app))
            $obj->url->setApp($app);

        if (isset($ctrl))
            $obj->url->setCtrl($ctrl);

        if (isset($func))
            $obj->url->setFunc($func);

        if (isset($params))
            $obj->url->setParameter($params);

        return $obj;
    }

    /**
     * Creates a route based button
     * @param string $route Route to compile
     * @param array $params Parameter for route compiling
     * @param string $mode
     * @return \Web\Framework\Html\Controls\UiButton
     */
    public static function routeButton($route, $params = array(), $mode = 'full')
    {
        $obj = new UiButton();
        $obj->setMode($mode);
        $obj->setType('button');
        $obj->setRoute($route, $params);

        return $obj;
    }

    /**
     * Creates a route based link
     * @param string $route Route to compile
     * @param array $params Parameter for route compiling
     * @param string $mode
     * @return \Web\Framework\Html\Controls\UiButton
     */
    public static function routeLink($route, $params = array(), $mode = 'full')
    {
        $obj = new UiButton();
        $obj->setMode($mode);
        $obj->setType('link');
        $obj->setRoute($route, $params);

        return $obj;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        // Update instance counter for uniqe auto ids
        self::$instance_count++;

        // Give button an unique id
        $this->setId('web_uibutton_' . uniqid());

        // Injet url object
        $this->url = Url::factory();
    }

    /**
     * Sets buttonmode to: ajax
     */
    public function useAjax()
    {
        $this->mode = 'ajax';
        return $this;
    }

    /**
     * Sets buttonmode to: full
     */
    public function useFull()
    {
        $this->mode = 'full';
        return $this;
    }

    /**
     * Sets the buttonmode
     * @param string $mode
     */
    public function setMode($mode)
    {
        $modelist = array(
            'ajax',
            'full'
        );

        if (!in_array($mode, $modelist))
        	Throw new Error('Wrong mode for UiButton.', 1000, array($mode, $modelist));

        $this->mode = $mode;
        return $this;
    }

    /**
     * Returns the set mode
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * S(non-PHPdoc)
     * @see \Web\Framework\Html\Elements\Link::setType()
     */
    public function setType($type)
    {
        $typelist = array(
            'link',
            'icon',
            'button',
            'imgbutton'
        );

        if (!in_array($type, $typelist))
        	Throw new Error('Wrong type for UiButton.', 1000, array($type, $typelist));

        $this->type = $type;
        return $this;
    }

    /**
     * Set an icon from fontawesome icon.
     * Use only the name without the leading "fa-"
     * @param string $icon
     * @param string $inner
     * @return \web\framework\Html\controls\UiButton
     */
    public function setIcon($icon, $inner = null)
    {
        $this->icon = Icon::factory($icon);
        return $this;
    }

    /**
     * Set a linktext.
     * If a linktext and an image is set, the linktext will be ignored!!!
     * @param $val string Inner HTML of link
     * @param $app string Optional name of app the text is from.
     * @return \web\framework\Html\controls\UiButton
     */
    function setText($val)
    {
        $this->text = $val;
        return $this;
    }

    /**
     * Set the links as post.
     * You need to set the formname paramtere, so the ajax script can fetch the
     * data of the form.
     *
     * @param $formname string
     */
    public function setForm($form_name)
    {
        $this->addData('web-form', $form_name);
        return $this;
    }

    /**
     * Add a confirmevent to the link.
     * IF confirm returns false, the link won't be executed
     * @param string $msg
     */
    public function setConfirm($msg)
    {
        $this->addData('web-confirm', $msg);
        return $this;
    }

    /**
     * Sets target of button to be displayed in modal window
     * @param string $modal Name of modal window frame
     * @return \Web\Framework\Html\Controls\UiButton
     */
    public function setModal($modal = '#web_modal')
    {
        $this->addData('web-modal', $modal);
        return $this;
    }

    /**
     * Sets named route and optionale params to the url object of button
     * @param string $route Name of registered route
     * @param string $params
     * @return \Web\Framework\Html\Controls\UiButton
     */
    public function setRoute($route, $params=array())
    {
        $this->url->setNamedRoute($route);

        if ($params)
            $this->url->setParameter($params, null, true);

        return $this;
    }

    /**
     * Adds one or more (assoc array) parameter to buttons url object by resetting already existing parameters.
     * @param mixed One param = key, val | list of params = array(key => val)
     * @return \Web\Framework\Html\Controls\UiButton
     */
    public function setParameter($arg1, $arg2=null)
    {
        $this->url->setParameter($arg1, $arg2, true);
        return $this;
    }

    /**
     * Adds one or more (assoc array) parameter to buttons url object
     * @param mixed One param = key, val | list of params = array(key => val)
     * @return \Web\Framework\Html\Controls\UiButton
     */
    public function addParameter($arg1, $arg2=null)
    {
    	$this->url->setParameter($arg1, $arg2, false);
    	return $this;
    }

    /**
     * Builds and returns button html code
     * @param string $wrapper
     * @throws Error
     * @return string
     */
    public function build($wrapper = null)
    {
        if ($this->mode == 'ajax')
            $this->addData('web-ajax', 'link');

        // -----------------------------------------
        // build link attributes
        // -----------------------------------------

        // href url
        $this->setHref($this->url->getUrl());

        // Set text and set icon means we have a button of type imagebutton
        if  ($this->text && $this->icon)
            $this->type = 'imgbutton';

        // icon/image
        if ($this->type == 'icon')
        {
            $this->addCss('web-icon');
            $this->icon->noStack();
            $this->setInner($this->icon->build());
        }

        // textbutton
        if ($this->type == 'button')
            $this->setInner('<span class="web-button-text">' . $this->text . '</span>');

            // simple link
        if ($this->type == 'link')
        {
            $this->addCss('web-link');
            $this->setInner('<span class="web-link-text">' . $this->text . '</span>');
        }

        // imgbutton
        if ($this->type == 'imgbutton')
        {
            $this->icon->noStack();
            $this->setInner($this->icon->build() . ' ' . $this->text);
        }

        // Do we need to set the default button css code for a non link?
        if ($this->type != 'link')
        {
            $this->addCss('btn');

            $check = array(
                'btn-primary',
                'btn-success',
                'btn-warning',
                'btn-info',
                'btn-default'
            );

            if ($this->checkCss($check) == false)
                $this->addCss('btn-default');
        }

        return parent::build(null);
    }
}
?>

<?php
namespace Web\Framework\Lib;

use Web\Framework\Html\Controls\ModalWindow;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Ajax commands which are managed by framework.js
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
final class Ajax extends Lib
{
    /**
     * Storage for ajax commands
     * @var \stdClass
     */
    private static $ajax;

    /**
     * Name of the app this ajax is related to
     * @var string
     */
    private $app;

    /**
     * Controllername which should be uses
     * @var string
     */
    private $ctrl;

    /**
     * Actiuonfunction to be called in controller
     * @var string
     */
    private $action;

    /**
     * Parameters to pass into the controlleraction
     * @var array
     */
    private $params;

    /**
     * The type of the current ajax.
     * @var string
     */
    private $type = 'html';

    /**
     * The documents DOM ID the ajax content should go in
     * @var string
     */
    private $target = '*';

    /**
     * The Content to return
     * @var string
     */
    private $content = '';

    /**
     * Validatein string
     * @var string
     */
    private $validate;

    /**
     * The mode how the content is to be injected into the DOM
     * @var string Select
     * @example from replace, prepend, append
     */
    private $mode = 'replace';

    /**
     * Attributename to fill in content
     * @var string
     */
    private $attribute;

    /**
     * Command variables to send with ajax
     * @var array
     */
    private $cmd_vars = array();

    /**
     * create instance store
     * @var unknown
     */
    private static $instance;

    /**
     * Factory method
     * @return \Web\Framework\Lib\Ajax
     */
    public static function factory()
    {
        return new self();
    }

    /**
     * Create msgbox in browser
     * @param $msg
     */
    public static function alert($msg)
    {
        self::factory()->setType('alert')->setContent($msg)->add();
    }

    /**
     * Start a controller run
     * @param $ctrl
     * @param $action
     * @param $target
     */
    public static function call($app_name, $controller, $action, $target = null, $params = null)
    {
        // Get the content from matching controller
        $content = App::create($app_name)->getController($controller)->run($action, $params);

        // Create a new Ajax object
        $ajax = self::factory();

        // Publish content to our Ajax
        $ajax->setContent($content);

        // Any output target set?
        if (isset($target))
            $ajax->setTarget($target);

            // Add this Ajax to the repsonse storage4
        $ajax->add();
    }

    /**
     * Create a HTML ajax
     * @param $target => DOM id
     * @param $content
     * @param $mode optional => the edit mode replace(default)|append|prepend|remove|after|before
     */
    public static function html($target, $content, $mode = 'replace')
    {
        self::factory()->setType('html')->setTarget($target)->setContent($content)->add();
    }

    /**
     * Send an error to the web_error div
     * @param unknown_type $error
     */
    public static function error($error)
    {
        self::factory()->setType('alert')->setTarget('#web-message')->setContent($error)->add();
    }

    /**
     * Change a DOM attribute
     * @param $target => DOM id
     * @param $attribute => attribute name
     * @param $content
     * @param $mode optional => the edit mode replace(default)|append|prepend|remove
     */
    public static function attrib($target, $attribute, $content, $mode = 'replace')
    {
        self::factory()->setType('attrib')->setTarget($target)->setAttribute($attribute)->setMode($mode)->setContent($content)->add();
    }

    /**
     * Change class attribute of dom element
     * @param $target => DOM id
     * @param $content
     * @param $mode optional => the edit mode replace(default)|append|prepend|remove
     */
    public static function css($target, $content, $mode = 'replace')
    {
        self::factory()->setType('css')->setTarget($target)->setMode($mode)->setContent($content)->add();
    }

    /**
     * Calls a page refresh by loading the provided url.
     * Calls location.href="url" in page.
     * @param string|Url $url Can be an url as string or an Url object on which the getUrl() method is called
     */
    public static function refresh($url)
    {
        if ($url instanceof Url)
            $url = $url->getUrl();

        self::factory()->setType('refresh')->setContent($url)->add();
    }

    /**
     * Change style attribute of dom element
     * @param $target => DOM id
     * @param $content
     * @param $mode optional => the edit mode replace(default)|append|prepend|remove
     */
    public static function style($target, $content, $mode = 'replace')
    {
        self::factory()->setType('style')->setTarget($target)->setMode($mode)->setContent($content)->add();
    }

    /**
     * Replaces content of a target.
     * @param string $target jQuery selector replace content of
     * @param string $content The data to be insert
     */
    public static function replaceHtml($target, $content)
    {
        self::factory()->setType('html')->setMode('replace')->setTarget($target)->setContent($content)->add();
    }

    /**
     * Inserts content after a target.
     * @param string $target jQuery selector to insert content after
     * @param string $content The data to be insert
     */
    public static function afterHtml($target, $content)
    {
        self::factory()->setType('html')->setMode('after')->setTarget($target)->setContent($content)->add();
    }

    /**
     * Inserts content before a target.
     * @param string $target jQuery selector to insert content before
     * @param string $content The data to be insert
     */
    public static function beforeHtml($target, $content)
    {
        self::factory()->setType('html')->setMode('before')->setTarget($target)->setContent($content)->add();
    }

    /**
     * Appends content to a target.
     * @param string $target jQuery selector to append content to
     * @param string $content The data to be appended
     */
    public static function appendHtml($target, $content)
    {
        self::factory()->setType('html')->setMode('append')->setTarget($target)->setContent($content)->add();
    }

    /**
     * Prepends content to a target.
     * @param string $target jQuery selector to prepend content to
     * @param string $content The data to be prepended
     */
    public static function prependHtml($target, $content)
    {
        self::factory()->setType('html')->setMode('prepend')->setTarget($target)->setContent($content)->add();
    }

    /**
     * Removes the html specified by $target parameter
     * @param string $target jQuery selector to be removed from DOM
     */
    public static function removeHtml($target)
    {
        self::factory()->setType('html')->setMode('remove')->setTarget($target)->add();
    }

    /**
     * Creates ajax response to load a js file.
     * @param string $file Complete url of file to load
     */
    public static function loadScript($file)
    {
        self::factory()->setType('load_script')->setContent($file)->add();
    }

    /**
     * Create console log output
     * @param string $msg
     */
    public static function log($msg)
    {
        self::factory()->setType('console')->setContent($msg)->add();
    }

    /**
     * Creates a print_r console output of provided $var
     * @param mixed $var
     */
    public static function dump($var)
    {
        self::factory()->setType('console')->setContent(print_r($var, true))->add();
    }

    /**
     * Adds additional vars to a command
     * @param string $name
     * @param string $value
     * @return \Web\Framework\Lib\Ajax
     */
    public function addCmdVars($name, $value)
    {
        $this->cmd_vars[$name] = $value;
        return $this;
    }

    /**
     * Set app name
     * @param $app
     */
    public function setApp($app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Set controller Name
     * @param $ctrl
     */
    public function setCtrl($ctrl)
    {
        $this->ctrl = $ctrl;
        return $this;
    }

    /**
     * Set function name
     * @param $action
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Set ajax type
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set DOM id of target
     * @param $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /**
     * Set content of ajax
     * @param $content
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Set name of attribute to alter
     * @param $attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * Builds ajax definition and adds it to the ajaxlist
     */
    public function add()
    {
        // Command vars counter
        static $counter = 0;

        // Some ajaxtype need a target to fill in the returned content.
        // This array defines those types to check set target property.
        $types_need_target = array(
            'html',
            'attrib',
            'style',
            'css'
        );

        // Build up definition of our ajax
        $definition = new \stdClass();

        // Create alert on missing target when type is in need-target list
        if (in_array($this->type, $types_need_target) && !isset($this->target))
        {
            self::alert('Your ajax response needs a target but no target is set. Aborting.');
            return;
        }

        // Create alert on content withou target
        if (isset($this->content) && !isset($this->target) && in_array($this->type, $types_need_target))
        {
            self::alert('Your ajax has content but no target to put it in. Aborting');
            return;
        }

        // Create alert on target without content
        if (!isset($definition->content) && isset($definition->target))
        {
            self::alert('Your set a target but there is no content for it. Aborting');
            return;
        }

        // Create modal content on type of modal
        if ($this->type == 'modal')
        {
            $modal = ModalWindow::factory();
            $modal->setContent($this->content);

            if (isset($this->cmd_vars['title']))
                $modal->setTitle($this->cmd_vars['title']);

            $this->content = $modal->build();
        }

        // Use all set public ajax properties as ajax cmd definition
        foreach ( $this as $property => $value )
        {
            if (isset($value))
            {
                $definition->{$property} = $value;
                unset($this->{$property});
            }
        }

        // Ajax commands object not created? Create one.
        if (!isset(self::$ajax))
            self::$ajax = new \stdClass();

        // Publish ajax definition to ajaxlist
        self::$ajax->{'cmd_' . $counter} = $definition;

        // Raise ajax counter
        $counter++;
    }

    /**
     * Builds the ajax command structure
     */
    public static function process()
    {
        // Add messages
        $messages = Message::getMessages();

        if ($messages)
        {
            foreach ( $messages as $message )
            {
                self::factory()
                        ->setType('html')
                        ->setMode('append')
                        ->setTarget('#web-message')
                        ->setContent($message->build())
                        ->add();
            }
        }

        // Output is json encoded
        return json_encode(self::$ajax);
    }
}
?>

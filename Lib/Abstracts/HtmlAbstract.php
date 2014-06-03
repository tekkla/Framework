<?php
namespace Web\Framework\Lib\Abstracts;

use Web\Framework\Lib\Arrays;
use Web\Framework\Lib\Error;
use Web\Framework\Lib\Interfaces\HtmlInterface;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Parent class for html all elements
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
abstract class HtmlAbstract extends ClassAbstract implements HtmlInterface
{
    /**
     * Element type
     * @var string
     */
    private $element;

    /**
     * Attribute: name
     * @var string
     */
    private $name;

    /**
     * Attribute: id
     * @var string
     */
    private $id;

    /**
     * Attribute: class
     * @var array
     */
    private $css = array();

    /**
     * Attribute: style
     * @var array
     */
    private $style = array();

    /**
     * Events
     * @var array
     */
    private $event = array();

    /**
     * Custom html attributes
     * @var array
     */
    private $attribute = array();

    /**
     * Data attributes
     * @var array
     */
    private $data = array();

    /**
     * Inner HTML of element
     * @var string
     */
    private $inner;

    /**
     * Sets the element type like 'div', 'input', 'p' etc
     * @param string $element
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function setElement($element)
    {
        $this->element = $element;
        return $this;
    }

    /**
     * Returns element type
     * @return string
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Sets the element name
     * @param string $name
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * Removes element name
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function removeName()
    {
        unset($this->name);
        return $this;
    }

    /**
     * Returns name if set.
     * No name set it returns boolean false.
     * @return Ambigous <boolean, string>
     */
    public function getName()
    {
        return isset($this->name) ? $this->name : false;
    }

    /**
     * Sets the id of the element
     * @param string $id
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the id of the element
     * @return Ambigous <boolean, string>
     */
    public function getId()
    {
        return isset($this->id) ? $this->id : false;
    }

    /**
     * Removes id from elements
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function removeId()
    {
        unset($this->id);
        return $this;
    }

    /**
     * Sets inner value of element like
     * <code>
     * &lt;div&gt;{inner}&lt;/div&gt;
     * </code>
     * @param unknown $inner
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function setInner($inner)
    {
        $this->inner = $inner;
        return $this;
    }

    /**
     * Returns inner value if set.
     * No set returns boolean false.
     * @return Ambigous <boolean, string>
     */
    public function getInner()
    {
        return isset($this->inner) ? $this->inner : false;
    }

    /**
     * Sets html title attribute
     * @param string $title
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function setTitle($title)
    {
        $this->addAttribute('title', $title);
        return $this;
    }

    /**
     * Add one or more css classes to the html object.
     * Accepts single value, a string of space separated classnames
     * or an array of classnames.
     * @param string|array $css
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function addCss($css)
    {
        if (!is_array($css))
        {
            // Clean css argument from unnecessary spaces
            $css = preg_replace('/[ ]+/', ' ', $css);

            // Do not trust the programmer and convert a possible
            // string of multiple css class notations to array
            $css = explode(' ', $css);
        }

        foreach ( $css as $class )
            $this->css[$class] = $class;

        return $this;
    }

    /**
     * Checks for the existance of a css property in a html object or for a css class / array of css classes in the css property
     * @param string array $css Optional parameter can be a single css class as string or a list of classes in an array
     * @todo Monster... is this really needed in this complexity?
     * @return boolean
     */
    public function checkCss($check = null)
    {
        // Css (could be array) and objects css property set?
        if (isset($check) && $this->css)
        {
            // convert non array css to array
            if (!is_array($check))
                $check = array(
                    $check
                );

                // Is css to check already in objects css array?
            return array_intersect($check, $this->css) ? true : false;
        }
        else
            // Without set css param we only check if css is used
            return $this->css;
    }

    /**
     * Returns set css values.
     * Returns boolean false if css is empty.
     * @return array
     */
    public function getCss()
    {
        return $this->css ? $this->css : false;
    }

    /**
     * Adds a single style or an array of styles to the element.
     * Although no parameters are visible the method handles two different
     * types of parameter. Set two params for "key" and "value" or an array
     * with a collection of keys and values.
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function addStyle()
    {
        $type = func_num_args() == 1 ? 'pair_array' : 'pair_one';
        $this->addTo(func_get_args(), $type);
        return $this;
    }

    /**
     * Removes a style from the styles collection
     * @param string $style
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function removeStyle($style)
    {
        if (isset($this->style[$style]))
            unset($this->style[$style]);

        return $this;
    }

    /**
     * Adds a single event or an array of events to the element.
     * Although no parameters are visible the method handles two different
     * types of parameter. Set two params for "key" and "value" or an array
     * with a collection of keys and values.
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function addEvent()
    {
        $type = func_num_args() == 1 ? 'pair_array' : 'pair_one';
        $this->addTo(func_get_args(), $type);
        return $this;
    }

    /**
     * Adds a single style or an array of styles to the element.
     * Although no parameters are visible the method handles two different
     * types of parameter. Set two params for "key" and "value" or an array
     * with a collection of keys and values.
     * This method takes care of single attributes like "selected" or "disabled".
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function addAttribute()
    {
        if (func_num_args() == 1 && !is_array(func_get_args()))
        {
            $type = 'pair_one';
            $args = array(
                func_get_arg(0),
                false
            );
        }
        elseif (func_num_args() && is_array(func_get_arg(0)) && Arrays::isAssoc($this->func_get_arg(0)))
        {
            $type = 'pair_array';
            $args = func_num_args();
        }
        else
        {
            $type = 'pair_one';
            $args = func_num_args();
        }

        $this->addTo(func_get_args(), $type);
        return $this;
    }

    /**
     * Removes an attribute
     */
    public function removeAttribute($name)
    {
        if (isset($this->attribute[$name]))
            unset($this->attribute[$name]);

        return $this;
    }

    /**
     * Returns the requests attributes value
     * @param string $attribute
     * @throws Error
     */
    public function getAttribute($attribute)
    {
        if (!isset($this->attribute[$attribute]))
            Throw new Error('The requested attribute "' . $attribute . '" does not exits in this html element "' . get_called_class() . '".');
        else
            return $this->attribute[$attribute];
    }

    /**
     * Check for an set attribute
     * @param string $attribute
     */
    public function checkAttribute($attribute)
    {
        return isset($this->attribute[$attribute]);
    }

    /**
     * Adds a single data attribute or an array of data attributes to the element.
     * Although no parameters are visible the method handles two different
     * types of parameter. Set two params for "key" and "value" or an array
     * with a collection of keys and values.
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function addData()
    {
        $type = func_num_args() == 1 ? 'pair_array' : 'pair_one';
        $this->addTo(func_get_args(), $type);
        return $this;
    }

    /**
     * Returns the value of the requested data attribute.
     * Returns boolean false if data attribute is not set.
     * @param string $key
     * @return Ambigous <boolean, multitype:>
     */
    public function getData($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : false;
    }

    /**
     * Checks the existance of a data attribute
     * @param string $key
     * @return boolean
     */
    public function checkData($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Removes a data ttribute
     * @param string $key
     * @return \Web\Framework\Lib\HtmlAbstract
     */
    public function removeData($key)
    {
        if (isset($this->data[$key]))
            unset($this->data[$key]);

        return $this;
    }

    /**
     * Adds single and multiple elements to properties.
     * @param unknown $argumentlist
     * @param unknown $type
     */
    protected function addTo($argumentlist, $type)
    {
        $dt = debug_backtrace();
        $func = strtolower(str_replace('add', '', $dt[1]['function']));

        if (!isset($this->{$func}) || (isset($this->{$func}) && !is_array($this->$func)))
            $this->{$func} = array();

        switch ($type)
        {
            case 'value_one' :
                foreach ( $argumentlist as $val )
                    $this->{$func}[] = $val;
                break;

            case 'value_array' :
                foreach ( $argumentlist as $val )
                    foreach ( $val as $val2 )
                        $this->{$func}[] = $val2;
                break;

            case 'pair_one' :
                $this->{$func}[$argumentlist[0]] = $argumentlist[1];
                break;

            case 'pair_array' :
                foreach ( $argumentlist as $val )
                    foreach ( $val as $key2 => $val2 )
                        $this->{$func}[$key2] = $val2;
                break;
        }
    }

    /**
     * Builds and returns the html code created out of all set attributes and their values.
     * @param string $wrapper
     * @return string
     */
    public function build($wrapper = null)
    {
        $html_attr = array();

        if (isset($this->id))
            $html_attr['id'] = $this->id;

        if (isset($this->name))
            $html_attr['name'] = $this->name;

        if ($this->css)
            $html_attr['class'] = implode(' ', $this->css);

        if ($this->style)
        {
            $styles = array();

            foreach ( $this->style as $name => $val )
                $styles[] = $name . ': ' . $val;

            $html_attr['style'] = implode('; ', $styles);
        }

        if (isset($this->event))
        {
            foreach ( $this->event as $event => $val )
                $html_attr[$event] = $val;
        }

        if (isset($this->data))
        {
            foreach ( $this->data as $attr => $val )
                $html_attr['data-' . $attr] = $val;
        }

        if (isset($this->attribute))
        {
            foreach ( $this->attribute as $attr => $val )
                $html_attr[$attr] = $val;
        }

        // we have all our attributes => build attribute string
        $tmp_attr = array();

        foreach ( $html_attr as $name => $val )
            $tmp_attr[] = $val === false ? $name : $name . (strpos($name, 'data') === false ? '="' . $val . '"' : '=\'' . $val . '\'');

        $html_attr = implode(' ', $tmp_attr);

        // html attribute string has been created, lets build the element
        switch ($this->element)
        {
            case 'label' :
                $html = '<label ' . $html_attr . '>' . $this->inner . '</label>';
                break;

            case 'input' :
                $html = '<input ' . $html_attr . '>';
                break;

            case 'textarea' :
                $html = '<textarea ' . $html_attr . '>' . $this->inner . '</textarea>';
                break;

            case 'img' :
                $html = '<img ' . $html_attr . '>';
                break;

            default :
                $html = '<' . $this->element . ' ' . $html_attr . '>' . $this->inner . '</' . $this->element . '>';
                break;
        }

        if (isset($wrapper))
            $html = '<' . $wrapper . '>' . $html . '</' . $wrapper . '>';

        return $html;
    }
}
?>

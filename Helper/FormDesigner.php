<?php
namespace Web\Framework\Helper;

use Web\Framework\Lib\Error;
use Web\Framework\Lib\Model;
use Web\Framework\Lib\Txt;
use Web\Framework\Lib\String;
use Web\Framework\Lib\FileIO;
use Web\Framework\Html\Controls\UiButton;
use Web\Framework\Html\Form\Form;
use Web\Framework\Html\Form\Input;
use Web\Framework\Html\Form\Textarea;
use Web\Framework\Html\Form\Button;
use Web\Framework\Html\Form\Select;
use Web\Framework\Html\Form\Checkbox;
use Web\Framework\Html\Elements\Div;
use Web\Framework\Html\Elements\Heading;
use Web\Framework\Html\Elements\Paragraph;
use Web\Framework\Html\Controls\OptionGroup;
use Web\Framework\Html\Controls\OnOffSwitch;
use Web\Framework\Html\Controls\Editor;
use Web\Framework\Html\Controls\DataSelect;
use Web\Framework\Html\Controls\Group;
use Web\Framework\Html\Controls\DateTimePicker;
use Web\Framework\Lib\Abstracts\FormElementAbstract;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * FormDesigner
 * @todo Write explanation... or an app which explains the basics
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Helper
 * @license BSD
 * @copyright 2014 by author
 * @final
 */
final class FormDesigner extends Form
{
    /**
     * Form controls storage
     * @var array
     */
    private $controls = array();

    /**
     * The mode the data will be send to server
     * @var string Options: full | ajax / Default: full
     */
    private $send_mode = 'submit';

    /**
     * Form name extension
     * @var string
     */
    private $name_ext;

    /**
     * Name of the related app
     * @var string
     */
    public $app_name;

    /**
     * Name of the attached model
     * @var string
     */
    private $model_name;

    /**
     * Displaymode of the form h = horizontal v = vertical (default) i = inline
     * @see http://getbootstrap.com/css/#forms
     * @var string
     */
    private $display_mode = 'v';

    /**
     * The gridtype used in the form Select from:
     * col-xs, col-sm (default), col-md and col-lg
     * @see http://getbootstrap.com/css/#grid-options
     * @var string
     */
    private $grid_type = 'col-sm';

    /**
     * Buttons to use with this form.
     * Every form has a submit button.
     * @var array
     */
    private $buttons = array(
        'submit' => 'web_save'
    );

    /**
     * By default buttons have no value.
     * @var bool
     */
    private $use_button_values = false;

    /**
     * Icons for buttons
     * @var array
     */
    private $icons = array(
        'submit' => 'save',
        'reset' => 'eraser'
    );

    /**
     * Associated model
     * @var Model
     */
    private $model;

    /**
     * Name of the route which creates the action url
     * @var string
     */
    public $route;

    /**
     * *********************************
     */
    /* ??? */
    /**
     * *********************************
     */
    private $group_open = false;

    private $group_name;

    private $group_headline;

    private $group_header;

    private $group_description;

    private $group;

    private $grid_label;

    private $grid_control;

    /**
     * Inject model dependency
     * @param Model $model
     * @return \Web\Framework\Html\Elements\FormDesigner
     */
    public function attachModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    public function setModelName($model_name)
    {
        $this->model_name = String::uncamelize($model_name);
    }

    public function setGridLabel($size)
    {
        $this->grid_label = $size;
        return $this;
    }

    public function setGridControl($size)
    {
        $this->grid_control = $size;
        return $this;
    }

    /**
     * Extends the form name and id on creation with this extensiondata
     * @param int|string $name_ext
     * @return \Web\Framework\Html\Elements\FormDesigner
     */
    public function extendName($name_ext)
    {
        $this->name_ext = $name_ext;
        return $this;
    }

    /**
     * Set the sendmode for the form.
     * You can use the html submit or the frameworks ajax system. Default: 'submit'
     * @param string $send_mode Send mode which can be 'ajax' or 'submit'
     * @throws Error
     * @return \Web\Framework\Html\Elements\FormDesigner
     */
    public function setSendMode($send_mode)
    {
        if (!in_array($send_mode, array(
            'ajax',
            'submit'
        )))
            Throw new Error('Wrong form sendmode.', 1000, array('ajax', 'submit'));

        $this->send_mode = $send_mode;
        return $this;
    }

    /**
     * Set the name of the related app.
     * @param string $app_name
     * @return \Web\Framework\Html\Elements\FormDesigner
     */
    public function setApp($app_name)
    {
        $this->app_name = (string) $app_name;
        return $this;
    }

    /**
     * Start control group
     * @param unknown $group_name
     * @return Group
     */
    public function &openGroup($group_name)
    {
        // close current open group
        if (isset($this->group_name))
            $this->closeGroup();

        $this->group_name = $group_name;

        $this->controls[$this->group_name] = Group::factory($group_name);

        return $this->controls[$this->group_name];
    }

    /**
     * Checks for an currently open group and closes it if found.
     */
    public function closeGroup()
    {
        if (isset($this->group_name))
        {
            $this->controls[$this->group_name . '_close'] = 'close_group';
            unset($this->group_name);
        }
    }

    public function setGroupHeadline($headline)
    {
        $this->group_headline = $headline;
    }

    public function setGroupHeader($header)
    {
        $this->group_header = $header;
    }

    /**
     * Creates a formelement and adds it by it's name to the controls member.
     * @param string $type The type of control to create
     * @param string $name Name of the control. Ths name is used to bind the control to a model field.
     * @throws Error
     * @return Ambigous <\Web\Framework\Html\Controls\UiButton, \Web\Framework\Html\Controls\Editor>, Html
     */
    public function &createElement($type, $name = null, $params = null)
    {
        switch ($type)
        {
            case 'hidden' :
				/* @var Input $element */
				$element = Input::factory($name)->setType('hidden');
                break;

            case 'text' :
				/* @var Input $element */
				$element = Input::factory($name)->addCss('web-form-text');
                break;

            case 'number' :
				/* @var Input $element */
				$element = Input::factory($name)->setType('number')->addCss('web-form-number');
                break;

            case 'mail' :
				/* @var Input $element */
				$element = Input::factory($name)->setType('mail')->addCss('web-form-mail');
                break;

            case 'phone' :
				/* @var Input $element */
				$element = Input::factory($name)->addCss('web-form-phone');
                break;

            case 'url' :
				/* @var Input $element */
				$element = Input::factory($name)->addCss('web-form-url');
                break;

            case 'date' :
            case 'date-iso' :
                $element = DateTimePicker::factory($name)->setFormat('YYYY-MM-DD')->setMask('9999-99-99')->setMaxlenght(10);
                break;

            case 'date-us' :
                $element = DateTimePicker::factory($name)->setFormat('mm/dd/yyyy')->setMask('99/99/9999')->setSize(10)->setMaxlenght(10);
                break;

            case 'date-gr' :
                $element = DateTimePicker::factory($name)->setFormat('dd.mm.yyyy')->setMask('99.99.9999')->setSize(10)->setMaxlenght(10);
                break;

            case 'time-24' :
                $element = DateTimePicker::factory($name)->setFormat('HH:mm')->setMask('99:99')->setSize(5)->setMaxlenght(5);
                break;

            case 'time-24s' :
                $element = DateTimePicker::factory($name)->setFormat('HH:mm:ss')->setMask('99:99:99')->setSize(8)->setMaxlenght(8);
                break;

            case 'time-12' :
                $element = DateTimePicker::factory($name)->setFormat('hh:mm A/PM')->setMask('99:99')->setSize(5)->setMaxlenght(5);
                break;

            case 'time-12s' :
                $element = DateTimePicker::factory($name)->showMeridian(true)->setFormat('hh:mm::ss A/PM')->setMask('99:99:99')->setSize(8)->setMaxlenght(8);
                break;

            case 'datetime' :
            case 'datetime-iso' :
                $element = DateTimePicker::factory($name)->setFormat('YYYY-MM-DD HH:mm')->setMask('9999-99-99 99:99')->setSize(16)->setMaxlenght(16);
                break;

            case 'password' :
                $element = Input::factory($name)->setType('password')->addCss('web-form-password');
                break;

            case 'file' :
                $element = Input::factory($name)->setType('file')->addCss('web-form-file');
                break;

            case 'select' :
                $element = Select::factory($name)->addCss('web-form-select');
                break;

            case 'multiselect' :
				/* @var Select $element */
				$element = Select::factory($name)->isMultiple(1)->setSize(10)->addCss('web-form-multiselect');
                break;

            case 'dataselect' :
                $element = DataSelect::factory($name)->addCss('web-form-select');
                break;

            case 'submit' :
                $element = Button::factory($name)->setType('submit')->setInner(Txt::get('web_btn_save'))->useIcon('save')->isPrimary();
                break;

            case 'reset' :
                $element = Button::factory($name)->setType('reset')->setInner(Txt::get('web_btn_reset'));
                break;

            case 'textarea' :
                $element = Textarea::factory($name);
                break;

            case 'checkbox' :
                $element = Checkbox::factory($name)->addCss('web-form-checkbox');
                break;

            case 'switch' :
                $element = OnOffSwitch::factory($name)->addCss('web-form-switch');
                break;

            case 'optiongroup' :
                $element = OptionGroup::factory($name)->addCss('web-form-optiongroup');
                break;

            case 'button' :
                $element = Button::factory($name)->setType('button');
                break;

            case 'ajaxbutton' :
                $element = UiButton::factory('ajax', 'button');
                break;

            case 'ajaxicon' :
                $element = UiButton::factory('ajax', 'icon');
                break;

            case 'editor' :
                $element = Editor::factory();
                break;

            case 'range' :
                if (!isset($params))
                    Throw new Error('Range elements need min and max parameters to be set. None was set.', 1001);

                if (count($params) < 2)
                    Throw new Error('Range elements need min and max parameters to be set. You only set one parameter.', 1001);

                if (!is_int($params[0]) || !is_int($params[1]))
                    Throw new Error('Range elements parameter need to be of type INT.', 1000);

                $element = Input::factory($name, 'number')->addCss('web-form-number')->addAttribute('min', $params[0])->addAttribute('max', $params[1]);

                break;

            /*
             * @TODO TYPES color image month radio range search tel week
             */

            // Simple html elements for layouting
            case 'h1' :
            case 'h2' :
            case 'h3' :
            case 'h4' :
            case 'h5' :
            case 'h6' :
                $element = Heading::factory(substr($type, -1, 1))->setInner($name);
                $name = uniqid('heading_');
                break;

            case 'p' :
                $element = Paragraph::factory()->setInner($name);
                $name = uniqid('hint_');
                break;
            default :
                $element = Input::factory($name)->addCss('web-form-text');
                break;
        }

        if (isset($this->model) && isset($this->model->data) && isset($this->model->data->{$name}) && method_exists($element, 'setField'))
        {
            $element->setField($name);
        }
        else
        {
            // Set element as unbound as long as it is a FormElement subclass
            if ($element instanceof FormElementAbstract)
                $element->setUnbound();
        }

        $this->controls[$name] = $element;

        return $this->controls[$name];
    }

    /**
     * Add a html form control to forms controllist
     * @param object|string $control
     * @return \Web\Framework\Lib\Form\Form
     */
    public function addControl($name, $control)
    {
        $this->controls[$name] = $control;
        return $this;
    }

    public function build($wrapper = null)
    {
        if (empty($this->controls))
            Throw new Error('Your form has no controls to show. Add controls and try again.', 10000);

        $base_form_name = 'web_appform';

        // model attched forms will use the model settings to set the form, app
        // and model name
        if ($this->hasModel())
        {
            $this->app_name = $this->model->app->getName();
            $this->model_name = $this->model->getName();
        }
        else
        {
            // manual forms need a set app and model name
            if (!isset($this->app_name))
                Throw new Error('With no model object assigned, your form needs an manual set app name.', 10000);

            if (!isset($this->model_name))
                Throw new Error('With no model object assigned, your form needs an model name to use it as pseudo model.', 10000);
        }

        $this->app_name = String::uncamelize($this->app_name);
        $this->model_name = String::uncamelize($this->model_name);

        // create formname
        $form_name = $form_id = $base_form_name . '_' . $this->app_name . '_' . $this->model_name . (isset($this->name_ext) ? '_' . $this->name_ext : '');

        // create control name prefix
        $control_name_prefix = 'web[' . $this->app_name . '][' . $this->model_name . ']';

        // create control id prefix
        $control_id_prefix = 'web_' . $this->app_name . '_' . $this->model_name;

        // set name only when no name was set
        $this->name = $form_name;

        // set id only when no id was set
        $this->id = $form_name;

        // Create display mode
        switch ($this->display_mode)
        {
            case 'h' :
                $this->addCss('form-horizontal');
                break;
            case 'i' :
                $this->addCss('form-inline');
                break;
        }

        // control html container
        $html_control = '';

        // are there global and not control related errors?
        if ($this->hasModel() && $this->model->hasErrors() && isset($this->model->errors['@']))
        {
            $div = Div::factory();
            $div->addCss('alert alert-danger');
            $div->setInner(implode('<br>', $this->model->errors['@']));

            $html_control .= $div->build();
        }

        // Create form buttons
        foreach ( $this->buttons as $btn => $text )
        {
            $btn_name = 'web_btn_' . $btn;
            $btn_id = 'web-btn-' . $btn;

            /* @var $button \Web\Framework\Html\Form\Button */
            $button = $this->createElement('button', $btn_name)->setId($btn_id)->setInner(Txt::get($text));

            switch ($btn)
            {
                case 'submit' :
                    $button->addCss('btn-primary');

                    switch ($this->send_mode)
                    {
                        case 'submit' :
                            $button->setType('submit')->addAttribute('form', $this->getId());
                            break;

                        case 'ajax' :
                            $button->addData('web-ajax', 'form')->addData('web-form', $this->getId());
                            break;
                    }

                    break;

                case 'reset' :
                    $button->setType('reset')->setInner(Txt::get('web_btn_reset'));
                    break;
            }

            if (isset($this->icons[$btn]))
                $button->useIcon($this->icons[$btn]);
        }

        // Open group left? Close if it is so.
        $this->closeGroup();

        $tabindex = 0;

        foreach ( $this->controls as $control_field => $control )
        {
            // No object and no Group? Next please!
            if (!is_object($control))
                if ($control != 'close_group')
                    continue;

            if ($control instanceof Group)
            {
                $this->group = $control;
                $this->group_open = true;
                continue;
            }

            // No object but Group?
            if ($control == 'close_group')
            {
                $html_control .= $this->group->build();
                $this->group_open = false;
                continue;
            }

            // Is the control a ui button and the mode is ajax?
            if ($control instanceof UiButton)
            {
                if ($control->getMode() == 'ajax')
                {
                    $control->setForm($this->getId());

                    if (isset($this->route))
                        $control->url->setNamedRoute($this->route);
                    else
                        $control->url->setAction($this->getAttribute('action'));
                }

                $html_control .= $control->build();
            }
            // No ajax button. Normal form control.
            elseif ($control instanceof FormElementAbstract)
            {
                // Only visible fields get a tabindex
                if (!$control instanceof Input || ($control instanceof Input && $control->getType() !== 'hidden'))
                {
                    $control->addAttribute('tabindex', $tabindex);
                    $tabindex++;
                }

                // What type of control do we have to handle?
                $type = $control->getData('web-control');

                // Create the control name
                $field_name = String::uncamelize($control->isBound() ? $control->getField() : $control->getName());

                // create control name web[app][model][existing name]
                if (method_exists($control, 'setName'))
                {
                    // Remove button name?
                    if (!$control instanceof Button || ($control instanceof Button && $this->use_button_values == true))
                    {
                        $name = ($type == 'input' && $control->getType() == 'file') ? 'web_files' : $control_name_prefix . '[' . $field_name . ']';
                        $control->setName($name);
                    }
                    else
                    {
                        $control->removeName();
                    }
                }

                // create control id {app}_{model}_{existing id}
                $control->setId($control_id_prefix . '_' . $field_name);

                // Set BS group class
                switch ($type)
                {
                    case 'radio' :
                        $container = '<div class="radio{state}"><label class="control-label" for="{id}">{control}{description}</label></div>';
                        break;

                    case 'checkbox' :
                        $container = '<div class="checkbox{state}"><label class="control-label" for="{id}">{control}{description}</label></div>';
                        break;

                    case 'button' :
                    case 'hidden' :
                        $container = '{control}';
                        break;

                    default :
                        $container = '<div class="form-group{state}">{label}{control}{help}</div>';
                        $control->addCss('form-control');
                        break;
                }

                // Preset the control data if there are model fields and values
                if ($control->isBound() && isset($this->model->data->{$field_name}))
                {
                    if (method_exists($control, 'setValue'))
                        $control->setValue($this->model->data->{$field_name});

                    if (method_exists($control, 'setInner'))
                        $control->setInner($this->model->data->{$field_name});

                        // The following controls do not like empty strings as value
                    if ($this->model->data->{$field_name} !== '')
                    {
                        if (method_exists($control, 'setSelectedValue'))
                            $control->setSelectedValue($this->model->data->{$field_name});

                        if ($type == 'checkbox' && $this->model->data->{$field_name} == $control->getValue())
                            $control->isChecked(1);

                        if (method_exists($control, 'switchOn') && $this->model->data->{$field_name} >= 1)
                            $control->switchOn();
                    }
                }

                // Set the form id to editor controls
                if ($type == 'editor')
                	$control->setFormId($this->getId());

                // Hidden controls dont need any label or other stuff to display
                if ($type == 'hidden')
                {
                    $html_control .= $control->build();
                    continue;
                }

                // Set working state for fields to nothing
                $state = '';

                // Add possible validation errors css to label and control
                if ($this->hasModel() && $this->model->hasErrors())
                {
                    if (isset($this->model->errors[$field_name]))
                    {
                        $control->addData('error', implode('<br>', $this->model->errors[$field_name]));
                        $state = ' has-error';
                        $container = str_replace('{help}', '{help}<div class="small text-danger">' . implode('<br>', $this->model->errors[$field_name]) . '</div>', $container);
                    }
                }

                // Insert gropupstate
                $container = str_replace('{state}', $state, $container);

                // Try to find a suitable text as label in our languagefiles
                if ($control->hasLabel() && !$control->getLabel())
                {
                    $label = Txt::get($this->model_name . '_' . String::uncamelize($control_field), $this->app_name);
                    $control->setLabel($label);
                }

                // Add possible label
                if ($control->hasLabel())
                {
                    $control->label->setFor($control->getId());

                    // Make it a BS control label
                    $control->label->addCss('control-label');

                    // Horizontal forms needs grid size for label
                    if ($this->display_mode == 'h')
                        $control->label->addCss('col-sm-4');

                    $label = $control->label->build();
                }
                else
                {
                    $label = '';
                }

                // Insert label into controlcontainer
                $container = str_replace('{label}', $label, $container);

                // Build possible description
                $help = $control->hasDescription() ? '<span class="help-block">' . $control->getDescription() . '</span>' : '';

                // Insert description into controlcontainer
                $container = str_replace('{help}', $help, $container);

                // Insert dom id of related control for checkbox and radio labels
                if ($type == 'checkbox' || $type == 'radio')
                    $container = str_replace('{id}', $control->getId(), $container);

                // Add max file size field before file input field
                if ($control instanceof Input && $control->getType() == 'file')
                {
                    $max_size_field = Input::factory('MAX_FILE_SIZE')->setType('hidden')->setValue(FileIO::getMaximumFileUploadSize());
                    $container = str_replace('{control}', $max_size_field->build() . '{control}', $container);
                }

                // Add hidden field to compare posted value with previous value.
                if ($control->hasCompare())
                {
                    $compare_name = str_replace($field_name, $field_name . '_compare', $control->getName());
                    $compare_control = Input::factory($compare_name)->setType('hidden')->setValue($control->getValue())->setId($control->getId() . '_compare');
                    $container = str_replace('{control}', '{control}' . $compare_control->build(), $container);
                }

                // Build control
                $ctrl = $this->display_mode == 'h' && $control->hasLabel() ? '<div class="col-sm-8">' . $control->build() . '</div>' : $control->build();

                if ($control->hasElementWidth())
                    $container = '<div class="' . $control->getElementWidth() . '">' . $container . '</div>';

                $html = str_replace('{control}', $ctrl, $container);

                if ($this->group_open)
                    $this->group->addContent($html);
                else
                    $html_control .= $html;
            }
            else
            {
                $html = $control->build();

                if ($this->group_open)
                    $this->group->addContent($html);
                else
                    $html_control .= $html;
            }
        }

        $this->setInner($html_control);

        return parent::build($wrapper);
    }

    private function hasModel()
    {
        return isset($this->model);
    }

    /**
     * Sets the forms display mode to horizontal
     * @return \Web\Framework\Html\Elements\FormDesigner
     */
    public function isHorizontal()
    {
        $this->display_mode = 'h';
        return $this;
    }

    /**
     * Sets the forms display mode to vertical
     * @return \Web\Framework\Html\Elements\FormDesigner
     */
    public function isVertical()
    {
        $this->display_mode = 'v';
        return $this;
    }

    /**
     * Sets the forms display mode to inline
     * @return \Web\Framework\Html\Elements\FormDesigner
     */
    public function isInline()
    {
        $this->display_mode = 'i';
        return $this;
    }

    /**
     * Wrapper method for setSendMode('ajax')
     * @return \Web\Framework\Html\Elements\FormDesigner
     */
    public function isAjax()
    {
        $this->setSendMode('ajax');
        return $this;
    }

    /**
     * Wrapper method for setSendmode('full')
     * @return \Web\Framework\Html\Elements\FormDesigner
     */
    public function isSubmit()
    {
        $this->setSendMode('submit');
        return $this;
    }

    /**
     * Set icon for a button
     * @param string $button Name of the button the icon is related to
     * @param string $icon Name of the icon to use
     * @throws Error
     * @return \Web\Framework\Html\Elements\FormDesignerDesigner
     */
    public function setIcon($button, $icon)
    {
        if (!array_key_exists($button, $this->icons))
            Throw new Error('Form Tool: Button not ok.');

        $this->icons[$button] = $icon;
        return $this;
    }

    /**
     * Remove one or all set button icons.
     * @param string $button Optional Name of the button to remove. If not set, all icons will be removed.
     * @throws Error
     */
    public function removeIcon($button = null)
    {
        if (isset($button) && !in_array($button, $this->icons))
            Throw new Error('Form Tool: Can not remove button icon');

        if (!isset($button))
            $this->icons = null;
        else
            unset($this->icons[$button]);
    }

    /**
     * Adds reset button to forms buttonlist
     *
     * @return \Web\Framework\Html\Elements\FormDesigner
     */
    public function useResetButton()
    {
        $this->buttons['reset'] = 'web_reset';
        return $this;
    }

    /**
     * Access to the control objects of this form
     *
     * @param string $control_name The name of the control you want to access
     * @throws Error
     * @return multitype:
     */
    public function getControl($control_name)
    {
        if (!isset($this->controls[$control_name]))
            Throw new Error('The requested control [' . $control_name . '] does not exist in this form.');

        return $this->controls[$control_name];
    }

    public function setSaveButtonText($text)
    {
        $this->buttons['submit'] = $text;
        return $this;
    }

    /**
     * Disables the automatic button creation.
     * Good when you use an alternative like the Actionbar helper.
     */
    public function noButtons()
    {
        $this->buttons = array();
        return $this;
    }

    public function setEnctype($enctype)
    {
        $this->addAttribute('enctype', $enctype);
        return $this;
    }
}
?>

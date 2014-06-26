<?php
namespace Web\Framework\Html\Controls;

use Web\Framework\Html\Elements\Div;
use Web\Framework\Html\Form\Button;
use Web\Framework\Lib\Error;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a Bootstrap buttongroup
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Form
 * @license BSD
 * @copyright 2014 by author
 */
final class ButtonGroup extends Div
{
    /**
     * Button stroage
     * @var array
     */
    private $buttons = array();

    /**
     * Adds a button to the group
     * @param Button $button
     * @return \Web\Framework\Html\Controls\ButtonGroup
     */
    public function addButton($button)
    {
        if (!$button instanceof Button && !$button instanceof UiButton)
            Throw new Error('Buttons for a buttongroup must be an instance of Button or UiButton');

        if (!$button->checkCss('btn'))
            $button->addCss('btn');

        $this->buttons[] = $button;
        return $this;
    }

    /**
     * Builds buttongroup
     * @throws Error
     * @return string
     * @see \Web\Framework\Lib\Abstracts\HtmlAbstract::build()
     */
    public function build($wrapper = null)
    {
        if (empty($this->buttons))
            Throw new Error('No buttons for buttongroup set.');

        $inner = '';

        /* @var $button Button */
        foreach ( $this->buttons as $button )
            $inner .= $button->build();

        $this->setInner($inner);
        $this->addCss('btn-group');

        return parent::build($wrapper);
    }
}
?>

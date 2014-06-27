<?php
namespace Web\Framework\Html\Controls;

use Web\Framework\Html\Elements\Div;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a group control with heading and leading text.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Controls
 * @license BSD
 * @copyright 2014 by author
 */
class Group extends Div
{

	private $new_row = false;

	/**
	 * Heading text
	 * @var string
	 */
	private $heading_text;

	/**
	 * Heading size
	 * @var int
	 */
	private $heading_size;

	/**
	 * Lead text
	 * @var string
	 */
	private $description;

	/**
	 * Closing text
	 * @var string
	 */
	private $footer;

	/**
	 * Use bootstrap panel
	 * @var boolean
	 */
	private $use_panel = false;

	/**
	 * When BS panel which style
	 * @var string
	 */
	private $panel_type = 'default';

	private $row = false;

	/**
	 * Group content
	 * @var string
	 */
	private $content = '';

	/**
	 * Factory method
	 * @param string $id
	 * @return \Web\Framework\Html\Controls\Group
	 */
	public static function factory($id=null)
	{
		$obj = new Group();

		if (isset($id))
			$obj->setId($id);

		return $obj;
	}

	/**
	 * Sets group to be displayed as Bootstrap panel
	 * @param bool $use_panel
	 * @return \Web\Framework\Html\Controls\Group
	 */
	public function usePanel($use_panel=true)
	{
		$this->use_panel = is_bool($use_panel) ? $use_panel : false;
		return $this;
	}

	/**
	 * Set heading text and size
	 * @param string $heading_text
	 * @param number $heading_size
	 * @return \Web\Framework\Html\Controls\Group
	 */
	public function setHeading($heading_text, $heading_size = 2)
	{
		$this->heading_text = $heading_text;
		$this->heading_size = is_int($heading_size) ? $heading_size : 2;
		return $this;
	}

	/**
	 * Set lead description text
	 * @param string $description
	 * @return \Web\Framework\Html\Controls\Group
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * Set footer text
	 * @param string $footer
	 * @return \Web\Framework\Html\Controls\Group
	 */
	public function setFooter($footer)
	{
		$this->footer = $footer;
		return $this;
	}

	/**
	 * Adds the content of group
	 * @param string $content
	 * @return \Web\Framework\Html\Controls\Group
	 */
	public function addContent($content)
	{
		$this->content .= $content;
		return $this;
	}

	/**
	 * Unset row display mode
	 * @return \Web\Framework\Html\Controls\Group
	 */
	public function noRow()
	{
		$this->row = false;
		return $this;
	}

	/**
	 * Force content to be displayed in a new row.
	 * This is important for content elements with grid sizes set.
	 * @return \Web\Framework\Html\Controls\Group
	 */
	public function newRow()
	{
		$this->row = true;
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Web\Framework\Lib\Abstracts\HtmlAbstract::build()
	 */
	public function build()
	{
		// Prepare group template.
		$html = '';

		if ($this->use_panel == true)
		{
			// Bootstrap panel template
			$html .= '<div class="panel panel-' . $this->panel_type . '">';

			if (isset($this->heading_text))
				$html .= '{heading}';

			$html .='<div class="panel-body">';

			if (isset($this->description))
				$html .= '{description}';

			if ($this->row)
				$html .= '<div class="row">';

			$html .= '{content}</div>';

			if ($this->row)
				$html .= '</div>';

			if (isset($this->footer))
				$html .= '{footer}';

			$html .= '</div>';
		}
		else
		{
			if ($this->row)
				$html .= '<div class="row">';

			if (isset($this->heading_text))
				$html .= '{heading}';

			if (isset($this->description))
				$html .= '{description}';

			$html .= '{content}';

			if (isset($this->footer))
				$html .= '{footer}';

			if ($this->row)
				$html .= '</div>';
		}

		// Create possible heading
		if (isset($this->heading_text))
		{
			// Heading: plain or withe BS title?
			$heading = '<h' . $this->heading_size . ($this->use_panel==true ? ' class="panel-title"' : '') . '>' . $this->heading_text . '</h' . $this->heading_size . '>';

			// Replace heading in BS panel template...
			$html = str_replace('{heading}', $this->use_panel == true ? '<div class="panel-heading">' . $heading . '</div>' : $heading, $html);
		}

		// Is there a description do create?
		if (isset($this->description))
		{
			// The description with small
			$description = '<p class="small">' . $this->description . '</p>';

			// Into the panel template...
			$html = str_replace('{description}', $description, $html);
		}

		// Add the content
		$html = str_replace('{content}', $this->content, $html);


		if (isset($this->footer))
		{
			$footer = '<span class="help-block">' . $this->description . '</span>';
			$html = str_replace('{footer}', $this->use_panel == true ? '<div class="panel-footer">' . $footer . '</div>' : $footer, $html);
		}

		$this->setInner($html);

		return parent::build();

	}
}
?>

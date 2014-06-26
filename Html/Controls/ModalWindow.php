<?php
namespace Web\Framework\Html\Controls;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a Bootstrap modal window control
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Controls
 * @license BSD
 * @copyright 2014 by author
 */
final class ModalWindow
{
    /**
     * Windowtitle
     * @var string
     */
    private $title = 'ModalWindow';

    /**
     * Content
     * @var string
     */
    private $content = 'No content set';

    /**
     * Factory method
     * @return \Web\Framework\Html\Controls\ModalWindow
     */
    public function factory()
    {
        return new ModalWindow();
    }

    /**
     * Set title of window
     * @param string $title
     * @return \Web\Framework\Html\Controls\ModalWindow
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;
        return $this;
    }

    /**
     * Sets content of window
     * @param string $content
     * @return \Web\Framework\Html\Controls\ModalWindow
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Builds and returns modal window html
     * @return string
     */
    public function build()
    {
        $html = '
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="web-modal-title">' . $this->title . '</h4>
				</div>
				<div class="modal-body" id="web-modal-content">' . $this->content . '</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary">Save changes</button>
				</div>
			</div>
		</div>';

        return $html;
    }
}
?>

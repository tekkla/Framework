<?php
namespace Web\Framework\Html\Controls;

use Web\Framework\Lib\Javascript;
use Web\Framework\Lib\Cfg;
use Web\Framework\Html\Form\Input;
use Web\Framework\Html\Elements\Div;
use Web\Framework\Html\Elements\FormElement;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Creates a CKE inline control
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Html\Controls
 * @license BSD
 * @copyright 2014 by author
 */
class Editor extends FormElement
{

    /**
     * Height in px
     * @var int
     */
    private $height = 600;

    /**
     * Background color (hex)
     * @var string
     */
    private $color = '#666';

    /**
     * Use filebrowser flag
     * @var bool
     */
    private $filebrowser_use = false;

    /**
     * Filebrowser width
     * @var int string
     */
    private $filebrowser_width = 600;

    /**
     * Filebrowser height
     * @var int string
     */
    private $filebrowser_height = 300;

    /**
     * Filebrowser userrole
     * @var string
     */
    private $filebrowser_userrole = '';

    /**
     * Id of form the editor belongs to
     * @var string
     */
    private $form_id;

    /**
     * Hidden value form field
     * @var Input
     */
    private $content_element;

    /**
     * Visible editor area div
     * @var Div
     */
    private $edit_element;

    public static function factory()
    {
        $obj = new Editor();

        // add needed js libraries
        $obj->addEditorScript();

        return $obj;
    }

    public function __construct()
    {
        // our editor will be uesd as inline editor
        $this->edit_element = Div::factory()->addAttribute('contenteditable', 'true')->addData('url', Cfg::Get('web', 'url_tools'))->addCss('web_form_editor web_border web_pad web_bg');

        // we need an hidden form field for content to post
        $this->content_element = Input::factory('', 'hidden');
    }

    private function addEditorScript()
    {
        Javascript::useFile( Cfg::Get('web', 'url_tools') . '/ckeditor/ckeditor.js?' . time(), true);
    }

    public function getType()
    {
        return 'editor';
    }

    public function setValue($value)
    {
        $this->edit_element->setInner($value);
        return $this;
    }

    public function setId($id)
    {
        $this->edit_element->setId($id . '_editor');
        $this->content_element->setId($id);
        return $this;
    }

    public function setName($name)
    {
        // the hidden field is the field with the form content
        $this->content_element->setName($name);

        return $this;
    }

    public function setFormId($form_id)
    {
        $this->form_id = $form_id;
        return $this;
    }

    public function setFilebrowserWidth($width)
    {
        $this->edit_element->addData('width', $width);
        return $this;
    }

    public function setFilebrowserHeight($height)
    {
        $this->edit_element->addData('height', $height);
        return $this;
    }

    /**
     * Sets user role and grants access on filebrowser
     * @param string $role
     * @return \Web\Framework\Html\Controls\Editor
     */
    public function setUserRole($role)
    {
        $_SESSION['web']['KCFinder_Role'] = $role;
        $_SESSION['web']['KCFinder_Access'] = true;

        return $this;
    }

    public function setUploadDir($uploaddir)
    {
        // filebrowser needs to stay in it's image uploadfolder
        $_SESSION['web']['KCFinder_uploaddir'] = $uploaddir;
        return $this;
    }

    public function build($wrapper = null)
    {
        $script = "
		if (typeof CKEDITOR !== undefined)
		{
			CKEDITOR.disableAutoInline = true;

			var editor = CKEDITOR.inline( '{$this->edit_element->getId()}',
			{
				on :
				{
					instanceReady : function( ev )
					{
						// Output paragraphs as <p>Text</p>.
						this.dataProcessor.writer.setRules( 'p',
						{
							indent : false,
							breakBeforeOpen : false,
							breakAfterOpen : false,
							breakBeforeClose : false,
							breakAfterClose : false
						});
					}
				},
				language : $('#{$this->edit_element->getId()}').data('lang'),
				filebrowserBrowseUrl : $('#{$this->edit_element->getId()}').data('url') + '/kcfinder/browse.php?opener=ckeditor&type=files',
				filebrowserUploadUrl : $('#{$this->edit_element->getId()}').data('url') + '/kcfinder/upload.php?opener=ckeditor&type=files',
				filebrowserImageBrowseUrl : $('#{$this->edit_element->getId()}').data('url') + '/kcfinder/browse.php?opener=ckeditor&type=images',
				filebrowserImageUploadUrl : $('#{$this->edit_element->getId()}').data('url') + '/kcfinder/upload.php?opener=ckeditor&type=images',
			});

			$('#{$this->form_id}').submit(function() {
				$('#{$this->content_element->getId()}').val( editor.getData() );
			});
		}";

        Javascript::useScript($script, true );

        $html = $this->content_element->build();
        $html .= $this->edit_element->build();

        return $html;
    }
}
?>
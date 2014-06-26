<?php
namespace Web\Framework\Html\Controls;

use Web\Framework\Lib\Javascript;
use Web\Framework\Lib\Cfg;
use Web\Framework\Html\Form\Input;
use Web\Framework\Html\Elements\Div;
use Web\Framework\Lib\Abstracts\FormElementAbstract;

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
final class Editor extends FormElementAbstract
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
    private $filebrowser_use = true;

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
        return new self;
    }

    public function __construct()
    {
        // our editor will be uesd as inline editor
        $this->edit_element = Div::factory()->addAttribute('contenteditable', 'true')->addData('url', Cfg::Get('Web', 'url_tools'));

        // we need an hidden form field for content to post
        $this->content_element = Input::factory()->setType('hidden');

        $this->addData('web-control', 'editor');

        // Add needed CKE js library
        Javascript::useFile( Cfg::Get('Web', 'url_tools') . '/ckeditor/ckeditor.js?' . time());
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
        echo __METHOD__;

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
			$(document).ready(function() {
			    CKEDITOR.disableAutoInline = true;

                CKEDITOR.stylesSet.add( 'my_styles', [
                    // Block-level styles
                    { name: 'BS Code', element: 'code' },
                    { name: 'BS Jumbotron', element: 'div', attributes: { 'class': 'jumbotron' } },
                ] );

			    var editor = CKEDITOR.inline('{$this->edit_element->getId()}', {
			        stylesSet : 'my_styles',
			        on : {
			            instanceReady : function(){
			                this.dataProcessor.writer.setRules('p', {
			                    indent : false,
			                    breakBeforeOpen : false,
			                    breakAfterOpen : false,
			                    breakBeforeClose : false,
			                    breakAfterClose : false
						    });
					   },
				    },
				    extraPlugins: 'bs-highlight,bs-jumbotron,bs-heading,bs-callout',
				    language : smf_lang_dictionary,
				    filebrowserBrowseUrl : $('#{$this->edit_element->getId()}').data('url') + '/kcfinder/browse.php?opener=ckeditor&type=files',
				    filebrowserUploadUrl : $('#{$this->edit_element->getId()}').data('url') + '/kcfinder/upload.php?opener=ckeditor&type=files',
				    filebrowserImageBrowseUrl : $('#{$this->edit_element->getId()}').data('url') + '/kcfinder/browse.php?opener=ckeditor&type=images',
				    filebrowserImageUploadUrl : $('#{$this->edit_element->getId()}').data('url') + '/kcfinder/upload.php?opener=ckeditor&type=images',
                });
			});

			$('#{$this->form_id}').submit(function(e) {
				$('#{$this->content_element->getId()}').val( editor.getData() );
				bootbox(editor.getData());
				e.preventDefault();
			});
		}";

        Javascript::useScript($script);

        $html = $this->content_element->build();
        $html .= $this->edit_element->build();

        return $html;
    }
}
?>
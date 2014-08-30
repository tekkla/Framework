<?php
namespace Web\Framework\AppsSec\Forum\Model;

use Web\Framework\Lib\Model;

if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Message model
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage AppSec Forum
 * @license BSD
 * @copyright 2014 by author
*/
final class MessagesModel extends Model
{
	protected $tbl = 'messages';
	protected $alias = 'msg';
	protected $pk = 'id_msg';
}
?>

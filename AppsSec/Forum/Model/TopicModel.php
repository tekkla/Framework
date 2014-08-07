<?php
namespace Web\Framework\AppsSec\Forum\Model;

use Web\Framework\Lib\Model;
use Web\Framework\Lib\Smf;

if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Setup model
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage AppSec Forum
 * @license BSD
 * @copyright 2014 by author
 */
final class TopicModel extends Model
{
    protected $tbl = 'topics';
    protected $alias = 'topic';
    protected $pk = 'id_topic';

    public function saveTopic(&$msgOptions, &$topicOptions, &$posterOptions)
    {
        // Include the needed smf-lib
        Smf::useSource(array(
            'Subs-Post',
            'Subs'
        ));

        // an existing id_message indicates an axisitng topic
        if (!empty($msgOptions['id']))
        {
            // modify exisiting post
            modifyPost($msgOptions, $topicOptions, $posterOptions);
        }
        else
        {
            // create the application post
            createPost($msgOptions, $topicOptions, $posterOptions);

            // get topic id
            return $this->getModel('Messages')->read(array(
                'field' => array(
                    'msg.id_msg AS id_message',
                    'msg.id_topic',
                    'msg.id_board',
                    'msg.subject'
                ),
                'filter' => 'msg.subject={string:subject} AND msg.id_member={int:id_member}',
                'param' => array(
                    'subject' => $msgOptions['subject'],
                    'id_member' => $posterOptions['id']
                )
            ));
        }
    }

    public function deleteTopic($topics, $decreasePostCount=true, $ignoreRecycling=false)
    {
        // include the needed smf-lib
        Smf::useSource('RemoveTopic');
        removeTopics($topics);
    }
}
?>

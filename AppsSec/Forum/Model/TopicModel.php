<?php
namespace Web\Framework\AppsSec\Forum\Model;

use Web\Framework\Lib\Model;
use Web\Framework\Lib\Smf;

class TopicModel extends Model
{
	public $tbl = 'topics';
	public $alias = 'topic';
	public $pk = 'id_topic';


	public function createTopic(&$msgOptions,&$topicOptions,&$posterOptions)
	{
		global $sourcedir;

		// include the needed smf-lib
		Smf::useSource(array(
			'Subs-Post',
			'Subs'
		));

		// an existing id_message indicates an axisitng topic
		if (isset($msgOptions['id']))
		{
			// modify exisiting post
			modifyPost($msgOptions, $topicOptions, $posterOptions);
		}
		else
		{
			// create the application post
			createPost($msgOptions,$topicOptions,$posterOptions);

			// get topic id
			$model = $this->app->getModel('Messages')
						->setField(array(
							'id_msg AS id_message',
							'id_topic',
							'id_board',
							'subject'
						))
						->setFilter('subject={string:subject} AND id_member={int:id_member}')
						->addParameter(array(
							'subject' => $msgOptions['subject'],
							'id_member'=> $posterOptions['id'],
						));

			return $model->read();
		}
	}

	public function deleteTopic($id_topic)
	{
		global $sourcedir;

		// include the needed smf-lib
		Smf::useSource('RemoveTopic');
		removeTopics($id_topic);
	}
}

?>
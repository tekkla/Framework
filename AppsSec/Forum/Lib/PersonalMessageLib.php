<?php
namespace Web\Framework\AppsSec\Forum\Lib;

use Web\Framework\Lib\Smf;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Personal message lib
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package Forum (AppSec)
 * @subpackage Lib
 * @license BSD
 * @copyright 2014 by author
 */
class PersonalMessageLib
{

	private $subject;

	private $msg;

	private $to = array();

	private $bcc = array();

	private $from;

	public function __construct($to, $subject, $msg)
	{
		$this->addTo($to);
		$this->setSubject($subject);
		$this->setMsg($msg);
	}

	private function addRecipient($reciep, $type = 'to')
	{
		if (is_array($reciep))
			$this->{$type} + $reciep;
		else
			$this->{$type}[] = $reciep;
	}

	/**
	 * Add one or more BCC reciepients.
	 * More then one needs to be an array of integers
	 * @param int|array $bcc
	 */
	public function addBcc($bcc)
	{
		$this->addRecipient($bcc, 'bcc');
	}

	/**
	 * Add one or more Reciepients.
	 * More then one needs to be an array of integers
	 * @param int|array $to
	 */
	public function addTo($to)
	{
		$this->addRecipient($to);
	}

	/**
	 * Set subject of message
	 * @param string $subject
	 */
	public function setSubject($subject)
	{
		$this->subject = $this->sanitizeUserInput($subject);
	}

	/**
	 * Set messagetext
	 * @param string $msg
	 */
	public function setMsg($msg)
	{
		$this->msg = $this->sanitizeUserInput($msg);
	}

	/**
	 * Set sender informations
	 * @param int $id
	 * @param string $name
	 * @param string $username
	 */
	public function setFrom($id, $name, $username)
	{
		$this->from = array(
			'id' => $id,
			'name' => $name,
			'username' => $username
		);
	}

	/**
	 * Sends message
	 * @return boolean
	 */
	public function send()
	{
		// include the needed smf-lib
		Smf::useSource('Subs-Post');

		sendpm(array(
			'to' => $this->to,
			'bcc' => $this->bcc
		), $this->subject, $this->msg, false, $this->from, 0);

		return true;
	}
}
?>

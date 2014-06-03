<?php

namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

// Used classes
use Web\Framework\Lib\Abstracts\ClassAbstract;
use Web\Framework\Lib\Errors\NoValidParameterError;
use Web\Framework\Lib\Errors\NeededPropertyNotSetError;

/**
 * Message class for flash messages.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class Message extends ClassAbstract
{
    /**
     * Predefined message types
     * @var array
     */
    private static $types = array(
        'primary',
        'success',
        'info',
        'warning',
        'danger',
        'default'
    );

    /**
     * Message disply type
     * @see self::$types
     * @var string
     */
    private $type;

    /**
     * Message cpntent
     * @var string
     */
    private $message;

    /**
     * Autmatic fadeout flag
     * @var bool
     */
    private $fadeout = true;

    /**
     * Factory pattern for message creation.
     * Creates a message object, sets the message,
     * stores the object in the message container and returns a reference to this object.
     * @param string $message
     * @return Message
     */
    public static function factory($message, $type = 'info', $fadeout = true)
    {
        if (!in_array($type, self::$types))
            Throw new NoValidParameterError($type, self::$types);

        $obj = new Message();
        $obj->setMessage($message);
        $obj->setType($type);
        $obj->setFadeout($fadeout);
        return $obj->add();
    }

    /**
     * Adds message object to session and returns a reference
     * to this message object
     * @throws Error
     */
    public function &add()
    {
        // Errorhandling on no set message text
        if (!isset($this->message) || empty($this->message))
            Throw new NeededPropertyNotSetError('Message::message');

            // Get current message counter
        $current_counter = $_SESSION['web']['message_counter'];

        // Assign this message to message session
        if (!isset($_SESSION['web']['messages']))
            $_SESSION['web']['messages'] = array();

        $_SESSION['web']['messages'][$current_counter] = $this;

        $_SESSION['web']['message_counter']++;

        // Return reference to the message
        return $_SESSION['web']['messages'][$current_counter];
    }

    /**
     * Creates "primary" message and returns reference to this messages.
     * @param string $message Message content
     * @param bool $fadeout Automatic fadeout. Set to false dto disable.
     * @return Message
     */
    public function primary($message, $fadeout = true)
    {
        $this->setType('primary');
        $this->setMessage($message);
        $this->setFadeout($fadeout);
        return $this->add();
    }

    /**
     * Creates "succcess" message and returns reference to this messages.
     * @param string $message Message content
     * @param bool $fadeout Automatic fadeout. Set to false dto disable.
     * @return Message
     */
    public function success($message, $fadeout = true)
    {
        $this->setType('success');
        $this->setMessage($message);
        $this->setFadeout($fadeout);
        return $this->add();
    }

    /**
     * Creates "info" message and returns reference to this messages.
     * @param string $message Message content
     * @param bool $fadeout Automatic fadeout. Set to false dto disable.
     * @return Message
     */
    public function info($message, $fadeout = true)
    {
        $this->setType('info');
        $this->setMessage($message);
        $this->setFadeout($fadeout);
        return $this->add();
    }

    /**
     * Creates "warning" message and returns reference to this messages.
     * @param string $message Message content
     * @param bool $fadeout Automatic fadeout. Set to false dto disable.
     * @return Message
     */
    public function warning($message, $fadeout = true)
    {
        $this->setType('warning');
        $this->setMessage($message);
        $this->setFadeout($fadeout);
        return $this->add();
    }

    /**
     * Creates "danger" message and returns reference to this messages.
     * @param string $message Message content
     * @param bool $fadeout Automatic fadeout. Set to false dto disable.
     * @return Message
     */
    public function danger($message, $fadeout = true)
    {
        $this->setType('danger');
        $this->setMessage($message);
        $this->setFadeout($fadeout);
        return $this->add();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        // Take care of not set messagge counter and set it to 0 if needed.
        if (!isset($_SESSION['web']['message_counter']))
            $_SESSION['web']['message_counter'] = 0;
    }

    /**
     * Sets message content
     * @param string $message
     * @return \Web\Framework\Lib\Message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Sets message type
     * @param string $type
     * @throws NoValidParameterError
     * @return \Web\Framework\Lib\Message
     */
    public function setType($type)
    {
        if (!in_array($type, self::$types))
            Throw new NoValidParameterError($type, self::$types);

        $this->type = $type;
        return $this;
    }

    /**
     * Switches fadeout on or off
     * @param bool $fadeout
     * @return \Web\Framework\Lib\Message
     */
    public function setFadeout($fadeout)
    {
        $this->fadeout = is_bool($fadeout) ? $fadeout : false;
        return $this;
    }

    public function build()
    {
        return '
		<div class="alert alert-' . $this->type . ' alert-dismissable' . ($this->fadeout ? ' web-fadeout' : '') . '">
		    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		    ' . $this->message . '
		</div>';
    }

    /**
     * Check for set messages
     */
    public static function checkMessages()
    {
        return isset($_SESSION['web']['messages']) && !empty($_SESSION['web']['messages']);
    }

    /**
     * Returns set messages and resets the the messagestorage.
     * If no message is set the method returns boolean false.
     */
    public static function getMessages()
    {
        $return = isset($_SESSION['web']['messages']) ? $_SESSION['web']['messages'] : false;

        if ($return)
            self::resetMessages();

        return $return;
    }

    /**
     * Resets messages in session
     */
    public static function resetMessages()
    {
        $_SESSION['web']['message_counter'] = 0;
        unset($_SESSION['web']['messages']);
    }
}
?>

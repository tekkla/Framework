<?php
namespace Web\Framework\Lib\Abstracts;

use Web\Framework\Lib\Txt;
use Web\Framework\Lib\User;
/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 *
 */
abstract class ErrorAbstract
{

    protected $code;

    protected $params;

    protected $fatal = false;

    protected $redirect = false;

    protected $user_message;

    protected $admin_message;

    /**
     * Constructor
     * @param string $message Message provided by Error object
     * @param int $code Errorcode
     * @param array $params Optional parameters
     */
    function __construct($message='', $code = 0, $params=array())
    {
        // On empty message the default error txt will be used
        if (!$message)
        {
            $message = Txt::get('web_error', 'Web');

            $this->admin_message = $message;
            $this->user_message = $message;
        }

        // None array messages are for admins. Users will get a default error.
        if ($message && ! is_array($message))
        {
            $this->admin_message = $message;
            $this->user_message = Txt::get('web_error', 'Web');
        }

        // Message as array means:
        // The first entry with text for admins
        // The second entry with text for normal users
        if (is_array($message))
        {
            // Set default error message if not set in message array
            if (!isset($message[1]))
                $message[1] = Txt::get('web_error', 'Web');

            $this->admin_message = $message[0];
            $this->user_message = $message[1];
        }

        // Store provided code and parameter
        $this->code = $code;
        $this->params = $params;
    }

    /**
     * Returns error message. Checks the user type and returns the message defined for him.
     * @return string
     */
    public function getMessage()
    {
        return User::isAdmin() ? $this->admin_message : $this->user_message;
    }

    /**
     * Returns error code
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Checks for set redirect
     * @return boolean
     */
    public function isRedirect()
    {
        return $this->redirect !== false;
    }

    /**
     * Returns redirect value
     * @return mixed
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Checks for set redirect
     * @return boolean
     */
    public function isFatal()
    {
        return $this->fatal !== false;
    }

    /**
     * Abstract method which every error handler MUST declare
     */
    abstract function process();
}
?>

<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    J!Code Framework
 * @package     J!Code Framework
 * @author      Jeroen Bleijenberg <jeroen@maxserv.nl>
 * 
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Jcode\Application\Model;

class Session extends \Jcode\Application\Model
{

    const MSG_TYPE_SUCCESS = 'success';

    const MSG_TYPE_WARNING = 'warning';

    const MSG_TYPE_ALERT = 'alert';

    const MSG_TYPE_INFO = 'info';

    const MSG_TYPE_SECONDARY = 'secondary';

    protected $_namespace = 'application';

    /**
     * Start sessions if not already done so.
     */
    protected function _construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!array_key_exists($this->_namespace, $_SESSION)) {
            $this->_setSession([]);
        }
    }

    /**
     * Set or get values to and from session
     *
     * @param $method
     * @param $args
     * @return bool|\Jcode\Object|mixed|void
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        $key = $this->underscore(substr($method, 3));
        $session = $this->_getSession();

        switch (substr($method, 0, 3)) {
            case 'set':
                $session[$key] = $args[0];

                $this->_setSession($session);

                return $this;

                break;
            case 'get':
                if (array_key_exists($key, $session)) {
                    $value = $session[$key];

                    /**
                     * Delete the item after getting it.
                     */
                    if (array_key_exists(0, $args) && $args[0] == true) {
                        unset($session[$key]);

                        $this->_setSession($session);
                    }

                    return $value;
                } else {
                    return false;
                }

                break;
            default:
                throw new \Exception(sprintf('Call to undefined method: %s', $method));
        }
    }

    /**
     * @return mixed
     */
    public function getSessionData()
    {
        return $this->_getSession();
    }

    /**
     * Add messages to session
     *
     * @param $message
     * @param string $type
     * @return $this
     */
    public function addMessage($message, $type = self::MSG_TYPE_INFO)
    {
        $session = $this->_getSession();

        if (!array_key_exists('messages', $session) || !is_array($session['messages'])) {
            $session['messages'] = [];
        }

        $messages = $session['messages'];

        array_push($messages, [$type, $message]);

        $session['messages'] = $messages;

        $this->_setSession($session);

        return $this;
    }

    /**
     * Get messages currently present in session
     *
     * @param bool $purge
     * @return array
     */
    public function getMessages($purge = true)
    {
        $session = $this->_getSession();

        if (!array_key_exists('messages', $session) || !is_array('messages', $session)) {
            $messages = [];
        } else {
            $messages = $session['messages'];
        }

        if ($purge) {
            $session['messages'] = [];
        }

        $this->_setSession($session);

        return $messages;
    }

    /**
     * Add warning message
     *
     * @param $message
     * @return Session
     */
    public function addWarning($message)
    {
        return $this->addMessage($message, self::MSG_TYPE_WARNING);
    }

    /**
     * Add success alert
     *
     * @param $message
     * @return Session
     */
    public function addSuccess($message)
    {
        return $this->addMessage($message, self::MSG_TYPE_SUCCESS);
    }

    /**
     * Add alert - alert
     *
     * @param $message
     * @return Session
     */
    public function addAlert($message)
    {
        return $this->addMessage($message, self::MSG_TYPE_ALERT);
    }

    /**
     * Add info alert
     *
     * @param $message
     * @return Session
     */
    public function addInfo($message)
    {
        return $this->addMessage($message, self::MSG_TYPE_INFO);
    }

    /**
     * Add secondary alert message
     *
     * @param $message
     * @return Session
     */
    public function addSecondary($message)
    {
        return $this->addMessage($message, self::MSG_TYPE_SECONDARY);
    }

    /**
     * Returns the session under this namespace
     *
     * @return mixed
     */
    protected function _getSession()
    {
        return $_SESSION[$this->_namespace];
    }

    /**
     * Write data back to session
     *
     * @param $data
     * @return mixed
     */
    protected function _setSession($data)
    {
        return $_SESSION[$this->_namespace] = $data;
    }
}
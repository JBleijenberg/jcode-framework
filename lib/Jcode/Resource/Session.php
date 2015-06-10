<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 3.0)
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-3.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category    docroot
 * @package     docroot
 * @author      Jeroen Bleijenberg <jeroen@maxserv.com>
 *
 * @copyright   Copyright (c) 2015 MaxServ (http://www.maxserv.com)
 * @license     http://opensource.org/licenses/GPL-3.0 General Public License (GPL 3.0)
 */
namespace Jcode\Resource;

class Session extends \Jcode\Object
{

	const MSG_TYPE_SUCCESS = 'success';

	const MSG_TYPE_WARNING = 'warning';

	const MSG_TYPE_ALERT = 'alert';

	const MSG_TYPE_INFO = 'info';

	protected $isSharedInstance = true;

	protected $namespace = 'application';

	/**
	 * @inject \Jcode\Registry
	 * @var \Jcode\Registry
	 */
	protected $registry;

	/**
	 * @inject \Jcode\ObjectManager
	 * @var \Jcode\ObjectManager
	 */
	protected $objectManager;

	/**
	 * Initialize sessions
	 */
	public function __construct()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		if (!array_key_exists($this->namespace, $_SESSION)) {
			$_SESSION[$this->namespace] = [];
		}

		return $this;
	}

	public function init()
	{
		$this->registerSessionNamespace();
	}

	public function __call($method, $args)
	{
		$key = substr($method, 3);

		$this->convertStringToDataKey($key);

		$session = $this->getSession();

		switch (substr($method, 0, 3)) {
			case 'set':
				$session[$key] = $args[0];

				$this->setSession($session);

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
						$this->setSession($session);
					}

					return $value;
				} else {
					return false;
				}
				break;
			default:
				throw new Exception(sprintf('Call to undefined method: %s', $method));
		}
	}

	/**
	 * Add message to Session object
	 *
	 * @param $msg
	 * @param string $type
	 * @return $this
	 */
	public function addMessage($msg, $type = self::MSG_TYPE_INFO)
	{
		$session = $this->getSession();

		if (!array_key_exists('messages', $session) || !is_array($session['messages'])) {
			$session['messages'] = [];
		}

		$msgObj = $this->objectManager->get('Jcode\Object');

		$msgObj->setType($type);
		$msgObj->setMessage($msg);
		array_push($session['messages'], $msgObj);

		$this->setSession($session);

		return $this;
	}

	/**
	 * Add success message
	 *
	 * @param $msg
	 * @return Session
	 */
	public function addSuccess($msg)
	{
		return $this->addMessage($msg, self::MSG_TYPE_SUCCESS);
	}

	/**
	 * Add warning message
	 *
	 * @param $msg
	 * @return Session
	 */
	public function addWarning($msg)
	{
		return $this->addMessage($msg, self::MSG_TYPE_WARNING);
	}

	/**
	 * Add info message
	 *
	 * @param $msg
	 * @return Session
	 */
	public function addInfo($msg)
	{
		return $this->addMessage($msg, self::MSG_TYPE_INFO);
	}

	/**
	 * Add alert
	 *
	 * @param $msg
	 * @return Session
	 */
	public function addAlert($msg)
	{
		return $this->addMessage($msg, self::MSG_TYPE_ALERT);
	}

	/**
	 * Get all messages for the current selected namespaces.
	 * By default the messages are cleared after they are fetched
	 *
	 * @param bool $purge
	 * @return array
	 */
	public function getMessages($purge = true)
	{
		$session = $this->getSession();

		if (!array_key_exists('messages', $session) || !is_array($session['messages'])) {
			$messages = [];
		} else {
			$messages = $session['messages'];
		}

		if ($purge === true) {
			$session['messages'] = [];

			$this->setSession($session);
		}

		return $messages;
	}

	public function getSessionData()
	{
		return $this->getSession();
	}

	/**
	 * Get $_SESSION variable of the current namespace
	 *
	 * @return mixed
	 */
	protected function getSession()
	{
		return $_SESSION[$this->namespace];
	}

	/**
	 * Set $_SESSION variable to the current namespace
	 *
	 * @param $params
	 * @return $this
	 */
	public function setSession($params)
	{
		$_SESSION[$this->namespace] = $params;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getRegisteredNamespaces()
	{
		return $this->registry->get('registered_session_namespaces', []);
	}

	protected function registerSessionNamespace()
	{
		$registeredNamespaces = $this->getRegisteredNamespaces();

		$registeredNamespaces[$this->namespace] = get_called_class();

		$this->registry->set('registered_session_namespaces', $registeredNamespaces);

		return $this;
	}
}
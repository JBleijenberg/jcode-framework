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
 * @category    J!Code: Framework
 * @package     J!Code: Framework
 * @author      Jeroen Bleijenberg <jeroen@maxserv.com>
 *
 * @copyright   Copyright (c) 2015 MaxServ (http://www.maxserv.com)
 * @license     http://opensource.org/licenses/GPL-3.0 General Public License (GPL 3.0)
 */
namespace Jcode\Router\Http;

use \Jcode\Application;
use \Exception;
use Jcode\Router\Front\Controller;

class Request
{

	protected $isSharedInstance = true;

	protected $eventId = 'router.request';

	protected $frontName = 'core';

	protected $controller = 'index';

	protected $action = 'index';

	/**
	 * @var \Jcode\Application\Config
	 * @inject \Jcode\Application\Config
	 */
	protected $config;

	/**
	 * @inject \Jcode\Router\Rewrite
	 * @var \Jcode\Router\Rewrite
	 */
	protected $rewrite;

	/**
	 * @inject \Jcode\ObjectManager
	 * @var \Jcode\ObjectManager
	 */
	protected $objectManager;

	protected $route;

	/**
	 * @var \Jcode\Object
	 */
	protected $module;

	/**
	 * Initialize request object
	 *
	 * @param \Jcode\Router\Http\Response $response
	 */
	public function buildHttpRequest(Response $response)
	{
		$route = trim($this->getServer('REQUEST_URI'), '/');

		if (empty($route)) {
			$route = $this->config->getDefaultRoute();
		}

		if ($rewrite = $this->rewrite->getRewrite($route)) {
			$route = $rewrite;
		}

		$params = null;

		if (strpos($route, '?')) {
			$route = current(explode('?', $route));
		}

		list($this->frontName, $this->controller, $this->action) = array_pad(explode('/', $route), 3, 'index');

		$this->dispatch($response);

		return;
	}

	public function dispatch(Response $response)
	{
		if ($module = $this->getConfig()->getModuleByFrontname($this->frontName)) {
			$this->module = $module;

			if ($router = $module->getRouter()) {
				if ($class = $router->getClass()) {
					$class = rtrim($class, '\\') . '\\' . ucfirst($this->controller);

					try {
						$controller = $this->objectManager->get($class, [$this, $response]);

						if ($controller instanceof Controller) {
							$action = $this->action . "Action";

							if (method_exists($controller, $action)) {
								$this->route = sprintf('%s/%s/%s', $this->frontName, $this->controller, $this->action);

								/* @var \Jcode\Object $get */
								$get = $this->objectManager->get('Jcode\Object');
								$get->importArray($_GET);

								/* @var \Jcode\Object $post */
								$post = $this->objectManager->get('Jcode\Object');
								$post->importArray($_POST);

								/* @var \Jcode\Object $files */
								$files = $this->objectManager->get('Jcode\Object');
								$files->importArray($_FILES);

								$controller->preDispatch($get, $post, $files);
								$controller->$action();
								$controller->postDispatch();
							} else {
								Application::log("Class is loaded, but action is not found");

								$this->noRoute();
							}
						} else {
							Application::log("{$class} not an instance of \\Jcode\\Router\\Front\\Controler");

							$this->noRoute();
						}
					} catch (Exception $e) {
						Application::logException($e);

						$this->noRoute();
					}
				} else {
					Application::log('Module router is defined, but no controller class is set');

					$this->noRoute();
				}
			} else {
				Application::log('Module is set, but no router is defined');

				$this->noRoute();
			}
		} else {
			$this->noRoute();
		}
	}

	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * Get a server variable
	 *
	 * @param null $key
	 *
	 * @return mixed
	 */
	public function getServer($key = null)
	{
		return ($key !== null) ? $_SERVER[$key] : $_SERVER;
	}

	/**
	 * @return \Jcode\Application\Config
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Return 404 response.
	 */
	public function noRoute()
	{
		$response = Application::env()->getResponse();

		$response->setHttpCode(404);
		$response->dispatch();
	}

	/**
	 * @return string
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * @param $controller
	 *
	 * @return $this
	 */
	public function setController($controller)
	{
		$this->controller = $controller;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @param $action
	 *
	 * @return $this
	 */
	public function setAction($action)
	{
		$this->action = $action;

		return $this;
	}

	public function getFrontName()
	{
		return $this->frontName;
	}

	/**
	 * @param $frontName
	 *
	 * @return $this
	 */
	public function setFrontName($frontName)
	{
		$this->frontName = $frontName;

		return $this;
	}

	/**
	 * Return currently used module
	 *
	 * @return \Jcode\Object
	 */
	public function getModule()
	{
		return $this->module;
	}
}
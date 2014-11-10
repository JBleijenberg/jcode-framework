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
namespace Jcode\Router\Http;

class Request
{

    /**
     * @var \Jcode\DependencyContainer
     */
    protected $_dc;

    /**
     * @var \Jcode\Log
     */
    protected $_log;

    protected $_frontName;

    protected $_module;

    protected $_controllerName;

    protected $_controllerInstance;

    protected $_actionName;

    /**
     * @var \Jcode\Application\Config
     */
    protected $_config;

    public function __construct(\Jcode\DependencyContainer $dc, \Jcode\Log $log)
    {
        $this->_dc = $dc;
        $this->_log = $log;
    }

    public function setConfig(\Jcode\Application\ConfigSingleton $config)
    {
        $this->_config = $config;
    }

    public function buildRequest()
    {
        $path = trim($this->getServer('REQUEST_URI'), '/');

        list($frontName, $controller, $action) = array_pad(explode('/', $path), 3, null);

        $this->_frontName = ($frontName != null) ? strtolower($frontName) : 'core';
        $this->_controllerName = ($controller != null) ? strtolower($controller) : 'index';
        $this->_actionName = ($action != null) ? strtolower($action) : 'index';
        $this->_module = $this->_config->getModuleByFrontName($this->_frontName);

        if (!$this->_module) {
            $this->_log->write(sprintf('Module not found by requested frontname: %s', $this->_frontName));

            return $this->_noRoute();
        }

        $baseClass = trim($this->_module->getController()->getClass(), '\\');
        $controllerClass = sprintf('%s\%s', $baseClass, sprintf('%sController', ucfirst($this->_controllerName)));

        $controller = $this->_dc->get($controllerClass);

        try {
            if ($controller instanceof \Jcode\Router\Controller) {
                $this->_controllerInstance = $controller;
            } else {
                $this->_log->write(sprintf('Controller not instance of \Jcode\Router\Controller: %s', $controllerClass));
                $this->_noRoute();
            }
        } catch (\Exception $e) {
            $this->_log->writeException($e);

            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @return \Jcode\Router\Controller
     */
    public function getControllerInstance()
    {
        return $this->_controllerInstance;
    }

    /**
     * Get $_SERVER variable
     *
     * @param null|string $var
     * @return bool|string
     */
    public function getServer($var = null)
    {
        if ($var === null) {
            return $_SERVER;
        } else {
            if (array_key_exists(strtoupper($var), $_SERVER)) {
                return $_SERVER[strtoupper($var)];
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getModuleCode()
    {
        return $this->_module->getModule()->getCode();
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->_controllerName;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->_actionName;
    }

    /**
     * No route has been found. Write 404 page
     */
    protected function _noRoute($code = 404)
    {
        throw new \Exception('Error while dispatching');
    }
}
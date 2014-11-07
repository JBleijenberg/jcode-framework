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
namespace Jcode\Router;

class Http
{

    /**
     * @var \Jcode\Router\Http\Request
     */
    protected $_request;

    /**
     * @var \Jcode\Router\Http\Response
     */
    protected $_response;

    /**
     * @var \Jcode\Event
     */
    protected $_eventHandler;

    /**
     * @param Http\Request $request
     * @param Http\Response $response
     */
    public function __construct(Http\Request $request, Http\Response $response, \Jcode\Event $eventHandler)
    {
        $this->_request = $request;
        $this->_response = $response;
        $this->_eventHandler = $eventHandler;
    }

    /**
     * Dispatch controller
     *
     * @param \Jcode\Application\ConfigSingleton $config
     * @return $this
     */
    public function dispatch(\Jcode\Application\ConfigSingleton $config)
    {
        $request = $this->_request;
        $response = $this->_response;

        $request->setConfig($config);
        $request->buildRequest();

        $controller = $request->getControllerInstance();
        $action = sprintf('%sAction', $request->getActionName());

        if (method_exists($controller, $action)) {
            $arr = ['request' => $request, 'response' => $response];

            //$this->_eventHandler->dispatchEvent('controller_init_before', $arr);

            $controller->setRequest($request);
            $controller->setResponse($response);
            $controller->setConfig($config);
            $controller->preDispatch();
            $controller->$action();
            $controller->postDispatch();

            //$this->_eventHandler->dispatchEvent('controller_init_after', $arr);
        } else {
            $controller->noRoute();
        }

        return $this;
    }
}
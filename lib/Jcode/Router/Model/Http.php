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
namespace Jcode\Router\Model;

class Http
{

    /**
     * @var \Jcode\Router\Model\Http\Request
     */
    protected $_request;

    /**
     * @var \Jcode\Router\Model\Http\Response
     */
    protected $_response;

    /**
     * @param Http\Request $request
     * @param Http\Response $response
     */
    public function __construct(Http\Request $request, Http\Response $response)
    {
        $this->_request = $request;
        $this->_response = $response;
    }

    /**
     * Dispatch controller
     *
     * @param \Jcode\Application\Model\Config $config
     * @return $this
     */
    public function dispatch(\Jcode\Application\Model\Config $config)
    {
        $request = $this->_request;
        $response = $this->_response;

        $request->setConfig($config);
        $request->buildRequest();

        $controller = $request->getControllerInstance();
        $action = sprintf('%sAction', $request->getActionName());

        if (method_exists($controller, $action)) {
            $controller->setRequest($request);
            $controller->setResponse($response);
            $controller->setConfig($config);
            $controller->preDispatch();
            $controller->$action();
            $controller->postDispatch();
        } else {
            $controller->noRoute();
        }

        return $this;
    }
}
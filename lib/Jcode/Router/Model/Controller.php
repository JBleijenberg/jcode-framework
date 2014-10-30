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

class Controller
{

    protected $layout;

    /**
     * @var \Jcode\Router\Model\Http\Request
     */
    protected $_request;

    /**
     * @var \Jcode\Router\Model\Http\Response
     */
    protected $_response;

    /**
     * @var \Jcode\Application\Model\Config
     */
    protected $_config;

    public function __construct(\Jcode\Layout\Block\Layout $layout, \Jcode\Translate\Model\Phrase $phrase)
    {
        $this->_layout = $layout;
        $this->_phrase = $phrase;
    }

    /**
     * @param Http\Request $request
     * @return $this
     */
    public function setRequest(\Jcode\Router\Model\Http\Request $request)
    {
        $this->_request = $request;

        return $this;
    }

    /**
     * @return Http\Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @param Http\Response $response
     * @return $this
     */
    public function setResponse(\Jcode\Router\Model\Http\Response $response)
    {
        $this->_response = $response;

        return $this;
    }

    /**
     * @param \Jcode\Application\Model\Config $config
     */
    public function setConfig(\Jcode\Application\Model\Config $config)
    {
        $this->_config = $config;
    }

    /**
     * @return \Jcode\Application\Model\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @return Http\Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @return \Jcode\Layout\Block\Layout
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    public function loadLayout($element = null, $moduleCode = null)
    {
        if ($element === null) {
            $element = sprintf('%s_%s_%s', $this->getRequest()->getModuleCode(),
                $this->getRequest()->getControllerName(), $this->getRequest()->getActionName());
        }

        if ($moduleCode !== null) {
            $module = $this->getConfig()->getModule($moduleCode);
        } else {
            $module = $this->getConfig()->getModule($this->getRequest()->getModuleCode());
        }

        debug($module);
    }

    /**
     * This method is fired after the request and response is set and before the action is called
     *
     * @return $this
     */
    public function preDispatch()
    {
        return $this;
    }

    /**
     * This method is fired after the controlleraction is called
     *
     * @return $this
     */
    public function postDispatch()
    {
        return $this;
    }

    /**
     * @param int $code
     */
    public function noRoute($code = 404)
    {
        die($code);
    }
}
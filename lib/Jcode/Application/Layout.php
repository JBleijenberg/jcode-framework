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
namespace Jcode\Application;

class Layout extends \Jcode\Object
{

    /**
     * @var \Jcode\Application\Config
     */
    protected $_config;

    /**
     * @var string
     */
    protected $_element;

    /**
     * @var \Flow\Loader
     */
    protected $_flow;

    protected $_module;


    public function setElement($element)
    {
        $this->_element = $element;
    }

    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @param \Flow\Loader $flow
     */
    public function setFlow(\Flow\Loader $flow)
    {
        $this->_flow = $flow;
    }

    public function getFlow()
    {
        return $this->_flow;
    }

    /**
     * @param \Jcode\Application\Config $config
     */
    public function setConfig(\Jcode\Application\Config $config)
    {
        $this->_config = $config;
    }

    public function getConfig()
    {
        return $this->_config;
    }

    public function setModule($module)
    {
        $this->_module = $module;
    }

    public function getModule()
    {
        return $this->_module;
    }

    public function render()
    {
        $layout = $this->getModule()->getDesign()->getData($this->getElement());

        try {
            $this->setData('application', $this->getConfig());

            $template = $this->getFlow()->load($layout->getTemplate());

            $template->display($this->getData());
        } catch(\Exception $e) {
            die($e->getMessage());
        }
    }
}
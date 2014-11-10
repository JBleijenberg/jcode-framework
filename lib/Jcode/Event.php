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
namespace Jcode;

class Event
{
    /**
     * @var \Jcode\Application\Config
     */
    protected $_config;

    /**
     * @var \Jcode\DependencyContainer
     */
    protected $_dc;

    /**
     * @param Application\Config|Application\ConfigSingleton $config
     * @param \Jcode\DependencyContainer $dc
     */
    public function __construct(\Jcode\Application\ConfigSingleton $config, \Jcode\DependencyContainer $dc)
    {
        $this->_config = $config;
        $this->_dc = $dc;
    }

    /**
     * Dispatch event.
     *
     * @param $name
     * @param \Jcode\Object $data
     * @return $this
     * @throws \Exception
     */
    public function dispatchEvent($name, \Jcode\Object $data)
    {
        $this->setEventData($data);

        foreach ($this->_config->getObservers($name) as $observer) {
            if (class_exists($observer->getClass())) {
                $class = $this->_dc->get($observer->getClass());

                if (method_exists($class, $observer->getMethod())) {
                    $func = $observer->getMethod();

                    $class->$func($this);
                } else {
                    throw new \Exception($this->translate('Call to undefined method: %s::%s()', $observer->getClass(), $observer->getMethod()));
                }
            } else {
                throw new \Exception($this->_helper->translate('Class not found: %s', $observer->getClass()));
            }
        }

        return $this;
    }

    /**
     * @return Application\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }
}
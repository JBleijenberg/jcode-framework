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

use Jcode\DependencyContainer;

class Config
{

    /**
     * @var \Jcode\Config
     */
    protected $_applicationConfig;

    /**
     * @var DependencyContainer
     */
    protected $_dc;

    /**
     * @var \Jcode\Log
     */
    protected $_log;

    /**
     * Registered active modules
     * @var array
     */
    private $_modules = [];

    /**
     * Registered module frontnames. Used for calling the correct module controller
     * @var array
     */
    private $_registeredFrontNames = [];

    /**
     * @var array
     */
    private $_registeredModuleNames = [];

    /**
     * @param \Jcode\Config $config
     * @param DependencyContainer $dc
     */
    public function __construct(\Jcode\Config $config, DependencyContainer $dc, \Jcode\Log $log)
    {
        $this->_applictaionClass = $config;
        $this->_dc = $dc;
        $this->_log = $log;
    }

    /**
     * Register all modules in the application/code/modules dir
     */
    public function initModules()
    {
        $configFiles = glob(BP . DS . 'application' . DS . '*' . DS . '*' .  DS . 'Config.xml');

        foreach ($configFiles as $config) {
            try {
                $xml = simplexml_load_file($config);

                if ($xml->module['active'] == 'true'){
                    $obj = $this->_dc->get('Jcode\Object');

                    foreach ($xml as $element => $val) {
                        foreach ((array)$val as $v) {
                            $att = $this->_dc->get('Jcode\Object');
                            $att->setData($v);

                            $obj->setData($element, $att);
                        }
                    }

                    $obj->setModulePath(dirname($config) . DS);
                    $obj->setViewPath(sprintf('%s%s', $obj->getModulePath(), 'Views' . DS));

                    $this->_modules[$obj->getModule()->getCode()] = $obj;
                    $this->_registeredModuleNames[$obj->getModule()->getName()] = &$this->_modules[$obj->getModule()->getCode()];

                    if (($frontName = $obj->getController()->getFrontname()) && ($className =$obj->getController()->getClass())) {
                        $this->_registeredFrontNames[$frontName] =  &$this->_modules[$obj->getModule()->getCode()];
                    }
                }
            } catch (\Exception $e) {
                $this->_log->write($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Try to get module by code
     *
     * @param string $code
     * @return bool|object
     */
    public function getModule($code)
    {
        if (array_key_exists($code, $this->_modules)) {
            return $this->_modules[$code];
        }

        return false;
    }

    /**
     * Try to get module by frontname
     *
     * @param $frontName
     * @return object|bool
     */
    public function getModuleByFrontName($frontName)
    {
        if (array_key_exists($frontName, $this->_registeredFrontNames)) {
            return $this->_registeredFrontNames[$frontName];
        }

        return false;
    }
}
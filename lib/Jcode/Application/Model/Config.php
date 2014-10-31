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

class Config extends \Jcode\Object
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
    public function __construct(DependencyContainer $dc, \Jcode\Log $log)
    {
        $this->_dc = $dc;
        $this->_log = $log;

        $this->getConfig();
    }

    public function config()
    {
        return $this->getConfig();
    }

    public function getConfig()
    {
        if (!$this->_applicationConfig) {
            $xml = simplexml_load_file(BP . DS . 'application' . DS . 'application.xml');

            $config = $this->_dc->get('Jcode\Object');

            foreach ($xml as $section => $content) {
                foreach ((array)$content as $c) {
                    $obj = $this->_dc->get('Jcode\Object');

                    $obj->setData($c);

                    $config->setData($section, $obj);
                }
            }

            $this->_applicationConfig = $config;
        }

        return $this->_applicationConfig;
    }

    public function getLayout()
    {
        return $this->getConfig()->getDesign()->getLayout();
    }

    /**
     * Register all modules in the application/code/modules dir
     */
    public function initModules()
    {
        $configFiles = glob(BP . DS . 'application' . DS . '*' . DS . '*' .  DS . 'config.xml');

        foreach ($configFiles as $config) {
            try {
                $xml = simplexml_load_file($config);

                if ($xml->module['active'] == 'true'){
                    $obj = $this->_dc->get('Jcode\Object');

                    foreach ($xml as $element => $val) {
                        if ($element == 'design') {
                            $design = $this->_dc->get('Jcode\Object');

                            if ($val->layout) {
                                foreach ($val->layout as $k => $v) {
                                    $design->setData((string)$v['path'], (string)$v['template']);
                                }

                            }

                            $obj->setDesign($design);
                        } else {
                            foreach ((array)$val as $v) {
                                $att = $this->_dc->get('Jcode\Object');
                                $att->setData($v);

                                $obj->setData($element, $att);
                            }
                        }
                    }

                    $obj->setModulePath(dirname($config) . DS);

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
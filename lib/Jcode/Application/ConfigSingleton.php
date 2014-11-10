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

use Jcode\DependencyContainer;

class ConfigSingleton extends \Jcode\Object
{

    const CACHE_KEY_MODULE_CONFIG = 'application_config::modules';

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
     * @param DependencyContainer $dc
     * @param \Jcode\Log $log
     * @internal param \Jcode\Config $config
     */
    public function __construct(\Jcode\Application\Helper $helper, \Jcode\DependencyContainer $dc, \Jcode\Log $log, $data = null)
    {
        parent::_construct($helper, $dc, $data);

        $this->_dc = $dc;
        $this->_log = $log;

        $this->getConfig();
    }

    public function getModules()
    {
        if (!parent::getModules()) {
            $this->_initModules();
        }

        return parent::getModules();
    }

    public function getConfig()
    {
        if (!$this->hasData()) {
            $config = json_decode(file_get_contents(BP . DS . 'application' . DS . 'application.json'), true);

            $this->setData($config['application']);
        }

        return $this;
    }

    public function getLayout()
    {
        return $this->getConfig()->getDesign()->getLayout();
    }

    /**
     * Register all modules in the application/code/modules dir
     */
    protected function _initModules()
    {
        if ($this->getData('modules')) {
            return $this;
        }

        $configFiles = glob(BP . DS . 'application' . DS . '*' . DS . '*' .  DS . 'module.json');

        $cache = $this->_dc->get('Jcode\Cache');

        if($cache->isActive() && ($modules = $cache->get(self::CACHE_KEY_MODULE_CONFIG))) {
            $this->setModules($modules);
            $this->setFrontNames($cache->get(self::CACHE_KEY_MODULE_CONFIG.'.frontnames'));

            return $this;
        } else {
            $modules = $this->_dc->get('Jcode\Object');

            foreach ($configFiles as $config) {
                $content = json_decode(file_get_contents($config), true);
                $module = $this->_dc->get('Jcode\Application\Module', $content['module']);
                $module->cleanup();

                $module->setModulePath(dirname($config));

                $modules->setData($module->getCode(), $module);
                $modules->cleanup();

                if (($frontname = $module->getController()->getFrontname()) && ($className = $module->getController()->getClass())) {
                    $frontNames = $this->getData('front_names', []);
                    $frontNames[$frontname] = $module;

                    $this->setFrontNames($frontNames);
                }
            }

            $this->setModules($modules);

            if ($cache->isActive()) {
                $cache->set(self::CACHE_KEY_MODULE_CONFIG, $modules);
                $cache->set(self::CACHE_KEY_MODULE_CONFIG . '.frontnames', $this->getData('front_names'));

                if ($cache->getResultCode() != \Memcached::RES_SUCCESS) {
                    $cache->delete(self::CACHE_KEY_MODULE_CONFIG);
                    $cache->delete(self::CACHE_KEY_MODULE_CONFIG . '.frontnames');
                }
            }

            $setup = $this->_dc->get('Jcode\Application\Model\Setup');
            $setup->run();
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
        if (($module = $this->getModules()->getData($code))) {
            return $module;
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
        $this->_initModules();

        $frontNames = $this->getFrontNames()->getData();

        if (array_key_exists($frontName, $frontNames)) {
            return $frontNames[$frontName];
        }

        return false;
    }

    /**
     * Find all observers that hook into the current event
     *
     * @param $handle
     * @return object
     * @throws \Exception
     */
    public function getObservers($handle) {
        $observers = $this->_dc->get('Jcode\Object');

        foreach ($this->getModules() as $module) {
            if (($moduleObservers = $module->getObservers()->getData($handle))) {
                $observers->addData($moduleObservers);
            }
        }

        return $observers;
    }

    protected function _attributesToArray($data)
    {
        if ($data instanceof \SimpleXMLElement) {
            $data = [$data];
        }

        $resultArray = [];

        foreach ($data as $element) {
            $obj = $this->_dc->get('Jcode\Object');

            foreach($element->attributes() as $name => $val) {
                $obj->setData((string)$name, (string)$val);
            }

            array_push($resultArray, $obj);
        }

        return $resultArray;
    }
}
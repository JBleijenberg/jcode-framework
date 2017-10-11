<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category    J!Code Framework
 * @package     J!Code Framework
 * @author      Jeroen Bleijenberg <jeroen@jcode.nl>
 *
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Jcode\Application;

use Jcode\Application;
use \Jcode\Cache\CacheInterface;
use \Jcode\DataObject;
use \Exception;

class Config extends DataObject
{

    /**
     * Wether to treat this class as a singleton or not
     * @var bool
     */
    protected $isSharedInstance = true;

    /**
     * @inject \Jcode\Event\Manager
     * @var \Jcode\Event\Manager
     */
    protected $eventManager;

    /**
     * @var \Jcode\Cache\CacheInterface
     */
    protected $cache;

    protected $eventId = 'jcode.application.configuration';

    /**
     * Load application configuration file into the application
     *
     * @return $this
     * @throws Exception
     */
    public function initApplicationConfiguration()
    {
        $applicationJson = BP . DS . 'application.json';

        if (!stream_resolve_include_path($applicationJson)) {
            throw new Exception('Missing application.json file');
        }

        $configuration = file_get_contents($applicationJson);
        $configuration = json_decode($configuration, true);

        if (is_array($configuration) && !empty($configuration)) {
            $this->importArray($configuration['application']);
        }

        date_default_timezone_set($this->getTimezone());

        $this->initCache();

        $this->eventManager->dispatchEvent($this->eventId . '.after.init', $this);

        if ($this->isCacheEnabled() && !$this->getCacheInstance()->exists('application.configuration')) {
            $this->getCacheInstance()->set('application.configuration', $this->getData());
        }

        return $this;
    }

    public function initCache()
    {
        if ($this->getCache() && $this->getCache()->getEnabled() == 1) {
            $cacheConfig = $this->getCache();
            /* @var \Jcode\Cache\CacheInterface $class */
            $class = Application::objectManager()->get($cacheConfig->getClass());

            $class->connect($cacheConfig);

            $this->setCacheInstance($class);
        }

        return $this;
    }

    public function isCacheEnabled()
    {
        return ($this->getCache() && $this->getCache()->getEnabled() == 1);
    }

    /**
     * @param \Jcode\Cache\CacheInterface $cache
     *
     * @return $this
     */
    public function setCacheInstance(CacheInterface $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @return \Jcode\Cache\CacheInterface
     */
    public function getCacheInstance()
    {
        return $this->cache;
    }

    /**
     * Load module configuration in to the application
     *
     * @return $this
     * @throws Exception
     */
    public function initModuleConfiguration()
    {
        $moduleJsons = glob(BP . DS . 'application' . DS . '*' . DS . '*' . DS . 'module.json');

        /* @var \Jcode\DataObject $urlRewrites */
        $urlRewrites = Application::objectManager()->get('\Jcode\DataObject');

        Application::register('module_collection', Application::objectManager()->get('\Jcode\DataObject\Collection'));
        Application::register('frontnames', Application::objectManager()->get('\Jcode\DataObject'));
        Application::register('url_rewrites', $urlRewrites);

        foreach ($moduleJsons as $moduleJson) {
            $cacheKey = 'moduleConfig:' . md5($moduleJson);

            $module = null;

            if ($this->isCacheEnabled()) {
                if ($this->getCacheInstance()->exists($cacheKey)) {
                    $module = Application::objectManager()->get('\Jcode\DataObject');
                    $module->importArray($this->getCacheInstance()->get($cacheKey));
                } else {
                    $module = $this->loadModuleConfiguration($moduleJson);

                    $this->getCacheInstance()->set($cacheKey, $module);
                }
            } else {
                $module = $this->loadModuleConfiguration($moduleJson);
            }

            if ($module instanceof DataObject && $module->hasData()) {
                Application::registry('module_collection')->addItem($module, $module->getIdentifier());

                if ($module->getRouter() && $module->getRouter()->getFrontname()) {
                    Application::registry('frontnames')
                        ->setData($module->getRouter()->getFrontname(), $module->getIdentifier());
                }

                $this->initUrlRewrites($module);
            }
        }

        return $this;
    }

    /**
     * Return parsed module configuration
     *
     * @param $moduleJson
     * @return \Jcode\DataObject
     */
    protected function loadModuleConfiguration($moduleJson)
    {
        $configuration = file_get_contents($moduleJson);
        $configuration = json_decode($configuration, true);

        $module = Application::objectManager()->get('\Jcode\DataObject');

        if (is_array($configuration) && !empty($configuration)) {
            /* @var \Jcode\DataObject $module */
            $module->importArray($configuration['module']);
            $module->setModulePath(dirname($moduleJson));
        }

        return $module;
    }

    /**
     * Add url rewrites to the system, which are defined in the module.json files
     *
     * @param \Jcode\DataObject $module
     * @return $this
     */
    protected function initUrlRewrites(DataObject $module)
    {
        /** @var \Jcode\Router\Rewrite $rewriteClass */
        $rewriteClass = Application::objectManager()->get('\Jcode\Router\Rewrite');

        if (($router = $module->getRouter()) && ($rewrites = $router->getRewrite())) {
            foreach ($rewrites as $source => $destination) {
                $rewriteClass->addRewrite($source, $destination);
            }
        }

        return $this;
    }

    /**
     * Get the base url of the application
     *
     * @param bool $secure
     * @return null
     */
    public function getBaseUrl($secure = false)
    {
        if (($secure == true && parent::getData('use_ssl') == true) || parent::getData('force_ssl') == true) {
            return parent::getData('secure_base_url');
        }

        return parent::getData('unsecure_base_url');
    }

    /**
     * Get the skin path of the active layout
     *
     * @param bool $secure
     * @return string
     */
    public function getSkinUrl($secure = false)
    {
        return $this->getBaseUrl($secure) . 'skin/' . $this->getLayout();
    }

    /**
     * Generate an internal url with the specified path
     *
     * @param $path
     * @param bool $secure
     * @return string
     */
    public function getUrl($path, $secure = false)
    {
        return $this->getBaseUrl($secure) . trim($path, '/');
    }

    /**
     * @param $frontName
     *
     * @return \Jcode\DataObject|null
     */
    public function getModuleByFrontname($frontName)
    {
        if (Application::registry('frontnames')->getData($frontName)) {
            /* @var \Jcode\Object $module */
            $module = Application::registry('module_collection')->getItemById($frontName);

            if ($module instanceof DataObject) {
                return $module;
            }
        }

        return null;
    }

    public function getModule($moduleName)
    {
        return Application::registry('module_collection')->getItemByColumnValue('name', $moduleName);
    }
}
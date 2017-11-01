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
use Symfony\Component\Finder\Finder;

/**
 * Class Config
 * @package Jcode\Application
 *
 */
class Config
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

    protected $cache;

    protected $eventId = 'jcode.application.configuration';

    protected $layout;

    protected $unsecure_base_url;

    protected $secure_base_url;

    protected $use_ssl;

    protected $force_ssl;

    protected $encryption_key;

    protected $title;

    protected $default_route;

    protected $database;

    protected $timezone;

    protected $session_duration;

    /**
     * @return DataObject
     */
    public function getCache(): DataObject
    {
        return $this->cache;
    }

    /**
     * @return mixed
     */
    public function getLayout() :String
    {
        return $this->layout;
    }

    /**
     * @return mixed
     */
    public function getUnsecureBaseUrl() :String
    {
        return $this->unsecure_base_url;
    }

    /**
     * @return mixed
     */
    public function getSecureBaseUrl() :String
    {
        return $this->secure_base_url;
    }

    /**
     * @return mixed
     */
    public function getUseSsl() :Bool
    {
        return $this->use_ssl;
    }

    /**
     * @return mixed
     */
    public function getForceSsl() :Bool
    {
        return $this->force_ssl;
    }

    /**
     * @return mixed
     */
    public function getEncryptionKey() :String
    {
        return $this->encryption_key;
    }

    /**
     * @return mixed
     */
    public function getTitle() :String
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getDefaultRoute() :String
    {
        return $this->default_route;
    }

    /**
     * @return mixed
     */
    public function getDatabase() :DataObject
    {
        return $this->database;
    }

    /**
     * @return mixed
     */
    public function getTimezone() :String
    {
        return $this->timezone;
    }

    /**
     * @return mixed
     */
    public function getSessionDuration() :Int
    {
        return $this->session_duration;
    }

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
            foreach ($configuration['application'] as $key => $value) {
                if (is_array($value)) {
                    $value = Application::getClass('\Jcode\DataObject')->importArray($value);
                }
                $this->$key = $value;
            }
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
            $class = Application::getClass($cacheConfig->getClass());

            $class->connect($cacheConfig);

            $this->setCacheInstance($class);
        }

        return $this;
    }

    /**
     * Create data key for use with setData(), addData() or __call
     * @param $key
     * @param string $method
     * @return String|void
     */
    public static function convertStringToMethod($key, $method = 'set') :?String
    {
        $parts = explode('_', $key);
        $parts = array_map('ucfirst', $parts);

        array_unshift($parts, $method);

        return implode('', $parts);
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
        $finder = new Finder();

        $finder
            ->files()
            ->ignoreUnreadableDirs()
            ->followLinks()
            ->name('module.json')
            ->depth('> 2')
            ->in(BP);

        /* @var \Jcode\DataObject $urlRewrites */
        $urlRewrites = Application::getClass('\Jcode\DataObject');

        Application::register('module_collection', Application::getClass('\Jcode\DataObject\Collection'));
        Application::register('frontnames', Application::getClass('\Jcode\DataObject'));
        Application::register('url_rewrites', $urlRewrites);

        foreach ($finder as $moduleJson) {
            $cacheKey = 'moduleConfig:' . md5($moduleJson->getPathname());

            $module = null;

            if ($this->isCacheEnabled()) {
                if ($this->getCacheInstance()->exists($cacheKey)) {
                    $module = Application::getClass('\Jcode\Application\Module');
                    $module->importArray($this->getCacheInstance()->get($cacheKey));
                } else {
                    $module = $this->loadModuleConfiguration($moduleJson->getPathname());

                    $this->getCacheInstance()->set($cacheKey, $module);
                }
            } else {
                $module = $this->loadModuleConfiguration($moduleJson->getPathname());
            }

            if ($module instanceof Module) {
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
     * @return Module
     */
    protected function loadModuleConfiguration($moduleJson)
    {
        $configuration = file_get_contents($moduleJson);
        $configuration = json_decode($configuration, true);

        /** @var Module $module */
        $module = Application::getClass('\Jcode\Application\Module');

        if (is_array($configuration) && !empty($configuration)) {
            foreach ($configuration['module'] as $key => $value) {
                $method = self::convertStringToMethod($key);

                $module->$method($value);
            }

            $module->setModulePath(dirname($moduleJson));
        }

        return $module;
    }

    /**
     * Add url rewrites to the system, which are defined in the module.json files
     *
     * @param Module|DataObject $module
     * @return $this
     */
    protected function initUrlRewrites(Module $module)
    {
        /** @var \Jcode\Router\Rewrite $rewriteClass */
        $rewriteClass = Application::getClass('\Jcode\Router\Rewrite');

        if (($router = $module->getRouter()) && ($rewrites = $router->getRewrite())) {
            foreach ($rewrites->getRewrites() as $source => $destination) {
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
        if (($secure == true && $this->getUseSsl() == true) || $this->getForceSsl() == true) {
            return $this->getSecureBaseUrl();
        }

        return $this->getUnsecureBaseUrl();
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
     * @return Module|null
     */
    public function getModuleByFrontname($frontName) :?Module
    {
        if (Application::registry('frontnames')->getData($frontName)) {
            /* @var \Jcode\Object $module */
            $module = Application::registry('module_collection')->getItemById($frontName);

            if ($module instanceof Module) {
                return $module;
            }
        }

        return null;
    }

    public function getModule($moduleName) :?Module
    {
        /** @var Module $module */
        foreach (Application::registry('module_collection') as $module) {
            if ($module->getName() == $moduleName) {
                return $module;
            }
        }
    }
}
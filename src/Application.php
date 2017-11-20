<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 3.0)
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-3.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category    J!Code: Framework
 * @package     J!Code: Framework
 * @author      Jeroen Bleijenberg <jeroen@jcode.nl>
 *
 * @copyright   Copyright (c) 2017 J!Code (http://www.jcode.nl)
 * @license     http://opensource.org/licenses/GPL-3.0 General Public License (GPL 3.0)
 */
namespace Jcode;

use \Exception;
use Jcode\Application\Environment;
use Jcode\Db\Resource;
use Jcode\Event\Manager;

final class Application
{

    protected $isSharedInstance = true;

    protected $eventId = 'application';

    /**
     * @var \Jcode\Application\Environment
     */
    protected static $environment;

    /**
     * @var \Jcode\ObjectManager
     */
    protected static $objectManager;

    /**
     * @var \Jcode\Registry
     */
    protected static $registry;

    /**
     * @var Manager
     */
    protected static $eventManager;

    protected static $isDeveloperMode = false;

    protected static $showTemplateHints = false;

    protected static $logMysqlQueries = false;

    public static function isDeveloperMode($bool = true)
    {
        self::$isDeveloperMode = $bool;
    }

    public static function showTemplateHints($bool = null)
    {
        if ($bool !== null) {
            self::$showTemplateHints = $bool;
        }

        return self::$showTemplateHints;
    }

    public static function logMysqlQueries($bool = null)
    {
        if ($bool !== null) {
            self::$logMysqlQueries = $bool;
        }

        return self::$logMysqlQueries;
    }

    public static function prepare()
    {
        self::$objectManager = new ObjectManager;
        self::$eventManager = new Manager();
        self::$registry = self::objectManager()->get('Jcode\Registry');
        self::$environment = self::$objectManager->get('Jcode\Application\Environment');

        self::$environment->configure();
        self::$environment->setup();

        self::dispatchEvent('application.init.after', self::objectManager()->get('\Jcode\DataObject'));
        self::dispatchEvent('application.dispatch.before', self::objectManager()->get('\Jcode\DataObject'));
    }

    /**
     * Initialize application and dispatch it
     */
    public static function run()
    {
        if (!self::$environment) {
            try {
                self::prepare();

                if (php_sapi_name() != 'cli') {
                    self::$environment->dispatch();
                }
            } catch (Exception $e) {
                self::logException($e);
            }
        }
    }

    public static function dispatchEvent($eventID, $eventObject)
    {
        self::$eventManager->dispatchEvent($eventID, $eventObject);
    }

    /**
     * @return ObjectManager
     */
    protected static function objectManager()
    {
        return self::$objectManager;
    }

    public static function getClass($name, array $args = [])
    {
        return self::objectManager()->get($name, $args);
    }

    /**
     * @param $name
     * @param array $args
     * @return \Jcode\Db\Resource
     */
    public static function getResourceClass($name, array $args = [])
    {
        return self::getClass($name, $args)->getResource();
    }

    public static function getModule($name)
    {
        return self::$environment->getConfig()->getModule($name);
    }

    /**
     * Retrieve initialized application environment.
     *
     * @return \Jcode\Application\Environment
     */
    public static function env()
    {
        if (!self::$environment) {
            self::$objectManager = new ObjectManager;
            self::$environment = self::$objectManager->get('Jcode\Application\Environment');
        }

        return self::$environment;
    }

    public static function getConfig($value = null)
    {
        return self::env()->getConfig($value);
    }

    /**
     * Log exception to file
     *
     * @param \Exception $e
     */
    public static function logException(Exception $e)
    {
        $logger = new Log;
        $logger->writeException($e);

        if (self::$isDeveloperMode) {
            echo "<pre>\r\n";

            echo $e->getMessage() . "\r\n\n";

            foreach ($e->getTrace() as $trace) {
                echo $trace['file'] . "(" . $trace['line'] . "): " . $trace['function'] . "()\r\n";
            }
            echo "</pre>";
        }
    }

    public static function log($message, $level = 3, $file = 'jcode.log')
    {
        if (self::$isDeveloperMode) {
            debug($message);
        }

        $logger = new Log;

        $logger->setLogfile($file);
        $logger->setLevel($level);
        $logger->setMessage($message);

        $logger->write();
    }

    /**
     * Return baseurl's
     *
     * @param string $type
     * @param bool $secure
     *
     * @return \Jcode\Application\Config|string
     */
    public static function getBaseUrl($type = Environment::URL_TYPE_DEFAULT, $secure = true)
    {
        $layoutName = self::env()->getConfig('layout');

        if ($secure === true || self::env()->getConfig('force_ssl') == true) {
            $baseUrl = self::env()->getConfig('secure_base_url');
        } else {
            $baseUrl = self::env()->getConfig('unsecure_base_url');
        }
        switch ($type) {
            case Environment::URL_TYPE_DEFAULT:
                $url = $baseUrl;

                break;
            case Environment::URL_TYPE_CSS:
                $layout = self::getConfig('layout');
                $url = $baseUrl . "assets/{$layout}/css/";

                break;
            case Environment::URL_TYPE_JS:
                $layout = self::getConfig('layout');
                $url = $baseUrl . "assets/{$layout}/js/";

                break;
            default:
                $url = $baseUrl;
        }

        return $url;
    }

    /**
     * Build a valid url
     *
     * @param $location
     * @param array $options
     * @return string
     */
    public static function getUrl($location, array $options = [])
    {
        if (strpos($location, '://') === false) {
            $location = trim($location, '/');
            $isSecure = (array_key_exists('secure', $options) && $options['secure'] === true) ? true : false;

            $location = trim(self::getBaseUrl(Environment::URL_TYPE_DEFAULT, $isSecure), '/') . '/' . $location;
        }

        if (array_key_exists('params', $options)) {
            $params = '/';

            foreach ($options['params'] as $key => $value) {
                $params .= "{$key}/{$value}/";
            }

            $location = $location . $params;
        }

        return $location;
    }

    /**
     * Add value to registry
     *
     * @param $key
     * @param $value
     * @param bool $grace
     * @throws Exception
     */
    public static function register($key, $value, $grace = true)
    {
        self::$registry->set($key, $value, $grace);
    }

    /**
     * Get value from register. If key is not present, return $default
     *
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    public static function registry($key, $default = null)
    {
        return self::$registry->get($key, $default);
    }
}
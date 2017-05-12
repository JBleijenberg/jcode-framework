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

    protected static $isDeveloperMode = false;

    public static function isDeveloperMode($bool = true)
    {
        self::$isDeveloperMode = $bool;
    }

    /**
     * Initialize application and dispatch it
     */
    public static function run()
    {
        if (!self::$environment) {
            self::$objectManager = new ObjectManager;
            self::$registry = self::objectManager()->get('Jcode\Registry');
            self::$environment = self::$objectManager->get('Jcode\Application\Environment');

            try {
                self::$environment->configure();
                self::$environment->setup();
                self::$environment->dispatch();
            } catch (Exception $e) {
                self::logException($e);
            }
        }
    }

    /**
     * @return ObjectManager
     */
    public static function objectManager()
    {
        return self::$objectManager;
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
     * Return layout element
     *
     * @param $element
     *
     * @return mixed
     */
    public static function getLayout($element)
    {
        return self::env()->getLayout($element);
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
        $layoutName = self::env()->getConfig('layout/name');

        if ($secure === true) {
            $baseUrl = self::env()->getConfig('secure_base_url');
        } else {
            $baseUrl = self::env()->getConfig('base_url');
        }
        switch ($type) {
            case Environment::URL_TYPE_DEFAULT:
                $url = $baseUrl;

                break;
            case Environment::URL_TYPE_CSS:
                $url = $baseUrl . '/design/' . $layoutName . '/css';

                break;
            case Environment::URL_TYPE_JS:
                $url = $baseUrl . '/js';

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
            $location = $location . '?' . http_build_query($options['params']);
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
     * @param null $default
     * @return bool
     */
    public static function registry($key, $default = null)
    {
        return self::$registry->get($key, $default);
    }
}
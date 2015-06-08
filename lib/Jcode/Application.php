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
 * @author      Jeroen Bleijenberg <jeroen@maxserv.com>
 *
 * @copyright   Copyright (c) 2015 MaxServ (http://www.maxserv.com)
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
			debug($e->getMessage(), true);
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
}
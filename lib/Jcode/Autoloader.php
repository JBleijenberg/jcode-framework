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
 * @author      Jeroen Bleijenberg <jeroen@maxserv.com>
 *
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * @param $className
 */
function appClassLoader($className)
{
	$className = ltrim($className, '\\');
	$fileName = BP . DS . 'application' . DS;

	if (($lastNsPos = strripos($className, '\\'))) {
		$namespace = substr($className, 0, $lastNsPos);
		$className = substr($className, $lastNsPos + 1);
		$fileName .= str_replace('\\', DS, $namespace) . DS;
	}

	$fileName .= str_replace('_', DS, $className) . '.php';

	if (stream_resolve_include_path($fileName) !== false) {
		require_once $fileName;
	}
}

/**
 * @param $className
 */
function libClassLoader($className)
{
	$className = ltrim($className, '\\');
	$fileName = BP . DS . 'lib' . DS;

	if (($lastNsPos = strripos($className, '\\'))) {
		$namespace = substr($className, 0, $lastNsPos);
		$className = substr($className, $lastNsPos + 1);
		$fileName .= str_replace('\\', DS, $namespace) . DS;
	}

	$fileName .= str_replace('_', DS, $className) . '.php';

	if (stream_resolve_include_path($fileName) !== false) {
		require_once $fileName;
	}
}

spl_autoload_register('appClassLoader');
spl_autoload_register('libClassLoader');
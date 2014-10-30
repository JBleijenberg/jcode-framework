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
 * @copyright   Copyright (c) 2014 MaxServ (http://www.maxserv.nl)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

function appClassLoader($className)
{
    $className = ltrim($className, '\\');
    $namespace = '';
    $fileName = BP . DS . 'application' . DS;

    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos+1);
        $fileName .= str_replace('\\', DS, $namespace) . DS;
    }

    $fileName .= str_replace('_', DS, $className) . '.php';

    if (stream_resolve_include_path($fileName) !== false) {
        require_once $fileName;
    }
}

function libClassLoader($className)
{
    $className = ltrim($className, '\\');
    $namespace = '';
    $fileName = BP . DS . 'lib' . DS;

    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos+1);
        $fileName .= str_replace('\\', DS, $namespace) . DS;
    }

    $fileName .= str_replace('_', DS, $className) . '.php';

    if (stream_resolve_include_path($fileName) !== false) {
        require_once $fileName;
    }
}

spl_autoload_register('appClassLoader');
spl_autoload_register('libClassLoader');
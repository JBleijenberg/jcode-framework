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

error_reporting(E_ALL);
ini_set('display_errors', true);

define('BP', dirname(realpath('../application.json')));
define('DS', DIRECTORY_SEPARATOR);

require_once BP . DS . 'lib' . DS . 'Jcode' . DS . 'Functions.php';
require_once BP . DS . 'lib' . DS . 'Jcode' . DS . 'Autoloader.php';

require_once BP . DS . 'lib' . DS . 'Jcode' . DS . 'Application.php';

\Jcode\Application::isDeveloperMode(true);
\Jcode\Application::run();
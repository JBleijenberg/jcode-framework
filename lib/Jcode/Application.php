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
namespace Jcode;

class Application
{

    /**
     * @var Router\Http
     */
    protected $_http;

    /**
     * @var DependencyContainer
     */
    protected $_dc;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var Log
     */
    protected $_log;

    /**
     * @var array
     */
    private $_modules = [];

    /**
     * @param DependencyContainer $dc
     * @param Router\Http $http
     * @param \Jcode\Application\Helper $helper
     * @param Application\ConfigSingleton $config
     * @param Log $log
     * @internal param $ Router\\Http $http
     */
    public function __construct(DependencyContainer $dc, Router\Http $http, \Jcode\Application\Helper $helper,
        Application\ConfigSingleton $config, Log $log)
    {
        $this->_http = $http;
        $this->_dc = $dc;
        $this->_helper = $helper;
        $this->_config = $config;
        $this->_log = $log;
    }

    public function translate()
    {
        return $this->_helper->translate(func_get_args());
    }

    public function run()
    {
        if (!$this->_http instanceof Router\Http) {
            throw new \Exception($this->_helper->translate('Invalid request object. Expecting instance of \Jcode\Router\Request\Http. %s Given instead', get_class($this->_http)));
        }

        try {
            $umask = umask(0);

            $this->_http->dispatch($this->_config);

            umask($umask);
        } catch (\Exception $e) {
            $this->_log->writeException($e);
        }
    }
}
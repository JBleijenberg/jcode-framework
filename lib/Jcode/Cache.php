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

class Cache
{
    /**
     * @var Cache\Engine\Memcached
     */
    protected $_engine;

    /**
     * @param Cache\Engine\Memcached $engine
     * @param Application\Config $config
     */
    public function __construct(\Jcode\Cache\Engine\Memcached $engine, \Jcode\Application\Config $config)
    {
        $this->_engine = $engine;
        $this->_engine->setCacheConfig($config->getCache());
        $this->_engine->init();

        return $this->_engine;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->_engine->get($key);
    }

    /**
     * @param $key
     * @param $value
     * @param int $expiration
     * @return mixed
     */
    public function set($key, $value, $expiration = 0)
    {
        return $this->_engine->set($key, $value, $expiration);
    }

    public function __call($method, $args)
    {
        return $this->_engine->$method();
    }
}
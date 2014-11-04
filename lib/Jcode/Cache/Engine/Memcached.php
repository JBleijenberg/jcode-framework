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
namespace Jcode\Cache\Engine;

class Memcached implements \Jcode\Cache\EngineInterface
{
    const DEFAULT_EXPIRATION_TIME = 86400;

    protected $_cacheConfig;

    protected $_memcached;

    public function init()
    {
        $memcached = new \Memcached;
        $memcached->setOption(\Memcached::OPT_COMPRESSION, true);
        $memcached->setOption(\Memcached::OPT_HASH, \Memcached::HASH_MURMUR);
        $memcached->addServer($this->getCacheConfig()->getHost(), $this->getCacheConfig()->getPort());

        $this->_memcached = $memcached;

        return $this->_memcached;
    }

    public function setCacheConfig($config)
    {
        $this->_cacheConfig = $config;
    }

    public function getCacheConfig()
    {
        return $this->_cacheConfig;
    }

    public function get($key)
    {
        return $this->_memcached->get($key);
    }

    public function set($key, $value, $expiration = self::DEFAULT_EXPIRATION_TIME)
    {
        return $this->_memcached->set($key, $value, $expiration);
    }

    public function __call($method, $args)
    {
        return $this->_memcached->$method();
    }
}
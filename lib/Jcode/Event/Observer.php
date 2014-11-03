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
namespace Jcode\Event;

class Observer extends \Jcode\Object
{

    protected $_eventData = [];

    /**
     * @var \Jcode\Application\Config
     */
    protected $_config;

    /**
     * Set data into object. Using setData will overwrite any data that already exists
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setEventData($key, $value = null)
    {
        if (is_array($key) && $value == null) {
            foreach ($key as $k => $v) {
                $this->setEventData($k, $v);
            }
        } else {
            if (!empty($key) && $value !== null) {
                $this->_data[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Get data from object. Return null if there is no data with the given key
     *
     * @param null|string $key
     * @param null $default
     * @return mixed
     */
    public function getEventData($key = null, $default = null)
    {
        if ($key === null) {
            return $this->_eventData;
        } else {
            if (array_key_exists($key, $this->_eventData)) {
                return $this->_eventData[$key];
            }
        }

        return $default;
    }

    /**
     * @param \Jcode\Application\Config $config
     * @return $this
     */
    public function setConfig(\Jcode\Application\Config $config)
    {
        $this->_config = $config;

        return $this;
    }
}
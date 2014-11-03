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

class Object implements \Iterator, \Countable
{

    protected $_data = [];

    protected $_origData = [];

    /**
     * @var Translate\Model\Phrase
     */
    protected $_phrase;

    /**
     * @var bool
     */
    protected $_hasChangedData = false;

    /**
     * @param Translate\Model\Phrase $phrase
     * @param null $data
     */
    public function __construct(Translate\Model\Phrase $phrase, $data = null)
    {
        $this->_phrase = $phrase;

        if ($data !== null) {
            if (!is_array($data)) {
                $data = [$data];
            }

            $this->setData($data);
        }

        return $this;
    }

    public function __call($method, $args)
    {
        $key = $this->underscore(substr($method, 3));

        switch (substr($method, 0, 3)) {
            case 'get':
                return $this->getData($key);

                break;
            case 'set':
                return $this->setData($key, $args[0]);

                break;
            default:
                return $this->getData($method);
        }

        return false;
    }

    /**
     * Remove data from object.
     *
     * @param null|string $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        if ($key === null) {
            $this->_data = [];
        } else {
            if (array_key_exists($key, $this->_data)) {
                unset($this->_data[$key]);
            }
        }

        return $this;
    }

    /**
     * Set data into object. Using setData will overwrite any data that already exists
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if (is_array($key) && $value == null) {
            foreach ($key as $k => $v) {
                $this->setData($k, $v);
            }
        } else {
            if (!empty($key) && $value !== null) {
                $this->_data[$key] = $value;

                if (!array_key_exists($key, $this->_origData)) {
                    $this->_origData[$key] = $value;
                    $this->_hasChangedData = true;
                } else {
                    if ($this->_origData[$key] !== $value) {
                        $this->_hasChangedData = true;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Practically the same as setData, only this function skips when there already is an entry with the given key.
     *
     * @param $key
     * @param null $value
     * @return $this
     */
    public function addData($key, $value = null)
    {
        if (is_array($key) && $value == null) {
            foreach ($key as $k => $v) {
                $this->addData($k, $v);
            }
        } else {
            if (!empty($key) && $value !== null && !array_key_exists($key, $this->_data)) {
                $this->setData($key, $value);
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
    public function getData($key = null, $default = null)
    {
        if ($key === null) {
            return $this->_data;
        } else {
            if (array_key_exists($key, $this->_data)) {
                return $this->_data[$key];
            }
        }

        return $default;
    }

    /**
     * Split given var on capitalized characters and prepend underscore
     *
     * @param string $key
     * @return string
     */
    public function underscore($key)
    {
        return strtolower(preg_replace('/(.)([A-Z])/','$1_$2', $key));
    }

    /**
     * Returns wether the object has changed data.
     *
     * @return bool
     */
    public function hasChangedData()
    {
        return $this->_hasChangedData;
    }

    /**
     * Check if the current instance of this object contains any data
     *
     * @return bool
     */
    public function hasData()
    {
        return !(empty($this->_data));
    }

    /**
     * Translator method
     *
     * @return string
     */
    public function translate()
    {
        return $this->_phrase->translate(func_get_args());
    }

    public function rewind()
    {
        reset($this->_data);
    }

    public function current()
    {
        return current($this->_data);
    }

    public function key()
    {
        return key($this->_data);
    }

    public function next()
    {
        return next($this->_data);
    }

    public function valid()
    {
        $key = $this->key();

        return ($key !== null && $key !== false);
    }

    public function count(){
        return count($this->_data);
    }
}
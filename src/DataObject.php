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
 * @author      Jeroen Bleijenberg <jeroen@jcode.nl>
 *
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Jcode;

use \Iterator;
use \Countable;

class DataObject implements Iterator, Countable
{

    /**
     * Register changes to the current object
     * @var bool
     */
    protected $hasChangedData = false;

    protected $eventId = 'jcode.dataobject';

    /**
     * Hold object data
     * @var array
     */
    protected $data = [];

    protected $origData = [];

    /**
     * @param array $array
     * @param bool $overwrite
     * @return $this
     * @throws \Exception
     */
    public function importArray(array $array, $overwrite = false)
    {
        foreach ($array as $key => $value) {
            if ($overwrite === false && array_key_exists($key, $this->data)) {
                continue;
            } else {
                if (is_array($value)) {
                    $child = new self();

                    $this->setData($key, $child->importArray($value));
                } else {
                    $this->setData($key, $value);
                }
            }
        }

        return $this;
    }

    public function importObject(DataObject $object)
    {
        foreach ($object as $key => $value) {
            $this->setData($key, $value);
        }

        return $this;
    }

    /**
     * Set data to this object. Using this data will always overwrite the current value.
     * Use addData() to only add and not overwrite
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function setData($key, $value)
    {
        if ((array_key_exists($key, $this->data)
                && $this->data[$key] != $value)
            || (!array_key_exists($key, $this->data))
        ) {
            $this->hasChangedData = true;
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Get data
     *
     * @param $key
     * @return null
     */
    public function getData($key = null)
    {
        if ($key !== null) {
            if (array_key_exists($key, $this->data)) {
                return $this->data[$key];
            } else {
                return false;
            }
        }

        return $this->data;
    }

    /**
     * Get origData
     *
     * @param null $key
     * @return array
     */
    public function getOrigData($key = null)
    {
        if ($key !== null && array_key_exists($key, $this->origData)) {
            return $this->origData[$key];
        }

        return $this->origData;
    }

    /**
     * Add data to this object. If the key already exists, nothing happens.
     * Use setData() to overwrite.
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function addData($key, $value)
    {
        if (!array_key_exists($key, $this->data)) {
            $this->hasChangedData = true;

            $this->setData($key, $value);
        }

        return $this;
    }

    /**
     * Remove element from data array
     *
     * @param $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        if ($key !== null && array_key_exists($key, $this->data)) {
            $this->hasChangedData = true;

            unset($this->data[$key]);
        } else {
            $this->data = [];
        }

        return $this;
    }

    public function unsetOrigData()
    {
        $this->origData = [];

        return $this;
    }

    /**
     * Check if this object contains data
     *
     * @return bool
     */
    public function hasData()
    {
        return !empty($this->data);
    }

    /**
     * Called when set.. or get.. is called
     *
     * @param $key
     * @param $value
     * @return DataObject|null
     */
    public function __call($key, $value)
    {
        $type = substr($key, 0, 3);
        $key = substr($key, 3);
        $value = current($value);

        $this->convertStringToDataKey($key);

        if ($type == 'set') {
            return $this->setData($key, $value);
        } else {
            if ($type == 'get' && array_key_exists($key, $this->data)) {
                return $this->getData($key);
            }
        }

        return null;
    }

    /**
     * @param null $bool
     *
     * @return bool
     */
    public function hasChangedData($bool = null)
    {
        if ($bool === null) {
            return $this->hasChangedData;
        } else {
            $this->hasChangedData = $bool;
        }
    }

    /**
     * Create data key for use with setData(), addData() or __call
     * @param $key
     */
    public function convertStringToDataKey(&$key)
    {
        $key = preg_replace('/(.)([A-Z])/', '$1_$2', $key);
        $key = strtolower($key);
    }

    public function copyToOrigData()
    {
        $this->origData = $this->data;

        return $this;
    }

    public function toArray()
    {
        $arr = [];

        foreach ($this->data as $key => $val) {
            if ($val instanceof $this) {
                $arr[$key] = $val->toArray();
            } else {
                $arr[$key] = $val;
            }
        }

        return $arr;
    }

    public function rewind()
    {
        reset($this->data);
    }

    public function current()
    {
        return current($this->data);
    }

    public function key()
    {
        return key($this->data);
    }

    public function next()
    {
        return next($this->data);
    }

    public function valid()
    {
        $key = $this->key();

        return ($key !== null && $key !== false);
    }

    public function count()
    {
        return count($this->data);
    }
}

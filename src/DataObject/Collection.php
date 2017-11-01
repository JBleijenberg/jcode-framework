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
namespace Jcode\DataObject;

use \Jcode\DataObject;
use \Exception;

class Collection extends DataObject
{

    protected $items = [];

    protected $eventId = 'jcode.dataobject.collection';

    /**
     * @param mixed $item
     * @param null $key
     * @param bool $grace
     * @return $this
     * @throws Exception
     */
    public function addItem($item, $key = null, $grace = true)
    {
        if ($key === null) {
            $this->items[] = $item;
        } else {
            if (!array_key_exists($key, $this->items) || $grace == false) {
                $this->items[$key] = $item;
            } else {
                throw new Exception('An item with the same key already exists');
            }
        }

        return $this;
    }

    public function getAllItems()
    {
        return $this->items;
    }

    public function rewind()
    {
        reset($this->items);
    }

    public function current()
    {
        return current($this->items);
    }

    public function key()
    {
        return key($this->items);
    }

    public function next()
    {
        return next($this->items);
    }

    public function valid()
    {
        $key = $this->key();

        return ($key !== null && $key !== false);
    }

    public function count()
    {
        return count($this->items);
    }

    /**
     * Get and return item by key
     * @param $key
     *
     * @return null
     */
    public function getItemById($key)
    {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        return null;
    }

    /**
     * Return item filtered by the given column value
     *
     * @param $column
     * @param $value
     *
     * @return null
     */
    public function getItemByColumnValue($column, $value)
    {
        foreach ($this->items as $item) {
            if ($item->getData($column) == $value) {
                return $item;
            }
        }

        return null;
    }

    public function toArray()
    {
        $arr = [];

        foreach ($this->items as $key => $val) {
            if ($val instanceof $this) {
                $arr[$key] = $val->toArray();
            } else {
                $arr[$key] = $val;
            }
        }

        return $arr;
    }

    public function getColumn($column) :array
    {
        $values = [];

        foreach ($this->items as $item) {
            $values[] = $item->getData($column);
        }

        return $values;
    }
}
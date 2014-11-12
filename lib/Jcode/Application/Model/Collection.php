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
namespace Jcode\Application\Model;

class Collection implements \Iterator, \Countable
{

    /**
     * @var array
     */
    protected $_items = [];

    /**
     * @var \Jcode\DependencyContainer
     */
    protected $_dc;

    protected $_modelClass = 'Jcode\Object';

    /**
     * @param \Jcode\DependencyContainer $dc
     */
    public function __construct(\Jcode\DependencyContainer $dc)
    {
        $this->_dc = $dc;
    }

    /**
     * Add item to collection
     *
     * @param array | \Jcode\Object $item
     * @return $this
     */
    public function addItem($item)
    {
        if ($item instanceof \Jcode\Object) {
            array_push($this->_items, $item);
        } else {
            if (is_array($item)) {
                $itemObject = $this->_dc->get($this->_modelClass, $item);

                array_push($this->_items, $itemObject);
            }
        }

        return $this;
    }

    /**
     * Remove item by key
     *
     * @param $key
     * @return $this
     */
    public function removeItemByKey($key)
    {
        if (array_key_exists($key, $this->_items)) {
            unset($this->_items[$key]);
        }

        return $this;
    }

    /**
     * Get collection items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Get first element of _items
     * @return bool|mixed
     */
    public function getFirstItem()
    {
        if (count($this->getItems()) > 0 ) {
            return $this->_items[0];
        }

        return false;
    }

    public function rewind()
    {
        reset($this->_items);
    }

    public function current()
    {
        return current($this->_items);
    }

    public function key()
    {
        return key($this->_items);
    }

    public function next()
    {
        return next($this->_items);
    }

    public function valid()
    {
        $key = $this->key();

        return ($key !== null && $key !== false);
    }

    public function count()
    {
        return count($this->_items);
    }
}
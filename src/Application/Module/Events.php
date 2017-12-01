<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 3.0)
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-3.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @author      Jeroen Bleijenberg
 *
 * @copyright   Copyright (c) 2017
 * @license     http://opensource.org/licenses/GPL-3.0 General Public License (GPL 3.0)
 */
namespace Jcode\Application\Module;

class Events implements \Iterator
{

    protected $events = [];

    public function addEvent(String $id, String $target)
    {
        $this->events[$id][] = $target;

        return $this;
    }

    public function getEvent($id)
    {
        if (array_key_exists($id, $this->events)) {
            return $this->events[$id];
        }

        return null;
    }

    public function getEvents() :array
    {
        return $this->events;
    }

    public function rewind()
    {
        reset($this->events);
    }

    public function current()
    {
        return current($this->events);
    }

    public function key()
    {
        return key($this->events);
    }

    public function next()
    {
        return next($this->events);
    }

    public function valid()
    {
        $key = $this->key();

        return ($key !== null && $key !== false);
    }

    public function count()
    {
        return count($this->events);
    }
}
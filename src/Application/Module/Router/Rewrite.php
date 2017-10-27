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
namespace Jcode\Application\Module\Router;

class Rewrite implements \Iterator
{

    protected $rewrites = [];

    public function addRewrite(String $source, String $destination)
    {
        $this->rewrites[$source] = $destination;

        return $this;
    }

    public function getRewrite($source) :?String
    {
        if (array_key_exists($source, $this->rewrites)) {
            return $this->rewrites[$source];
        }

        return null;
    }

    public function getRewrites() :array
    {
        return $this->rewrites;
    }

    public function rewind()
    {
        reset($this->rewrites);
    }

    public function current()
    {
        return current($this->rewrites);
    }

    public function key()
    {
        return key($this->rewrites);
    }

    public function next()
    {
        return next($this->rewrites);
    }

    public function valid()
    {
        $key = $this->key();

        return ($key !== null && $key !== false);
    }

    public function count()
    {
        return count($this->rewrites);
    }
}
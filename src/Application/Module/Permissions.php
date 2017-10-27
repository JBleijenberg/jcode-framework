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

/**
 * Class Permissions
 * @package Jcode\Application\Module
 */
class Permissions implements \Iterator
{

    /**
     * @var array
     */
    protected $permissions = [];

    /**
     * @param String $path
     * @param String $description
     * @return $this
     */
    public function addPermission(String $path, String $description)
    {
        $this->permissions[$path] = $description;

        return $this;
    }

    /**
     * @return array
     */
    public function getPermissions() :array
    {
        return $this->permissions;
    }

    /**
     *
     */
    public function rewind()
    {
        reset($this->permissions);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->permissions);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return key($this->permissions);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return next($this->permissions);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $key = $this->key();

        return ($key !== null && $key !== false);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->permissions);
    }
}
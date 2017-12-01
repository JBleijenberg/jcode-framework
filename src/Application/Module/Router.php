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

use Jcode\Application\Module\Router\Rewrite;

class Router
{

    /**
     * @var
     */
    protected $class;

    /**
     * @var
     */
    protected $frontname;

    protected $rewrite;

    /**
     * @return mixed
     */
    public function getClass() :String
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     * @return $this
     */
    public function setClass(String $class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFrontname() :String
    {
        return $this->frontname;
    }

    /**
     * @param mixed $frontname
     * @return $this
     */
    public function setFrontname(String $frontname)
    {
        $this->frontname = $frontname;

        return $this;
    }

    public function setRewrite(Rewrite $rewrite)
    {
        $this->rewrite = $rewrite;

        return $this;
    }

    public function getRewrite()
    {
        return $this->rewrite;
    }

}
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
namespace Jcode\Application;

use Jcode\Application;
use Jcode\Application\Module\Router;
use Jcode\DataObject;

class Module
{

    public $name;

    private $version;

    private $active;

    private $identifier;

    private $router;

    private $events;

    private $permissions;

    private $modulePath;

    public function setName(String $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName() :String
    {
        return $this->name;
    }

    public function setVersion(String $version)
    {
        $this->version = $version;

        return $this;
    }

    public function getVersion() :String
    {
        return $this->version;
    }

    public function setActive(Bool $active)
    {
        $this->active = $active;

        return $this;
    }

    public function getActive() :Bool
    {
        return $this->active;
    }

    public function setIdentifier(String $identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier() :String
    {
        return $this->identifier;
    }

    public function setRouter(array $router)
    {
        $routerObj = Application::getClass('\Jcode\Application\Module\Router');

        $routerObj->setClass($router['class']);
        $routerObj->setFrontname($router['frontname']);

        if (isset($router['rewrite'])) {
            $rewrite = Application::getClass('\Jcode\Application\Module\Router\Rewrite');

            foreach ($router['rewrite'] as $source => $destination) {
                $rewrite->addRewrite($source, $destination);
            }

            $routerObj->setRewrite($rewrite);
        }

        $this->router = $routerObj;

        return $this;
    }

    public function getRouter() :?Router
    {
        return $this->router;
    }

    public function setEvents(array $events)
    {
        /** @var Application\Module\Events $eventsObj */
        $eventsObj = Application::getClass('\Jcode\Application\Module\Events');

        foreach ($events as $id => $targets) {
            foreach ($targets as $target) {
                $eventsObj->addEvent($id, $target);
            }
        }

        $this->events = $eventsObj;

        return $this;
    }

    public function getEvents() :?Application\Module\Events
    {
        return $this->events;
    }

    public function setPermissions(array $permissions)
    {
        /** @var Application\Module\Permissions $permissionObj */
        $permissionObj = Application::getClass('\Jcode\Application\Module\Permissions');

        foreach ($permissions as $path => $description) {
            $permissionObj->addPermission($path, $description);
        }

        $this->permissions = $permissionObj;

        return $this;
    }

    public function getPermissions() :?Application\Module\Permissions
    {
        return $this->permissions;
    }

    public function setModulePath(String $path)
    {
        $this->modulePath = $path;

        return $this;
    }

    public function getModulePath() :String
    {
        return $this->modulePath;
    }

}
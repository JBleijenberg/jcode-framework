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
 * @author      Jeroen Bleijenberg <jeroen@maxserv.com>
 *
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Jcode;

use \ReflectionClass;
use \Exception;
use \Jcode\Application;

class ObjectManager
{
    /**
     * Hold shared instances of objects.
     *
     * @var array
     */
    protected $sharedInstances = [];

    public function __construct()
    {
        $this->sharedInstances['\Jcode\ObjectManager'] = $this;
    }

    /**
     * Try to return an instance of the requested class
     *
     * @param $class
     * @param array $args
     * @return object
     * @throws Exception
     */
    public function get($class, array $args = [])
    {
        if (!class_exists($class)) {
            throw new Exception("Class {$class} does not exists");
        }

        if (array_key_exists($class, $this->sharedInstances)) {
            return $this->sharedInstances[$class];
        }

        $reflectionClass = new ReflectionClass($class);

        $instance = $reflectionClass->newInstanceArgs($args);

        $eventId = false;

        foreach ($reflectionClass->getProperties() as $property) {
            /* @var \ReflectionProperty $property */
            preg_match('/@inject (.*)/', $property->getDocComment(), $inject);

            if (is_array($inject) && array_key_exists(1, $inject)) {
                if (get_class($instance) != $inject[1]) {
                    $property->setAccessible(true);
                    $property->setValue($instance, $this->get($inject[1]));
                }
            }

            if ($property->getName() == 'isSharedInstance' && $property->isProtected()) {
                $property->setAccessible(true);

                if ($property->getValue($instance) === true) {
                    $this->sharedInstances[$class] = $instance;
                }
            }

            if ($property->getName() == 'eventId' && $property->isProtected()) {
                $eventId = true;
            }
        }

        if (method_exists($instance, 'init')) {
            $instance->init();
        }

        return $instance;
    }
}

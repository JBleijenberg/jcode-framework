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

class DependencyContainer
{
    private $_instances = [];

    /**
     * Try to load the given class name and inject the dependencies that are asked for in it's constructor
     *
     * @param $className
     * @param null $args
     * @return object
     * @throws \Exception
     */
    public function get($className, $args = null)
    {
        if (!class_exists($className)) {
            throw new \Exception(sprintf('Dependency Container: Missing class %s', $className));
        }

        if (substr($className, -9) == 'Singleton' && array_key_exists($className, $this->_instances)) {
            return $this->_instances[$className];
        }

        if (!is_array($args)) {
            $args = [$args];
        }

        $reflection = new \ReflectionClass($className);

        $injections = [];

        if ($reflection->getConstructor()) {
            foreach ($reflection->getConstructor()->getParameters() as $param) {
                if ($param->getClass()) {
                    $injectClassName = $param->getClass()->name;

                    if (is_string($injectClassName)) {
                        if ($injectClassName == get_class($this)) {
                            array_push($injections, $this);
                        } else {
                            array_push($injections, $this->get($injectClassName));
                        }
                    }
                }
            }
        }

        array_push($injections, $args);

        if (substr($className, -9) == 'Singleton') {
                if ($reflection->getConstructor()) {
                    $this->_instances[$className] = $reflection->newInstanceArgs($injections);
                } else {
                    $this->_instances[$className] = $reflection->newInstance();
                }
            return $this->_instances[$className];
        } else {
            if ($reflection->getConstructor()) {
                return $reflection->newInstanceArgs($injections);
            } else {
                return $reflection->newInstance();
            }
        }
    }
}
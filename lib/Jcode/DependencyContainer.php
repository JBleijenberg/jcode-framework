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
    protected $_sharedInstances = [];

    public function __construct()
    {
        $this->_sharedInstances['Jcode\DependencyContainer'] = $this;
    }

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

        if (array_key_exists($className, $this->_sharedInstances)) {
            return $this->_sharedInstances[$className];
        }

        if (!is_array($args) && $args !== null) {
            $args = [$args];
        }

        $reflection = new \ReflectionClass($className);

        $injections = [];

        if ($reflection->getConstructor()) {
            foreach ($reflection->getConstructor()->getParameters() as $param) {
                if ($param->getClass()) {
                    $injectClassName = $param->getClass()->name;

                    if (is_string($injectClassName)) {
                        array_push($injections, $this->get($injectClassName));
                    }
                }
            }
        }

        if (!empty($args)) {
            array_push($injections, $args);
        }

        if (empty($injections)) {
            $injections = null;
        }

        if ($injections !== null) {
            $instance = $reflection->newInstanceArgs($injections);
        } else {
            $instance = $reflection->newInstance();
        }

        if (substr($className, -9) == 'Singleton') {
            $this->_sharedInstances[$className] = $instance;
        }

        return $instance;
    }
}
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
namespace Jcode;

use \Exception;

class Registry
{

    protected $eventId = 'jcode.registry';

    protected $isSharedInstance = true;

    protected $values = [];

    /**
     * Try to retrieve a value from the registry based on the given key
     *
     * @param $key
     * @param mixed $default
     * @return bool
     */
    public function get($key, $default = false)
    {
        if (array_key_exists($key, $this->values)) {
            return $this->values[$key];
        }

        return $default;
    }

    /**
     * Check if a value can (may) be added to the registry. If so, add it
     *
     * @param $key
     * @param $value
     * @param bool $grace
     * @throws Exception
     */
    public function set($key, $value, $grace = true)
    {
        if (array_key_exists($key, $this->values) && $grace === false) {
            if ($grace === false) {
                throw new Exception("Value with the key '{$key}' already exists'");
            }
        }

        $this->addValue($key, $value);
    }

    /**
     * Add value to the registry
     *
     * @param $key
     * @param $value
     */
    protected function addValue($key, $value)
    {
        $this->values[$key] = $value;
    }
}

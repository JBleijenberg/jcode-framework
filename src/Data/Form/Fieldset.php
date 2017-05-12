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
 * @category    docroot
 * @package     docroot
 * @author      Jeroen Bleijenberg <jeroen@jcode.nl>
 *
 * @copyright   Copyright (c) 2017 J!Code (http://www.jcode.nl)
 * @license     http://opensource.org/licenses/GPL-3.0 General Public License (GPL 3.0)
 */
namespace Jcode\Data\Form;

use Jcode\Application;
use Jcode\Object;

class Fieldset extends Object
{

    protected $label;

    protected $fields = [];

    /**
     * Set fieldset options while instantiating object
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->$option = $value;
            } else {
                $this->setData($option, $value);
            }
        }

        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Create a new input field with the given options and add it to the fieldset
     *
     * @param $id
     * @param $type
     * @param array $options
     * @return $this
     * @throws \Exception
     */
    public function addField($id, $type, array $options = [])
    {
        $type = ucfirst($type);

        $className = "Jcode\\Data\\Form\\Element\\{$type}";

        $instance = Application::objectManager()->get($className, [$options]);

        if (is_a($instance, $className)) {
            $instance->setId($id);

            $this->fields[$id] = $instance;
        }

        return $this;
    }

    /**
     * Return all added fields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
}
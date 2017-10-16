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
namespace Jcode\Model;

use Jcode\Application;
use Jcode\Db\AdapterInterface;

class Setup
{

    /**
     * Database adapter
     *
     * @var AdapterInterface
     */
    protected $adapter;

    public function init()
    {
        $config = Application::env()->getConfig('database');

        switch($config->getAdapter()) {
            case 'mysql':
                $this->adapter = Application::objectManager()->get('\Jcode\DBAdapter\Mysql');

                break;
            case 'postgresql':
                $this->adapter = Application::objectManager()->get('\Jcode\DBAdapter\Postgresql');

                break;
            default:
                throw new \Exception('Unknown database adapter');
        }

        $this->adapter->connect($config);
    }

    /**
     * @return AdapterInterface
     */
    public function start() :AdapterInterface
    {
        return $this->adapter;
    }

    public function end()
    {
        $this->adapter = null;
    }

    public function runQuery($query)
    {
        die($query);
    }
}
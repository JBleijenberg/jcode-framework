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
 * @category    J!Code: Framework
 * @package     J!Code: Framework
 * @author      Jeroen Bleijenberg <jeroen@maxserv.com>
 *
 * @copyright   Copyright (c) 2015 MaxServ (http://www.maxserv.com)
 * @license     http://opensource.org/licenses/GPL-3.0 General Public License (GPL 3.0)
 */
namespace Jcode\Db;

class Adapter
{

	protected $isSharedInstance = true;

	/**
	 * @inject \Jcode\Application\Config
	 * @var \Jcode\Application\Config
	 */
	protected $config;

	/**
	 * @inject \Jcode\Registry
	 * @var \Jcode\Registry
	 */
	protected $registry;

	/**
	 * @inject \Jcode\ObjectManager
	 * @var \Jcode\ObjectManager
	 */
	protected $objectmanager;

	/**
	 * @var \Jcode\Db\AdapterInterface
	 */
	protected $instance;

	public function init()
	{
		$config = $this->config;

		if ($config->getDatabase() && $config->getDatabase()->hasData()) {
			$this->instance = $this->objectmanager->get($config->getDatabase()->getAdapter());
			$this->instance->connect($config->getDatabase());
		}
	}

	public function getInstance()
	{
		return $this->instance;
	}
}
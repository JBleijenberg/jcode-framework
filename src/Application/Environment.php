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
 * @author      Jeroen Bleijenberg <jeroen@jcode.nl>
 *
 * @copyright   Copyright (c) 2017 J!Code (http://www.jcode.nl)
 * @license     http://opensource.org/licenses/GPL-3.0 General Public License (GPL 3.0)
 */
namespace Jcode\Application;

use Jcode\Application;
use Jcode\DataObject;
use Jcode\DataObject\Collection;
use \SimpleXMLElement;

class Environment
{

    const URL_TYPE_CSS = 'css';

    const URL_TYPE_JS = 'js';

    const URL_TYPE_DEFAULT = 'default';

    protected $eventId = 'jcode.application.environment';

    /**
     * @var \Jcode\Application\Config
     * @inject \Jcode\Application\Config
     */
    protected $config;

    /**
     * @var \Jcode\Router\Front
     */
    protected $front;

    public function configure()
    {
        $this->config->initApplicationConfiguration();
        $this->config->initModuleConfiguration();

        return $this;
    }

    public function setup()
    {

    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function dispatch()
    {
        $this->front = Application::objectManager()->get('Jcode\Router\Front');

        $this->front->dispatch();

        return $this;
    }

    /**
     * @return \Jcode\Router\Http\Response
     * @throws \Exception
     */
    public function getResponse()
    {
        if (!$this->front) {
            $this->front = Application::objectManager()->get('Jcode\Router\Front');
        }

        return $this->front->getResponse();
    }

    /**
     * @return \Jcode\Router\Http\Request
     */
    public function getRequest()
    {
        if (!$this->front) {
            $this->front = Application::objectManager()->get('Jcode\Router\Front');
        }

        return $this->front->getRequest();
    }

    /**
     * return configuration object.
     * If a path is given, that specific configuration is returned
     *
     * @param null $path
     *
     * @return \Jcode\Application\Config|string
     */
    public function getConfig($path = null)
    {
        $config = $this->config;

        if ($path !== null) {
            $path = explode('/', $path);

            foreach ($path as $p) {
                $config = $config->getData($p);
            }
        }

        return $config;
    }
}
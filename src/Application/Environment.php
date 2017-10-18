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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Tests\Iterator\Iterator;

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
        $modules        = Application::registry('module_collection');
        $moduleVersions = (file_exists(BP . '/modules.json'))
            ? json_decode(file_get_contents(BP . '/modules.json'), true)
            : [];

        foreach ($modules as $module) {
            if (!array_key_exists($module->getName(), $moduleVersions)) {
                $moduleVersions[$module->getName()] = '';
            }

            if (is_dir($module->getModulePath() . DS . 'Setup')) {
                $finder = new Finder();

                $finder
                    ->files()
                    ->ignoreUnreadableDirs()
                    ->followLinks()
                    ->name('*.php')
                    ->in($module->getModulePath() . DS . 'Setup');
            } else {
                $finder =  new Iterator();
            }

            while ($module->getVersion() !== $moduleVersions[$module->getName()]) {
                if ($moduleVersions[$module->getName()] == '') {
                    $filename = "install-([\d\.]+)\.php";
                } else {
                    $installedVersion = $moduleVersions[$module->getName()];
                    $filename         = "upgrade-{$installedVersion}-([\d\.]+)\.php$";
                }

                $file = array_filter(array_map(function($f) use($filename) {
                    preg_match("/{$filename}$/", $f, $matches);

                    return $matches;
                }, array_keys(iterator_to_array($finder))));

                if (!empty($file)) {
                    $fileArray    = current($file);
                    $fileLocation = $fileArray[0];
                    $newVersion   = $fileArray[1];

                    require_once $module->getModulePath() . '/Setup/' . $fileLocation;

                    $moduleVersions[$module->getName()] = $newVersion;
                } else {
                    $moduleVersions[$module->getName()] = $module->getVersion();
                }
            }

            file_put_contents(BP . '/modules.json', json_encode($moduleVersions, JSON_PRETTY_PRINT));
        }
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
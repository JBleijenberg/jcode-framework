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
namespace Jcode\Application;

use Jcode\Application;
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

    /**
     * @var \Jcode\Object\Collection
     */
    protected $layout;

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
     * @param $element
     *
     * @return mixed
     * @throws \Exception
     * @internal param \Jcode\Application\Resource\Template $block
     * @internal param $template
     */
    public function getLayout($element)
    {
        if (!is_string($element)) {
            $element = (string)$element;
        }

        if (!$this->layout) {
            $this->layout = $this->collectLayoutXml();
        }

        if ($layout = $this->layout->getData($element)) {
            return $this->parseLayoutElement($layout);
        }

        return null;
    }

    protected function parseLayoutElement(SimpleXMLElement $element)
    {
        $object = Application::objectManager()->get('Jcode\Object');

        if (isset($element['extends'])) {
            $child = $this->getLayout($element['extends']);

            foreach ($child as $childName => $childElement) {
                $object->setData($childName, $childElement);
            }
        }

        foreach ($element->reference as $reference) {
            $object->setData((string)$reference['name'], $this->parseReference($reference));
        }

        return $object;
    }

    public function parseReference(SimpleXMLElement $reference)
    {
        if (isset($reference['extends'])) {
            $referenceObject = $this->getLayout((string)$reference['extends'])->getData((string)$reference['name']);
        } else {
            $referenceObject = Application::objectManager()->get('Jcode\Object\Collection');
        }

        if (!$referenceObject->getItemById('child_html') instanceof \Jcode\Object\Collection) {
            $referenceObject->addItem(Application::objectManager()->get('Jcode\Object\Collection'), 'child_html');
        }

        foreach ($reference->block as $block) {
            /* @var \Jcode\Object\Collection $childHtml */
            $childHtml = $referenceObject->getItemById('child_html');

            if ($childHtml->getItemById((string)$block['name'])) {
                $childBlock = $childHtml->getItemById((string)$block['name']);

                foreach ($this->getLayoutBlock($block)->getData() as $key => $val) {
                    $childBlock->setData($key, $val);
                }
            } else {
                $childHtml->addItem($this->getLayoutBlock($block), (string)$block['name']);
            }
        }

        return $referenceObject;
    }

    /**
     * @param \SimpleXMLElement $element
     *
     * @return object
     * @throws \Exception
     */
    protected function getLayoutBlock(SimpleXMLElement $element)
    {
        $blockObject = Application::objectManager()->get((string)$element['class']);

        $blockObject->setName((string)$element['name']);

        if (isset($element['template'])) {
            $blockObject->setTemplate((string)$element['template']);
        }

        if ($element->method) {
            $methodCollection = Application::objectManager()->get('Jcode\Object\Collection');

            foreach ($element->method as $method) {
                $args = [];

                foreach ($method as $arg => $value) {
                    $args[$arg] = (string)$value;
                }

                $func = (string)$method['name'];

                $blockObject->$func(current($args));
            }
        }

        if ($element->block) {
            $collection = Application::objectManager()->get('Jcode\Object\Collection');

            foreach ($element->block as $block) {
                $collection->addItem($this->getLayoutBlock($block), (string)$block['name']);
            }

            $blockObject->setChildHtml($collection);
        }

        return $blockObject;
    }

    protected function collectLayoutXml()
    {
        $files = glob(BP . DS . 'application' . DS . '*' . DS . '*' . DS . 'View' . DS . 'Layout' . DS . Application::env()->getConfig('layout/name') . DS . '*.xml');

        $layoutArray = Application::objectManager()->get('Jcode\Object');

        foreach ($files as $file) {
            $xml = simplexml_load_file($file);

            foreach ($xml->request as $request) {
                if (!empty($request['path'])) {
                    $layoutArray->setData((string)$request['path'], $request);
                }
            }
        }

        return $layoutArray;
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
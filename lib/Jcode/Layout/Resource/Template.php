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
 * @author      Jeroen Bleijenberg <jeroen@maxserv.com>
 *
 * @copyright   Copyright (c) 2015 MaxServ (http://www.maxserv.com)
 * @license     http://opensource.org/licenses/GPL-3.0 General Public License (GPL 3.0)
 */
namespace Jcode\Layout\Resource;

use Jcode\Application;
use Jcode\Object;

class Template extends Object
{

	/**
	 * @inject \Jcode\Application\Config
	 * @var \Jcode\Application\Config
	 */
	protected $config;

	protected $template;

	/**
	 * @inject \Jcode\ObjectManager
	 * @var \Jcode\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @inject \Jcode\Registry
	 * @var \Jcode\Registry
	 */
	protected $registry;

	public function setTemplate($template)
	{
		$this->template = $template;
	}

	public function getTemplate()
	{
		return $this->template;
	}

	public function getCacheKey()
	{
		return md5(
			get_class($this) . get_called_class() . $this->template
		);
	}

	public function useCache()
	{
		return true;
	}

	/**
	 * @internal param $blockname
	 * @internal param array $vars
	 * @internal param null $template
	 */
	public function render()
	{
		if ($this->getTemplate()) {
			$templateArgs = explode('::', $this->getTemplate());

			$module = $this->config->getModule(current($templateArgs));

			next($templateArgs);

			if (file_exists($module->getModulePath() . DS . 'View' . DS . 'Template' . DS . current($templateArgs))) {
				include $module->getModulePath() . DS . 'View' . DS . 'Template' . DS . current($templateArgs);
			}
		}
	}

	public function getReferenceHtml($reference)
	{
		$layout = $this->registry->get('current_layout');

		if ($element = $layout->getData($reference)) {
			foreach ($element->getItemById('child_html') as $childHtml) {
				return $this->renderBlock($childHtml, ['reference' => $reference]);
			}
		}
	}

	public function getChildHtml($name)
	{
		$layout = $this->registry->get('current_layout')->getData($this->getReference());

		foreach ($layout->getItemById('child_html') as $block) {
			if ($block->getName() == $this->getName()) {
				return $this->renderBlock($block->getChildHtml()->getItemById($name));
			}
		}

		return null;
	}

	protected function renderBlock($block, array $args = [])
	{
		$instance = $this->objectManager->get($block->getClass());

		$instance->setTemplate($block->getTemplate());
		$instance->setName($block->getName());

		foreach ($args as $key => $val) {
			$instance->setData($key, $val);
		}

		if ($block->getMethods()) {
			foreach ($block->getMethods() as $method) {
				$action = $method->getMethod();

				$instance->$action(current($method->getArgs()));
			}
		}

		return $instance->render();
	}
}
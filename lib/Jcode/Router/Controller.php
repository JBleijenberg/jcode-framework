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
namespace Jcode\Router;

class Controller
{

    /**
     * @var \Jcode\Application\Layout
     */
    protected $layout;

    /**
     * @var \Jcode\Router\Http\Request
     */
    protected $_request;

    /**
     * @var \Jcode\Router\Http\Response
     */
    protected $_response;

    /**
     * @var \Jcode\Application\Config
     */
    protected $_config;

    /**
     * @var \Jcode\DependencyContainer
     */
    protected $_dc;

    public function __construct(
        \Jcode\Application\Layout $layout,
        \Jcode\Translate\Phrase $phrase,
        \Jcode\DependencyContainer $dc
    ) {
        $this->_layout = $layout;
        $this->_phrase = $phrase;
        $this->_dc = $dc;
    }

    /**
     * @param Http\Request $request
     * @return $this
     */
    public function setRequest(\Jcode\Router\Http\Request $request)
    {
        $this->_request = $request;

        return $this;
    }

    /**
     * @return Http\Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @param Http\Response $response
     * @return $this
     */
    public function setResponse(\Jcode\Router\Http\Response $response)
    {
        $this->_response = $response;

        return $this;
    }

    /**
     * @param \Jcode\Application\Config $config
     */
    public function setConfig(\Jcode\Application\Config $config)
    {
        $this->_config = $config;
    }

    /**
     * @return \Jcode\Application\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @return Http\Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @return \Jcode\Application\Layout
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    public function loadLayout($element = null, $moduleCode = null)
    {
        if ($element === null) {
            $element = sprintf('%s_%s_%s', $this->getRequest()->getModuleCode(),
                $this->getRequest()->getControllerName(), $this->getRequest()->getActionName());
        }

        if ($moduleCode !== null) {
            $module = $this->getConfig()->getModule($moduleCode);
        } else {
            $module = $this->getConfig()->getModule($this->getRequest()->getModuleCode());
        }

        $sourceDir = sprintf('%s/public/design/%s/template', BP, $this->getConfig()->getLayout());
        $targetDir = sprintf('%s/public/var/cache', BP);

        $helpers = [
            'dc' => function($class) { return $this->_dc->get($class); },
            'translate' => function($string) { return $this->_phrase->translate($string); }
        ];

        $flow = $this->_dc->get('Flow\Loader',
            [
                'source' => $sourceDir,
                'target' => $targetDir,
                'mode' => \Flow\Loader::RECOMPILE_ALWAYS,
                'helpers' => $helpers,
            ]);

        $layout = $this->getLayout();

        $layout->setElement($element);
        $layout->setFlow($flow);
        $layout->setModule($module);
        $layout->setConfig($this->getConfig());

        $this->_layout = $layout;

        return $this->_layout;
    }

    public function renderLayout()
    {
        if ($this->getLayout() instanceof \Jcode\Application\Layout) {
            $this->getLayout()->render();
        }

        return $this;
    }

    /**
     * This method is fired after the request and response is set and before the action is called
     *
     * @return $this
     */
    public function preDispatch()
    {
        return $this;
    }

    /**
     * This method is fired after the controlleraction is called
     *
     * @return $this
     */
    public function postDispatch()
    {
        return $this;
    }

    /**
     * @param int $code
     */
    public function noRoute($code = 404)
    {
        die($code);
    }
}
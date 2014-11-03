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

use Jcode\Translate\Phrase;

class Application
{

    /**
     * @var Router\Http
     */
    protected $_http;

    /**
     * @var DependencyContainer
     */
    protected $_dc;

    /**
     * @var Phrase
     */
    protected $_phrase;

    /**
     * @var array
     */
    private $_modules = [];

    /**
     * @param DependencyContainer $dc
     * @param Router\\Http $http
     * @param Phrase $phrase
     * @param Application\Config $config
     */
    public function __construct(DependencyContainer $dc, Router\Http $http, Translate\Phrase $phrase,
        Application\Config $config)
    {
        $this->_http = $http;
        $this->_dc = $dc;
        $this->_phrase = $phrase;
        $this->_config = $config;
    }

    public function translate()
    {
        return $this->_phrase->translate(func_get_args());
    }

    public function run()
    {
        if (!$this->_http instanceof Router\Http) {
            throw new \Exception($this->translate('Invalid request object. Expecting instance of \Jcode\Router\Model\Request\Http. %s Given instead', get_class($this->_http)));
        }

        try {
            $this->_config->initModules();

            $umask = umask(0);

            $this->_http->dispatch($this->_config);

            umask($umask);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
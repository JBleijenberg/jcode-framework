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
namespace Jcode\Application;

class Module extends \Jcode\Object
{

    /**
     * @var \Jcode\DependencyContainer
     */
    protected $_dc;

    /**
     * @param \Jcode\Translate\Phrase $phrase
     * @param \Jcode\DependencyContainer $dc
     * @param null $data
     */
    public function __construct(\Jcode\Translate\Phrase $phrase, \Jcode\DependencyContainer $dc, $data = null)
    {
        parent::__construct($phrase, $data);

        $this->_dc = $dc;
    }

    /**
     * Assign layouts to easier to use getData format, instead of having to loop through it for getting
     * the corrent layout
     *
     * @param array $design
     */
    public function setDesign(array $design)
    {
        if (!empty($design)) {
            $obj = $this->_dc->get('Jcode\Object');

            foreach ($design as $layout) {
                $obj->setData($layout->getPath(), $layout);
            }
        }

        return parent::setDesign($obj);
    }

    /**
     * Set module's observers in a way it is easy for the application to get the right one
     *
     * @param array $observers
     * @return mixed
     * @throws \Exception
     */
    public function setObservers(array $observers)
    {
        $event = $this->_dc->get('Jcode\Object');

        if (!empty($observers)) {
            foreach ($observers as $observer) {
                $currentObservers = $event->getData($observer->getHandle(), []);

                $currentObservers[$observer->getName()] = $observer;

                $event->setData($observer->getHandle(), $currentObservers);
            }
        }

        return parent::setObservers($event);
    }
}
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
namespace Jcode\Core\Controller;

class IndexController extends \Jcode\Router\Controller
{

    public function indexAction()
    {
        $testModel = $this->_dc->get('Jcode\Core\Model\Test');
        $collection = $testModel->getResource();

        $collection->addColumnsToSelect(['id', 'value']);

        $collection->addJoin(['second_test_table' => 'alias'], 'alias.other_id = main_table.id', ['other_id', 'other_value']);
        $collection->addFilter('value', ['eq' => ['derp', 'derp', 'merp']]);
        $collection->addFilter('id', 1);
        $collection->addOrder('value', 'ASC');
        $collection->addOrder('id', 'ASC');
        $collection->addLimit(1);
        $collection->addGroupBy('value');

        debug($collection->getQuery());
        debug($collection->getItems());
    }
}
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

class Model extends \Jcode\Object
{

    /**
     * @var string
     */
    protected $_table;

    /**
     * @var string
     */
    protected $_primaryKey;

    /**
     * @var string
     */
    protected $_resourceClass = 'Jcode\Application\Model\Resource';

    /**
     * @var \Jcode\DependencyContainer
     */
    protected $_dc;

    /**
     * @param \Jcode\Translate\Phrase $phrase
     * @param \Jcode\DependencyContainer $dc
     * @param null $data
     * @internal param \Jcode\Db\Adapter $adapter
     */
    public function __construct(\Jcode\Translate\Phrase $phrase, \Jcode\DependencyContainer $dc, $data = null)
    {
        parent::__construct($phrase, $data);

        $this->_dc = $dc;

        $this->_construct();
    }

    protected function _construct()
    {

    }

    /**
     * @param string $table
     * @param string $primaryKey
     */
    protected function _init($table, $primaryKey)
    {
        $this->_table = $table;
        $this->_primaryKey = $primaryKey;
    }

    /**
     * Set alternate collection model
     * @param string $classname
     * @return $this
     */
    public function setResourceClass($classname)
    {
        if ($classname != '') {
            $this->_resourceClass = $classname;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->_primaryKey;
    }

    /**
     * @return object
     * @throws \Exception
     */
    public function getResource()
    {
        $resource = $this->_dc->get($this->_resourceClass);

        $resource->init($this);

        return $resource;
    }
}
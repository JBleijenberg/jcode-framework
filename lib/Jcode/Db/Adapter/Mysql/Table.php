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
namespace Jcode\Db\Adapter\Mysql;

class Table
{

    /**
     * @var \Jcode\DependencyContainer
     */
    protected $_dc;

    protected $_tableName;

    protected $_columns = [];

    protected $_dropColumns = [];

    protected $_alterColumns = [];

    protected $_engine;

    protected $_primaryKey;

    protected $_charset = 'utf8';

    const ENGINE_INNODB = 'InnoDB';

    const ENGINE_MEMORY = 'memory';

    const ENGINE_MYISAM = 'MyISAM';

    public function __construct(\Jcode\DependencyContainer $dc)
    {
        $this->_dc = $dc;
    }

    /**
     * Set tablename
     *
     * @param $tableName
     * @return $this
     * @throws \Exception
     */
    public function setTableName($tableName)
    {
        $this->_tableName = $tableName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTableName()
    {
        return $this->_tableName;
    }

    /**
     * Set table engine
     *
     * @param $engine
     * @return $this
     * @throws \Exception
     */
    public function setEngine($engine)
    {
        $this->_engine = $engine;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEngine()
    {
        return $this->_engine;
    }

    public function getCharset()
    {
        return $this->_charset;
    }

    public function setCharset($charset)
    {
        $this->_charset($charset);
    }

    /**
     * Add column to this table
     *
     * @param $name
     * @param $type
     * @param null $length
     * @param array $options
     * @return $this
     * @throws \Exception
     */
    public function addColumn($name, $type, $length = null, array $options = [])
    {
        $column = $this->_dc->get('Jcode\Db\Adapter\Mysql\Table\Column');

        $column->setName($name);
        $column->setType($type);
        $column->setLength($length);
        $column->setOptions($options);

        array_push($this->_columns, $column);

        return $this;
    }

    /**
     * Alter column from this table
     *
     * @param $name
     * @param array $options
     * @return $this
     */
    public function alterColumn($name, array $options)
    {
        $column = $this->_dc->get('Jcode\Db\Adapter\Mysql\Table\Column');

        $column->setName($name);
        $column->setOptions($options);

        array_push($this->_alterColumns, $column);

        return $this;
    }

    public function getAlteredColumns()
    {
        return $this->_alterColumns;
    }

    /**
     * Drop column from table
     *
     * @param $name
     * @return $this
     */
    public function dropColumn($name)
    {
        array_push($this->_dropColumns, $name);

        return $this;
    }

    public function getDroppedColumns()
    {
        return $this->_dropColumns;
    }

    public function getColumns()
    {
        return $this->_columns;
    }

    public function setPrimaryKey($key)
    {
        $this->_primaryKey = $key;

        return $this;
    }

    public function getPrimaryKey()
    {
        return $this->_primaryKey;
    }
}
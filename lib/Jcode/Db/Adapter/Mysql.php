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
namespace Jcode\Db\Adapter;

use \Jcode\Db\Adapter\Mysql\Table\Column as Column;

class Mysql extends \PDO
{
    protected $_bindVars = [];

    protected $_bindIncrement = 1;

    /**
     * @var \Jcode\DependencyContainer
     */
    protected $_dc;

    /**
     * @var \Jcode\Log
     */
    protected $_log;

    private $_query;

    public function __construct(\Jcode\Application\ConfigSingleton $config, \Jcode\DependencyContainer $dc, \Jcode\Log $log)
    {
        $this->_dc = $dc;
        $this->_log = $log;

        if (($dbconfig = $config->getDatabase())) {
            $dsn = sprintf('mysql:dbname=%s;host=%s', $dbconfig->getName(), $dbconfig->getHost());

            $options = [
                parent::ATTR_TIMEOUT => 5,
                parent::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ];

            parent::__construct($dsn, $dbconfig->getUser(), $dbconfig->getPassword(), $options);
        }
    }

    public function getTable($tablename, $engine = 'InnoDB')
    {
        $table = $this->_dc->get('Jcode\Db\Adapter\Mysql\Table');

        $table->setTableName($tablename);
        $table->setEngine($engine);

        return $table;
    }

    public function alterTable(Mysql\Table $table)
    {
        $this->_query = sprintf('SHOW FULL COLUMNS FROM %s', $table->getTableName());

        $tableInfo = $this->execute();

        if (empty($tableInfo)) {
            throw new \Exception('Selected table is empty. Nothing to alter');
        }

        $orderedTableInfo = [];

        array_map(function($arr) use(&$orderedTableInfo){
            $orderedTableInfo[$arr['Field']] = $arr;
        },$tableInfo);

        $query = sprintf('ALTER TABLE %s', $table->getTableName());
        $tables = '';

        foreach ($table->getDroppedColumns() as $column) {
            if (!array_key_exists($column, $orderedTableInfo)) {
                throw new \Exception('Trying to drop a non existing column.');
            }

            $tables .= sprintf('DROP COLUMN %s, ', $column);
        }

        foreach ($table->getAlteredColumns() as $column) {
            if (!array_key_exists($column->getName(), $orderedTableInfo)) {
                throw new \Exception('Trying to alter a non existing column.');
            }

            if ($column->getOption('name')) {
                $tables .= sprintf('CHANGE COLUMN %s %s', $column->getName(), $column->getOption('name'));
            } else {
                $tables .= sprintf('CHANGE COLUMN %s', $column->getName());
            }

            $columnInfo = $orderedTableInfo[$column->getName()];

            if ($column->getOption('type')) {
                $tables .= sprintf(' %s', strtoupper($column->getOption('type')));

                switch ($column->getOption('type')) {
                    case Column::TYPE_BINARY:
                    case Column::TYPE_LONGBLOB:
                    case Column::TYPE_LONGTEXT:
                    case Column::TYPE_MEDIUMBLOB:
                    case Column::TYPE_MEDIUMTEXT:
                    case Column::TYPE_TINYBLOB:
                    case Column::TYPE_TINYTEXT:
                    case Column::TYPE_TEXT:
                    case Column::TYPE_BLOB:
                    case Column::TYPE_TIME:
                    case Column::TYPE_TIMESTAMP:
                    case Column::TYPE_DATE:
                    case Column::TYPE_DATETIME:
                        $length = null;

                        break;
                    default:
                        if (!$column->getOption('length')) {
                            throw new \Exception('Length value required for this column type.');
                        }

                        $length = $column->getOption('length');
                }

                $tables .= sprintf('(%s)', $length);
            }

            if ($column->getOption('unsigned') == true) {
                $tables .= ' unsigned';
            }

            if ($column->getOption('not_null') == true) {
                $tables .= ' NOT NULL';
            } else {
                if ($column->getOption('not_null') === false && $columnInfo['Null'] == 'YES') {
                    $tables .= ' NULL';
                }
            }

            if ($column->getOption('auto_increment') == true) {
                $tables .= ' AUTO_INCREMENT';
            }

            if ($column->getOption('zerofill') == true) {
                $tables .= ' ZEROFILL';
            } else {
                if ($column->getOption('zerofill') === false) {
                    $tables .= ' DROP ZEROFILL';
                }
            }

            if ($column->getOption('default')) {
                $tables .= sprintf(' DEFAULT "%s"', $column->getOption('default'));
            } else {
                if ($column->getOption('default') === false) {
                    $tables .= ' DROP DEFAULT';
                }
            }

            if ($column->getOption('comment')) {
                $tables .= sprintf(' COMMENT "%s"', $column->getOption('comment'));
            } else {
                if ($column->getOption('comment') === false) {
                    $tables .= ' COMMENT ""';
                }
            }

            $tables .= ', ';
        }

        if ($table->getColumns()) {
            foreach ($table->getColumns() as $column) {
                $tables .= 'ADD ';

                $this->getAddColumnQuery($table, $column, $tables);
            }
        }

        $tables = trim($tables, ', ');

        $this->_query = sprintf('%s %s;', $query, $tables);

        return $this->execute();
    }

    protected function _createTable(Mysql\Table $table, $query)
    {
        if (empty($table->getTableName())
            || empty($table->getEngine())
            || empty($table->getColumns())
        ) {

            throw new \Exception('Not enough data to create table');
        }

        $tables = '';

        foreach ($table->getColumns() as $column) {
            $this->getAddColumnQuery($table, $column, $tables);
        }

        if ($table->getPrimaryKey()) {
            $tables .= sprintf(' PRIMARY KEY(%s)', $table->getPrimaryKey());
        }

        $query .= sprintf(' (%s) ENGINE=%s DEFAULT CHARSET=%s;', $tables, $table->getEngine(), $table->getCharset());

        $this->_query = $query;

        $this->execute();
    }

    public function getAddColumnQuery($table, $column, &$tables)
    {
        if (empty($column->getName()) || empty($column->getType())) {
            throw new \Exception('Cannot add column to table. Name or type missing');
        }

        if ($column->getOption('primary') == true) {
            $table->setPrimaryKey($column->getName());
        }

        $tables .= sprintf('%s %s', $column->getName(), $column->getType());

        switch ($column->getType()) {
            case Column::TYPE_BINARY:
            case Column::TYPE_LONGBLOB:
            case Column::TYPE_LONGTEXT:
            case Column::TYPE_MEDIUMBLOB:
            case Column::TYPE_MEDIUMTEXT:
            case Column::TYPE_TINYBLOB:
            case Column::TYPE_TINYTEXT:
            case Column::TYPE_TEXT:
            case Column::TYPE_BLOB:
            case Column::TYPE_TIME:
            case Column::TYPE_TIMESTAMP:
            case Column::TYPE_DATE:
            case Column::TYPE_DATETIME:
                $length = null;

                break;
            default:
                $length = $column->getLength();

        }

        if ($length) {
            $tables .= sprintf('(%s)', $length);
        }

        if ($column->getOption('unsigned') == true) {
            $tables .= ' unsigned';
        }

        if ($column->getOption('not_null') == true) {
            $tables .= ' NOT NULL';
        }


        if ((!$table->getPrimaryKey() && $column->getOption('auto_increment') == true)
            || ($table->getPrimaryKey() == $column->getName())
        ) {
            $tables .= ' AUTO_INCREMENT';
        }

        if ($column->getOption('zerofill') == true) {
            $tables .= ' ZEROFILL';
        }

        if ($column->getOption('default') != false) {
            $tables .= sprintf(' DEFAULT "%s"', $column->getOption('default'));
        }

        if ($column->getOption('comment') != false) {
            $tables .= sprintf(' COMMENT "%s"', $column->getOption('comment'));
        }

        $tables .= ', ';
    }

    public function createTable(Mysql\Table $table)
    {
        return $this->_createTable($table, sprintf('CREATE TABLE %s', $table->getTableName()));
    }

    public function createTableIfNotExists(Mysql\Table $table)
    {
        return $this->_createTable($table, sprintf('CREATE TABLE IF NOT EXISTS %s', $table->getTableName()));
    }

    public function createTableDropIfExists(Mysql\Table $table)
    {
        return $this->_createTable($table, sprintf('DROP TABLE IF EXISTS %s; CREATE TABLE %s', $table->getTableName(), $table->getTableName()));
    }

    public function build(\Jcode\Application\Model\Resource $resource)
    {
        if ($this->_query != '') {
            return $this;
        }

        $select = '';

        if ($resource->getDistinct() && !in_array($resource->getDistinct(), $resource->getSelect())) {
            $select .= sprintf('DISTINCT %s, ', $resource->getDistinct());
        }

        foreach ($resource->getSelect() as $column) {
            $select .= sprintf('%s, ', $column);
        }

        $select = trim($select, ', ');
        $query = sprintf('SELECT %s FROM %s AS main_table', $select, $resource->getTable());

        if (!empty($resource->getJoin())) {
            foreach ($resource->getJoin() as $join) {
                reset($join['tables']);

                $query .= sprintf(' %s JOIN %s AS %s ON %s', strtoupper($join['type']), key($join['tables']),
                    current($join['tables']), $join['clause']);
            }
        }

        $where = '';

        if (!empty($resource->getFilter())) {
            $filters = $resource->getFilter();

            foreach ($filters as $column => $filter) {
                reset($filters);

                foreach ($filter as $condition) {
                    $where .= ($column === key($filters)) ? ' WHERE ' : 'AND ';

                    $this->formatStatement(key($condition), $column, current($condition), $where);
                }
            }

            if (empty($resource->getExpression()) && $where != '') {
                $query .= $where;
            }
        }

        if (!empty($resource->getExpression())) {
            $where .= (empty($where)) ? ' WHERE ' : ' AND ';

            foreach ($resource->getExpression() as $column => $expression) {
                foreach ($expression as $expr) {
                    $where .= sprintf('%s %s', $column, key($expr));

                    if (is_array(current($expr))) {
                        foreach (current($expr) as $value) {
                            $this->_bindVars[$this->_bindIncrement++] = $value;
                        }
                    } else {
                        $this->_bindVars[$this->_bindIncrement++] = current($expr);
                    }

                    $query .= $where;
                }
            }
        }

        if ($resource->getGroupBy()) {
            $query .= sprintf(' GROUP BY %s', $resource->getGroupBy());
        }

        if (!empty($resource->getOrder())) {
            foreach ($resource->getOrder() as $i => $order) {
                $query .= ($i == 0) ? sprintf(' ORDER BY %s %s', key($order), current($order)) : sprintf(', %s %s',
                    key($order), current($order));
            }
        }

        if (!empty($resource->getLimit())) {
            $query .= sprintf(' LIMIT %s, %s', $resource->getLimit('offset'), $resource->getLimit('limit'));
        }


        $this->_query = sprintf('%s;', $query);

        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function execute()
    {
        if (!$this->_query) {
            throw new \Exception('No query specified. Run build() first');
        }

        try {
            $this->beginTransaction();

            $stmt = parent::prepare($this->_query);

            foreach ($this->_bindVars as $id => $value) {
                $stmt->bindValue($id, $value);
            }

            $stmt->execute();
            $this->commit();
        } catch(\PDOException $e) {
            $this->_log->writeException($e);
            $this->rollBack();

            throw new \Exception($e->getMessage());
        } catch(\Exception $e) {
            $this->_log->writeException($e);
            $this->rollBack();

            throw new \Exception($e->getMessage());
        }

        return $stmt->fetchAll(parent::FETCH_ASSOC);
    }

    /**
     * Build query and parse it into a readable format.
     * This is for debugging purposes.
     *
     * @return bool|mixed
     */
    public function getQuery()
    {
        if ($this->_query) {
            $tmpVars = $this->_bindVars;

            $query = preg_replace_callback('/([\?])/', function () use (&$tmpVars) {
                return sprintf("'%s'", array_shift($tmpVars));
            }, $this->_query);

            return $query;
        }

        return false;
    }

    /**
     * @param $condition
     * @param $column
     * @param $value
     * @param $where
     * @throws \Exception
     */
    public function formatStatement($condition, $column, $value, &$where)
    {
        switch ($condition) {
            case 'eq':
                $this->_defaultFormatStatement('=', $column, $value, $where);
                break;
            case 'neq':
                $this->_defaultFormatStatement('!=', $column, $value, $where);
                break;
            case 'gt':
                $this->_defaultFormatStatement('>', $column, $value, $where);
                break;
            case 'lt':
                $this->_defaultFormatStatement('<', $column, $value, $where);
                break;
            case 'gteq':
                $this->_defaultFormatStatement('>=', $column, $value, $where);
                break;
            case 'lteq':
                $this->_defaultFormatStatement('<=', $column, $value, $where);
                break;
            case 'like':
                $this->_defaultFormatStatement('LIKE', $column, $value, $where);
                break;
            case 'nlike':
                $this->_defaultFormatStatement('NOT LIKE', $column, $value, $where);
                break;
            case 'in':
                $this->_formatInStatement('IN', $column, $value, $where);
                break;
            case 'nin':
                $this->_formatInStatement('NOT IN', $column, $value, $where);
                break;
            case 'null':
                $this->_formatNullStatement('IS NULL', $column, $where);
                break;
            case 'not-null':
                $this->_formatNullStatement('NOT NULL', $column, $where);
                break;
            default:
                throw new \Exception('Invalied condition supplied');
        }
    }

    /**
     * @param $condition
     * @param $column
     * @param $value
     * @param $where
     */
    protected function _defaultFormatStatement($condition, $column, $value, &$where)
    {
        if (is_array($value)) {
            $valArr = [];

            foreach ($value as $val) {
                $valArr[] = sprintf('%s %s ?', $column, $condition);

                $this->_bindVars[$this->_bindIncrement++] = $val;
            }

            $where .= sprintf('(%s) ', implode(' OR ', $valArr));
        } else {
            $where .= sprintf('%s %s ?', $column, $condition);
            $this->_bindVars[$this->_bindIncrement++] = $value;
        }
    }

    /**
     * @param $column
     * @param $value
     * @param $where
     * @param string $condition
     */
    protected function _formatInStatement($condition = 'IN', $column, $value, &$where)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        $where .= sprintf('%s %s (?) ', $column, $condition);

        $this->_bindVars[$this->_bindIncrement++] = $value;
    }

    /**
     * @param string $condition
     * @param $column
     * @param $where
     * @internal param $value
     */
    protected function _formatNullStatement($condition = 'IS NULL', $column, &$where)
    {
        $where .= sprintf('%s %s ', $column, $condition);
    }
}
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

class Mysql extends \PDO
{
    protected $_bindVars = [];

    protected $_bindIncrement = 1;

    private $_query;

    public function __construct(\Jcode\Application\Config $config)
    {
        if (($dbconfig = $config->getDatabase())) {
            $dsn = sprintf('mysql:dbname=%s;host=%s', $dbconfig->getName(), $dbconfig->getHost());

            $options = [
                parent::ATTR_TIMEOUT => 5,
                parent::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ];

            parent::__construct($dsn, $dbconfig->getUser(), $dbconfig->getPassword(), $options);
        }
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

                $query .= sprintf(' %s JOIN %s AS %s ON %s', strtoupper($join['type']), key($join['tables']), current($join['tables']), $join['clause']);
            }
        }

        $where = '';

        if (!empty($resource->getFilter())) {
            $filters = $resource->getFilter();

            foreach ($filters as $column => $filter) {
                reset($filters);

                foreach ($filter as $condition) {
                    $where .= ($column === key($filters)) ? ' WHERE ': 'AND ';

                    $this->formatStatement(key($condition), $column, current($condition), $where);
                }
            }

            if (empty($resource->getExpression()) && $where != '') {
                $query .= $where;
            }
        }

        if (!empty($resource->getExpression())) {
            $where .= ($where == '') ? ' WHERE ': ' AND ';

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
                $query .= ($i == 0) ? sprintf(' ORDER BY %s %s', key($order), current($order)) : sprintf(', %s %s', key($order), current($order)) ;
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

        $stmt = parent::prepare($this->_query);

        foreach ($this->_bindVars as $id => $value) {
            $stmt->bindValue($id, $value);
        }

        $result = $stmt->execute();

        if ($result === false) {
            $error = $stmt->errorInfo();

            throw new \Exception($error[2]);
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

            $query = preg_replace_callback('/([\?])/', function() use (&$tmpVars) {
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
                $this->_formatNullStatement('IS NULL', $column,$where);
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
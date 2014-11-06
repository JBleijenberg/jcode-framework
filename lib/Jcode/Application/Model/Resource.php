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
namespace Jcode\Application\Model;

class Resource extends \Jcode\Application\Model\Collection
{

    /**
     * @var object
     */
    protected $_adapter;

    /**
     * @var \Jcode\DependencyContainer
     */
    protected $_dc;

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
    protected $_modelClass;

    /**
     * Array of columns to select
     * @var array
     */
    protected $_select = ['main_table.*'];

    /**
     * Array of JOIN() to perform
     * @var array
     */
    protected $_join = [];

    /**
     * Array of WHERE() statements
     *
     * @var array
     */
    protected $_filter = [];

    protected $_expressions = [];

    /**
     * Array of ORDER() statements
     * @var array
     */
    protected $_order = [];

    /**
     * Array of LIMIT() statements
     * @var array
     */
    protected $_limit = [];

    protected $_distinct;

    protected $_group = [];

    /**
     * Array of allowed conditions
     * @var array
     */
    protected $_conditions = [
        'eq', // =
        'neq',// !=
        'gt', // >
        'lt', // <
        'gteq', // >=
        'lteq', // <=
        'like', // LIKE()
        'nlike',// NOT LIKE()
        'in', // IN()
        'nin', // NOT IN()
        'null', // IS NULL
        'not-null', // NOT NULL
    ];

    /**
     * @param \Jcode\Db\Adapter $adapter
     * @param \Jcode\DependencyContainer $dc
     */
    public function __construct(
        \Jcode\Translate\Phrase $phrase,
        \Jcode\DependencyContainer $dc,
        \Jcode\Db\Adapter $adapter,
        $data = null
    ) {
        parent::__construct($phrase, $dc, $data);

        $this->_adapter = $adapter->getInstance();
        $this->_dc = $dc;
    }

    /**
     * @param \Jcode\Application\Model $model
     * @return $this
     */
    public function init(\Jcode\Application\Model $model)
    {
        $this->_modelClass = get_class($model);
        $this->_table = $model->getTable();
        $this->_primaryKey = $model->getPrimaryKey();

        return $this;
    }

    /**
     * @return object
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->_primaryKey;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->_table;
    }

    public function getSelect()
    {
        return $this->_select;
    }

    public function getJoin()
    {
        return $this->_join;
    }

    public function getFilter()
    {
        return $this->_filter;
    }

    public function getExpression()
    {
        return $this->_expressions;
    }

    public function getOrder()
    {
        return $this->_order;
    }

    public function getLimit($key = null)
    {
        if ($key !== null) {
            return $this->_limit[$key];
        }

        return $this->_limit;
    }

    public function getDistinct()
    {
        return $this->_distinct;
    }

    public function getGroupBy()
    {
        return $this->_group;
    }

    /**
     * Add filter to query. If condition is not an array, filter defaults to $column = $condition
     *
     * @param string $column
     * @param string|array $filter
     * @return $this
     */
    public function addFilter($column, $filter)
    {
        if (!is_array($filter)) {
            $filter = ['eq' => $filter];
        }

        if (!strstr($column, '.')) {
            $column = sprintf('main_table.%s', $column);
        }

        reset($filter);

        if (in_array(key($filter), $this->_conditions)) {
            $this->_filter[$column][] = [key($filter) => current($filter)];
        }

        return $this;
    }

    /**
     * Add custom expression to filter
     *
     * @param $column
     * @param $expression
     * @param $values
     * @return $this
     */
    public function addExpressionFilter($column, $expression, $values)
    {
        if (!strstr($column, '.')) {
            $column = sprintf('main_table.%s', $column);
        }

        $this->_expressions[$column][] = [$expression => $values];

        return $this;
    }

    public function addJoin(array $tables, $clause, array $args = [], $type = 'inner')
    {
        reset($tables);

        $this->_join[key($tables)] = [
            'tables' => $tables,
            'clause' => $clause,
            'args' => $args,
            'type' => $type,
        ];

        if (empty($args)) {
            array_push($this->_select, sprintf('%s.*', current($tables)));
        } else {
            foreach ($args as $arg) {
                array_push($this->_select, sprintf('%s.%s', current($tables), $arg));
            }
        }

        return $this;
    }

    /**
     * Add Limit to select query
     *
     * @param int $offset
     * @param null $limit
     * @return $this
     */
    public function addLimit($offset = 0, $limit = null)
    {
        if ($limit === null) {
            $this->_limit = ['offset' => 0, 'limit' => $offset];
        } else {
            $this->_limit = ['offset' => $offset, 'limit' => $limit];
        }

        return $this;
    }

    /**
     * Add order to select query
     *
     * @param $column
     * @param string $direction
     * @return $this
     * @throws \Exception
     */
    public function addOrder($column, $direction = 'ASC')
    {
        if ($direction != 'ASC' && $direction != 'DESC') {
            throw new \Exception($this->translate('Invalid direction supplied for %s. ASC or DESC expected, %s given',
                    __FUNCTION__, $direction));
        }

        if (!strstr($column, '.')) {
            $column = sprintf('main_table.%s', $column);
        }

        array_push($this->_order, [$column => $direction]);

        return $this;
    }

    /**
     * Add distinct to select query
     *
     * @param $column
     * @return $this
     */
    public function addDistinct($column)
    {
        if (!strstr($column, '.')) {
            $column = sprintf('main_table.%s', $column);
        }

        $this->_distinct = $column;

        return $this;
    }

    /**
     * Add group by to select query
     *
     * @param $column
     * @return $this
     */
    public function addGroupBy($column)
    {
        if (!strstr($column, '.')) {
            $column = sprintf('main_table.%s', $column);
        }

        $this->_group = $column;

        return $this;
    }

    public function getItems()
    {
        if (empty($this->_items)) {
            $this->getAdapter()->build($this);

            $result = $this->getAdapter()->execute();

            foreach ($result as $item) {
                $itemObject = $this->_dc->get($this->_modelClass, $item);

                $this->addItem($itemObject);
            }
        }

        return $this->_items;
    }

    public function getQuery()
    {
        return $this->getAdapter()
            ->build($this)
            ->getQuery();
    }
}
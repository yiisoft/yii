<?php

/**
 * CJoinQuery represents a JOIN SQL statement.
 *
 * @author  Qiang Xue <qiang.xue@gmail.com>
 * @package system.db.ar
 * @since   1.0
 */
class CJoinQuery
{
    /**
     * @var array list of column selections
     */
    public $selects = [];
    /**
     * @var boolean whether to select distinct result set
     */
    public $distinct = false;
    /**
     * @var array list of join statement
     */
    public $joins = [];
    /**
     * @var array list of WHERE clauses
     */
    public $conditions = [];
    /**
     * @var array list of ORDER BY clauses
     */
    public $orders = [];
    /**
     * @var array list of GROUP BY clauses
     */
    public $groups = [];
    /**
     * @var array list of HAVING clauses
     */
    public $havings = [];
    /**
     * @var integer row limit
     */
    public $limit = -1;
    /**
     * @var integer row offset
     */
    public $offset = -1;
    /**
     * @var array list of query parameters
     */
    public $params = [];
    /**
     * @var array list of join element IDs (id=>true)
     */
    public $elements = [];

    /**
     * Constructor.
     *
     * @param CJoinElement $joinElement The root join tree.
     * @param CDbCriteria  $criteria    the query criteria
     */
    public function __construct($joinElement, $criteria = null)
    {
        if ($criteria !== null) {
            $this->selects[] = $joinElement->getColumnSelect($criteria->select);
            $this->joins[] = $joinElement->getTableNameWithAlias();
            $this->joins[] = $criteria->join;
            $this->conditions[] = $criteria->condition;
            $this->orders[] = $criteria->order;
            $this->groups[] = $criteria->group;
            $this->havings[] = $criteria->having;
            $this->limit = $criteria->limit;
            $this->offset = $criteria->offset;
            $this->params = $criteria->params;
            if (!$this->distinct && $criteria->distinct) {
                $this->distinct = true;
            }
        } else {
            $this->selects[] = $joinElement->getPrimaryKeySelect();
            $this->joins[] = $joinElement->getTableNameWithAlias();
            $this->conditions[] = $joinElement->getPrimaryKeyRange();
        }
        $this->elements[$joinElement->id] = true;
    }

    /**
     * Joins with another join element
     *
     * @param CJoinElement $element the element to be joined
     */
    public function join($element)
    {
        if ($element->slave !== null) {
            $this->join($element->slave);
        }
        if (!empty($element->relation->select)) {
            $this->selects[] = $element->getColumnSelect($element->relation->select);
        }
        $this->conditions[] = $element->relation->condition;
        $this->orders[] = $element->relation->order;
        $this->joins[] = $element->getJoinCondition();
        $this->joins[] = $element->relation->join;
        $this->groups[] = $element->relation->group;
        $this->havings[] = $element->relation->having;

        if (is_array($element->relation->params)) {
            if (is_array($this->params)) {
                $this->params = array_merge($this->params, $element->relation->params);
            } else {
                $this->params = $element->relation->params;
            }
        }
        $this->elements[$element->id] = true;
    }

    /**
     * Creates the SQL statement.
     *
     * @param CDbCommandBuilder $builder the command builder
     *
     * @return CDbCommand DB command instance representing the SQL statement
     */
    public function createCommand($builder)
    {
        $sql = ($this->distinct ? 'SELECT DISTINCT ' : 'SELECT ') . implode(', ', $this->selects);
        $sql .= ' FROM ' . implode(' ', array_unique($this->joins));

        $conditions = [];
        foreach ($this->conditions as $condition) {
            if ($condition !== '') {
                $conditions[] = $condition;
            }
        }
        if ($conditions !== []) {
            $sql .= ' WHERE (' . implode(') AND (', $conditions) . ')';
        }

        $groups = [];
        foreach ($this->groups as $group) {
            if ($group !== '') {
                $groups[] = $group;
            }
        }
        if ($groups !== []) {
            $sql .= ' GROUP BY ' . implode(', ', $groups);
        }

        $havings = [];
        foreach ($this->havings as $having) {
            if ($having !== '') {
                $havings[] = $having;
            }
        }
        if ($havings !== []) {
            $sql .= ' HAVING (' . implode(') AND (', $havings) . ')';
        }

        $orders = [];
        foreach ($this->orders as $order) {
            if ($order !== '') {
                $orders[] = $order;
            }
        }
        if ($orders !== []) {
            $sql .= ' ORDER BY ' . implode(', ', $orders);
        }

        $sql = $builder->applyLimit($sql, $this->limit, $this->offset);
        $command = $builder->getDbConnection()->createCommand($sql);
        $builder->bindValues($command, $this->params);
        return $command;
    }
}
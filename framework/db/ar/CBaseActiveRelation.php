<?php

/**
 * CBaseActiveRelation is the base class for all active relations.
 *
 * @author  Qiang Xue <qiang.xue@gmail.com>
 * @package system.db.ar
 */
class CBaseActiveRelation extends CComponent
{
    /**
     * @var string name of the related object
     */
    public $name;
    /**
     * @var string name of the related active record class
     */
    public $className;
    /**
     * @var mixed the foreign key in this relation
     */
    public $foreignKey;
    /**
     * @var mixed list of column names (an array, or a string of names separated by commas) to be selected.
     * Do not quote or prefix the column names unless they are used in an expression.
     * In that case, you should prefix the column names with 'relationName.'.
     */
    public $select = '*';
    /**
     * @var string WHERE clause. For {@link CActiveRelation} descendant classes, column names
     * referenced in the condition should be disambiguated with prefix 'relationName.'.
     */
    public $condition = '';
    /**
     * @var array the parameters that are to be bound to the condition.
     * The keys are parameter placeholder names, and the values are parameter values.
     */
    public $params = [];
    /**
     * @var string GROUP BY clause. For {@link CActiveRelation} descendant classes, column names
     * referenced in this property should be disambiguated with prefix 'relationName.'.
     */
    public $group = '';
    /**
     * @var string how to join with other tables. This refers to the JOIN clause in an SQL statement.
     * For example, <code>'LEFT JOIN users ON users.id=authorID'</code>.
     * @since 1.1.3
     */
    public $join = '';
    /**
     * @var string|array property for setting post-JOIN operations such as USE INDEX.
     * String typed value can be used with JOINs for HAS_MANY and MANY_MANY relations, while array typed
     * value designed to be used only with MANY_MANY relations. First array element will be used for junction
     * table JOIN and second array element will be used for target table JOIN.
     * @since 1.1.16
     */
    public $joinOptions = '';
    /**
     * @var string HAVING clause. For {@link CActiveRelation} descendant classes, column names
     * referenced in this property should be disambiguated with prefix 'relationName.'.
     */
    public $having = '';
    /**
     * @var string ORDER BY clause. For {@link CActiveRelation} descendant classes, column names
     * referenced in this property should be disambiguated with prefix 'relationName.'.
     */
    public $order = '';

    /**
     * Constructor.
     *
     * @param string $name       name of the relation
     * @param string $className  name of the related active record class
     * @param string $foreignKey foreign key for this relation
     * @param array  $options    additional options (name=>value). The keys must be the property names of this class.
     */
    public function __construct($name, $className, $foreignKey, $options = [])
    {
        $this->name = $name;
        $this->className = $className;
        $this->foreignKey = $foreignKey;
        foreach ($options as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Merges this relation with a criteria specified dynamically.
     *
     * @param array   $criteria  the dynamically specified criteria
     * @param boolean $fromScope whether the criteria to be merged is from scopes
     */
    public function mergeWith($criteria, $fromScope = false)
    {
        if ($criteria instanceof CDbCriteria) {
            $criteria = $criteria->toArray();
        }
        if (isset($criteria['select']) && $this->select !== $criteria['select']) {
            if ($this->select === '*' || $this->select === false) {
                $this->select = $criteria['select'];
            } elseif ($criteria['select'] === false) {
                $this->select = false;
            } elseif ($criteria['select'] !== '*') {
                $select1 = is_string($this->select) ? preg_split('/\s*,\s*/', trim($this->select), -1,
                    PREG_SPLIT_NO_EMPTY) : $this->select;
                $select2 = is_string($criteria['select']) ? preg_split('/\s*,\s*/', trim($criteria['select']), -1,
                    PREG_SPLIT_NO_EMPTY) : $criteria['select'];
                $this->select = array_merge($select1, array_diff($select2, $select1));
            }
        }

        if (isset($criteria['condition']) && $this->condition !== $criteria['condition']) {
            if ($this->condition === '') {
                $this->condition = $criteria['condition'];
            } elseif ($criteria['condition'] !== '') {
                $this->condition = "({$this->condition}) AND ({$criteria['condition']})";
            }
        }

        if (isset($criteria['params']) && $this->params !== $criteria['params']) {
            $this->params = array_merge($this->params, $criteria['params']);
        }

        if (isset($criteria['order']) && $this->order !== $criteria['order']) {
            if ($this->order === '') {
                $this->order = $criteria['order'];
            } elseif ($criteria['order'] !== '') {
                $this->order = $criteria['order'] . ', ' . $this->order;
            }
        }

        if (isset($criteria['group']) && $this->group !== $criteria['group']) {
            if ($this->group === '') {
                $this->group = $criteria['group'];
            } elseif ($criteria['group'] !== '') {
                $this->group .= ', ' . $criteria['group'];
            }
        }

        if (isset($criteria['join']) && $this->join !== $criteria['join']) {
            if ($this->join === '') {
                $this->join = $criteria['join'];
            } elseif ($criteria['join'] !== '') {
                $this->join .= ' ' . $criteria['join'];
            }
        }

        if (isset($criteria['having']) && $this->having !== $criteria['having']) {
            if ($this->having === '') {
                $this->having = $criteria['having'];
            } elseif ($criteria['having'] !== '') {
                $this->having = "({$this->having}) AND ({$criteria['having']})";
            }
        }
    }
}
<?php

/**
 * CActiveRelation is the base class for representing active relations that bring back related objects.
 *
 * @author  Qiang Xue <qiang.xue@gmail.com>
 * @package system.db.ar
 * @since   1.0
 */
class CActiveRelation extends CBaseActiveRelation
{
    /**
     * @var string join type. Defaults to 'LEFT OUTER JOIN'.
     */
    public $joinType = 'LEFT OUTER JOIN';
    /**
     * @var string ON clause. The condition specified here will be appended to the joining condition using AND operator.
     */
    public $on = '';
    /**
     * @var string the alias for the table that this relation refers to. Defaults to null, meaning
     * the alias will be the same as the relation name.
     */
    public $alias;
    /**
     * @var string|array specifies which related objects should be eagerly loaded when this related object is lazily loaded.
     * For more details about this property, see {@link CActiveRecord::with()}.
     */
    public $with = [];
    /**
     * @var boolean whether this table should be joined with the primary table.
     * When setting this property to be false, the table associated with this relation will
     * appear in a separate JOIN statement.
     * If this property is set true, then the corresponding table will ALWAYS be joined together
     * with the primary table, no matter the primary table is limited or not.
     * If this property is not set, the corresponding table will be joined with the primary table
     * only when the primary table is not limited.
     */
    public $together;
    /**
     * @var mixed scopes to apply
     * Can be set to the one of the following:
     * <ul>
     * <li>Single scope: 'scopes'=>'scopeName'.</li>
     * <li>Multiple scopes: 'scopes'=>array('scopeName1','scopeName2').</li>
     * </ul>
     * @since 1.1.9
     */
    public $scopes;
    /**
     * @var string the name of the relation that should be used as the bridge to this relation.
     * Defaults to null, meaning don't use any bridge.
     * @since 1.1.7
     */
    public $through;

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
        if ($fromScope) {
            if (isset($criteria['condition']) && $this->on !== $criteria['condition']) {
                if ($this->on === '') {
                    $this->on = $criteria['condition'];
                } elseif ($criteria['condition'] !== '') {
                    $this->on = "({$this->on}) AND ({$criteria['condition']})";
                }
            }
            unset($criteria['condition']);
        }

        parent::mergeWith($criteria);

        if (isset($criteria['joinType'])) {
            $this->joinType = $criteria['joinType'];
        }

        if (isset($criteria['on']) && $this->on !== $criteria['on']) {
            if ($this->on === '') {
                $this->on = $criteria['on'];
            } elseif ($criteria['on'] !== '') {
                $this->on = "({$this->on}) AND ({$criteria['on']})";
            }
        }

        if (isset($criteria['with'])) {
            $this->with = $criteria['with'];
        }

        if (isset($criteria['alias'])) {
            $this->alias = $criteria['alias'];
        }

        if (isset($criteria['together'])) {
            $this->together = $criteria['together'];
        }
    }
}
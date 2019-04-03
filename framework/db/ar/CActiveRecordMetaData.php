<?php

/**
 * CActiveRecordMetaData represents the meta-data for an Active Record class.
 *
 * @author  Qiang Xue <qiang.xue@gmail.com>
 * @package system.db.ar
 * @since   1.0
 */
class CActiveRecordMetaData
{
    /**
     * @var CDbTableSchema the table schema information
     */
    public $tableSchema;
    /**
     * @var array table columns
     */
    public $columns;
    /**
     * @var array list of relations
     */
    public $relations = [];
    /**
     * @var array attribute default values
     */
    public $attributeDefaults = [];

    private $_modelClassName;

    /**
     * Constructor.
     *
     * @param CActiveRecord $model the model instance
     *
     * @throws CDbException if specified table for active record class cannot be found in the database
     */
    public function __construct($model)
    {
        $this->_modelClassName = get_class($model);

        $tableName = $model->tableName();
        if (($table = $model->getDbConnection()->getSchema()->getTable($tableName)) === null) {
            throw new CDbException(Yii::t('yii',
                'The table "{table}" for active record class "{class}" cannot be found in the database.',
                ['{class}' => $this->_modelClassName, '{table}' => $tableName]));
        }

        if (($modelPk = $model->primaryKey()) !== null || $table->primaryKey === null) {
            $table->primaryKey = $modelPk;
            if (is_string($table->primaryKey) && isset($table->columns[$table->primaryKey])) {
                $table->columns[$table->primaryKey]->isPrimaryKey = true;
            } elseif (is_array($table->primaryKey)) {
                foreach ($table->primaryKey as $name) {
                    if (isset($table->columns[$name])) {
                        $table->columns[$name]->isPrimaryKey = true;
                    }
                }
            }
        }
        $this->tableSchema = $table;
        $this->columns = $table->columns;

        foreach ($table->columns as $name => $column) {
            if (!$column->isPrimaryKey && $column->defaultValue !== null) {
                $this->attributeDefaults[$name] = $column->defaultValue;
            }
        }

        foreach ($model->relations() as $name => $config) {
            $this->addRelation($name, $config);
        }
    }

    /**
     * Adds a relation.
     *
     * $config is an array with three elements:
     * relation type, the related active record class and the foreign key.
     *
     * @param string $name   $name Name of the relation.
     * @param array  $config $config Relation parameters.
     *
     * @return void
     * @throws CDbException
     * @since 1.1.2
     */
    public function addRelation($name, $config)
    {
        if (isset($config[0], $config[1], $config[2]))  // relation class, AR class, FK
        {
            $this->relations[$name] = new $config[0]($name, $config[1], $config[2], array_slice($config, 3));
        } else {
            throw new CDbException(Yii::t('yii',
                'Active record "{class}" has an invalid configuration for relation "{relation}". It must specify the relation type, the related active record class and the foreign key.',
                ['{class}' => $this->_modelClassName, '{relation}' => $name]));
        }
    }

    /**
     * Checks if there is a relation with specified name defined.
     *
     * @param string $name $name Name of the relation.
     *
     * @return boolean
     * @since 1.1.2
     */
    public function hasRelation($name)
    {
        return isset($this->relations[$name]);
    }

    /**
     * Deletes a relation with specified name.
     *
     * @param string $name $name
     *
     * @return void
     * @since 1.1.2
     */
    public function removeRelation($name)
    {
        unset($this->relations[$name]);
    }
}
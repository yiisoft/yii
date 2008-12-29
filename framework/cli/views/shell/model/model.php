<?php

class {ClassName} extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{TableName}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array({Rules}
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
		);
	}

	/**
	 * Returns the list of attributes that should not be massively assigned.
	 * You may also override safeAttributes() to specify the list of attributes
	 * that CAN be massively assigned. Do not override both, though.
	 * @return array list of attributes that should not be massively assigned.
	 */
	public function protectedAttributes()
	{
		return array(
		);
	}
}
<?php

class Post extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * This method is required by all child classes of CActiveRecord.
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
		return 'posts';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('title','length','max'=>128),
			array('title, create_time, author_id', 'required'),
			array('author_id', 'numerical', 'integerOnly'=>true),
		);
	}
}
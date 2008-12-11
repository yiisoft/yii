<?php

class {ClassName} extends CActiveRecord
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


	// -----------------------------------------------------------
	// Uncomment the following methods to override them if needed
	/*
	public function relations()
	{
		return array(
			'author'=>array(self::BELONGS_TO, 'User', 'authorId'),
			'comments'=>array(self::HAS_MANY, 'Comment', 'postId', 'with'=>'author', 'order'=>'createTime DESC'),
			'tags'=>array(self::MANY_MANY, 'Tag', 'PostTag(postId, tagId)', 'order'=>'name'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'authorID'=>'Author',
		);
	}

	public function protectedAttributes()
	{
		return array();
	}
	*/
}
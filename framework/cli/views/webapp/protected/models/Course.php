<?php

/**
 * Course model.
 * Represents a course.
 */
class Course extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{courses}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('title, description, link', 'required'),
			array('published', 'boolean'),
			array('title', 'length', 'max'=>255),
			array('description', 'length', 'max'=>500),
			array('link', 'length', 'max'=>500),
			array('link', 'url'),
			array('created_at, updated_at', 'safe'),
			// The following rule is used by search().
			array('id, title, description, link, published, created_at, updated_at', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'title' => 'Название курса',
			'description' => 'Описание',
			'link' => 'Ссылка на курс',
			'published' => 'Опубликовано',
			'created_at' => 'Дата создания',
			'updated_at' => 'Дата обновления',
		);
	}

	/**
	 * Before save, set timestamps
	 */
	protected function beforeSave()
	{
		if(parent::beforeSave())
		{
			$now = date('Y-m-d H:i:s');
			if($this->isNewRecord)
			{
				$this->created_at = $now;
			}
			$this->updated_at = $now;
			
			return true;
		}
		return false;
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('published',$this->published);
		$criteria->order = 'created_at DESC';

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @return Course the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}




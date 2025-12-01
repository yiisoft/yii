<?php

/**
 * News model.
 * Represents a news article.
 */
class News extends CActiveRecord
{
	public $imageFile;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{news}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('title, content, excerpt', 'required'),
			array('published', 'boolean'),
			array('title', 'length', 'max'=>255),
			array('image', 'length', 'max'=>500),
			array('slug', 'length', 'max'=>255),
			array('imageFile', 'file', 'types'=>'jpg, jpeg, png, gif, webp', 'allowEmpty'=>true),
			array('created_at, updated_at', 'safe'),
			// The following rule is used by search().
			array('id, title, content, excerpt, image, slug, published, created_at, updated_at', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'title' => 'Заголовок',
			'content' => 'Содержание',
			'excerpt' => 'Краткое описание',
			'image' => 'Изображение (URL)',
			'imageFile' => 'Файл изображения',
			'slug' => 'URL (slug)',
			'published' => 'Опубликовано',
			'created_at' => 'Дата создания',
			'updated_at' => 'Дата обновления',
		);
	}

	/**
	 * Before save, generate slug and set timestamps
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
			
			// Generate slug from title if not set
			if(empty($this->slug))
			{
				$this->slug = $this->generateSlug($this->title);
			}
			
			return true;
		}
		return false;
	}

	/**
	 * Generate URL-friendly slug from title
	 */
	private function generateSlug($title)
	{
		$slug = strtolower($title);
		$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
		$slug = trim($slug, '-');
		
		// Ensure uniqueness
		$baseSlug = $slug;
		$counter = 1;
		while(self::model()->exists('slug=:slug', array(':slug'=>$slug)))
		{
			$slug = $baseSlug . '-' . $counter;
			$counter++;
		}
		
		return $slug;
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
	 * @return News the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}


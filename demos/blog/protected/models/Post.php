<?php

class Post extends CActiveRecord
{
	const STATUS_DRAFT=0;
	const STATUS_PUBLISHED=1;
	const STATUS_ARCHIVED=2;

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
		return 'Post';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('title, content, status', 'required'),
			array('title', 'length', 'max'=>128),
			array('status', 'in', 'range'=>array(0, 1, 2)),
			array('tags', 'match', 'pattern'=>'/^[\w\s,]+$/', 'message'=>'Tags can only contain word characters.'),
		);
	}

	/**
	 * @return array attributes that can be massively assigned
	 */
	public function safeAttributes()
	{
		return array(
			'title',
			'content',
			'status',
			'tags',
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'author'=>array(self::BELONGS_TO, 'User', 'authorId'),
			'comments'=>array(self::HAS_MANY, 'Comment', 'postId', 'order'=>'??.createTime'),
			'tagFilter'=>array(self::MANY_MANY, 'Tag', 'PostTag(postId, tagId)',
				'together'=>true,
				'joinType'=>'INNER JOIN',
				'condition'=>'??.name=:tag'),
		);
	}

	/**
	 * @return array tags
	 */
	public function getTagArray()
	{
		return array_unique(preg_split('/\s*,\s*/',trim($this->tags),-1,PREG_SPLIT_NO_EMPTY));
	}

	/**
	 * @return array post status names indexed by status IDs
	 */
	public function getStatusOptions()
	{
		return array(
			self::STATUS_DRAFT=>'Draft',
			self::STATUS_PUBLISHED=>'Published',
			self::STATUS_ARCHIVED=>'Archived',
		);
	}

	/**
	 * @return string the status display for the current post
	 */
	public function getStatusText()
	{
		$options=$this->statusOptions;
		return isset($options[$this->status]) ? $options[$this->status] : "unknown ({$this->status})";
	}

	/**
	 * Prepares attributes before performing validation.
	 */
	protected function beforeValidate($on)
	{
		$parser=new CMarkdownParser;
		$this->contentDisplay=$parser->safeTransform($this->content);
		if($this->isNewRecord)
		{
			$this->createTime=$this->updateTime=time();
			$this->authorId=Yii::app()->user->id;
		}
		else
			$this->updateTime=time();
		return true;
	}

	/**
	 * Postprocessing after the record is saved
	 */
	protected function afterSave()
	{
		if(!$this->isNewRecord)
			$this->dbConnection->createCommand('DELETE FROM PostTag WHERE postId='.$this->id)->execute();

		foreach($this->getTagArray() as $name)
		{
			if(($tag=Tag::model()->findByAttributes(array('name'=>$name)))===null)
			{
				$tag=new Tag(array('name'=>$name));
				$tag->save();
			}
			$this->dbConnection->createCommand("INSERT INTO PostTag (postId, tagId) VALUES ({$this->id},{$tag->id})")->execute();
		}
	}

	/**
	 * Postprocessing after the record is deleted
	 */
	protected function afterDelete()
	{
		// The following two deletions are mainly for SQLite database.
		// In other DBMS, the related row deletion is enforced by FK constraints
		Comment::model()->deleteAll('postId='.$this->id);
		$this->dbConnection->createCommand('DELETE FROM PostTag WHERE postId='.$this->id)->execute();
	}
}
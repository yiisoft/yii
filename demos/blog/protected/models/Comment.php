<?php

class Comment extends CActiveRecord
{
	const STATUS_PENDING=0;
	const STATUS_APPROVED=1;
	/**
	 * @var string this property is used to collect user verification code input
	 */
	public $verifyCode;

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
		return 'Comment';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('author,email,url','length','max'=>128),
			array('author,email,content', 'required'),
			array('email','email'),
			array('url','url'),
			array('verifyCode', 'captcha', 'on'=>'insert', 'allowEmpty'=>!Yii::app()->user->isGuest || !extension_loaded('gd')),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'post'=>array(self::BELONGS_TO, 'Post', 'postId', 'joinType'=>'INNER JOIN'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'author'=>'Name',
			'url'=>'Website',
			'content'=>'Comment',
			'verifyCode'=>'Verification Code',
		);
	}

	/**
	 * @return array comment status names indexed by status IDs
	 */
	public function getStatusOptions()
	{
		return array(
			self::STATUS_PENDING=>'Pending',
			self::STATUS_APPROVED=>'Approved',
		);
	}

	/**
	 * @return string the status display for the current comment
	 */
	public function getStatusText()
	{
		$options=$this->statusOptions;
		return isset($options[$this->status]) ? $options[$this->status] : "unknown ({$this->status})";
	}

	/**
	 * @return string the hyperlink display for the current comment's author
	 */
	public function getAuthorLink()
	{
		if(!empty($this->url))
			return CHtml::link(CHtml::encode($this->author),$this->url);
		else
			return CHtml::encode($this->author);
	}

	/**
	 * @return integer the number of comments that are pending approval
	 */
	public function getPendingCommentCount()
	{
		return Comment::model()->count('status='.self::STATUS_PENDING);
	}

	/**
	 * @param integer the maximum number of comments that should be returned
	 * @return array the most recently added comments
	 */
	public function findRecentComments($limit=10)
	{
		$criteria=array(
			'condition'=>'Comment.status='.self::STATUS_APPROVED,
			'order'=>'Comment.createTime DESC',
			'limit'=>$limit,
		);
		return $this->with('post')->findAll($criteria);
	}

	/**
	 * Approves a comment.
	 */
	public function approve()
	{
		if($this->status==Comment::STATUS_PENDING)
		{
			$this->status=Comment::STATUS_APPROVED;
			$this->save();
			Post::model()->updateCounters(array('commentCount'=>1), "id={$this->postId}");
		}
	}

	/**
	 * Prepares attributes before performing validation.
	 */
	protected function beforeValidate($on)
	{
		$parser=new CMarkdownParser;
		$this->contentDisplay=$parser->safeTransform($this->content);
		if($this->isNewRecord)
			$this->createTime=time();
		return true;
	}

	/**
	 * Postprocessing after the record is saved
	 */
	protected function afterSave()
	{
		if($this->isNewRecord && $this->status==Comment::STATUS_APPROVED)
			Post::model()->updateCounters(array('commentCount'=>1), "id={$this->postId}");
	}

	/**
	 * Postprocessing after the record is deleted
	 */
	protected function afterDelete()
	{
		if($this->status==Comment::STATUS_APPROVED)
			Post::model()->updateCounters(array('commentCount'=>-1), "id={$this->postId}");
	}
}
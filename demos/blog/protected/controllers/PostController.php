<?php
/**
 * PostController class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * PostController controls the CRUD operations for posts.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 */
class PostController extends CController
{
	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_post;

	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image
			// this is used by the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xF5F5F5,
			),
		);
	}

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'list' and 'show' actions
				'actions'=>array('list','show','captcha'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated users to perform any action
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Shows a particular post.
	 */
	public function actionShow()
	{
		$post=$this->loadPost();
		$comment=$this->newComment($post);
		$this->render('show',array(
			'post'=>$post,
			'comments'=>$post->comments,
			'newComment'=>$comment,
		));
	}

	/**
	 * Creates a new post.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$post=new Post;
		if(isset($_POST['Post']))
		{
			$post->attributes=$_POST['Post'];
			if(isset($_POST['previewPost']))
				$post->validate();
			else if(isset($_POST['submitPost']) && $post->save())
				$this->redirect(array('show','id'=>$post->id));
		}
		$this->render('create',array('post'=>$post));
	}

	/**
	 * Updates a particular post.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$post=$this->loadPost();
		if(isset($_POST['Post']))
		{
			$post->attributes=$_POST['Post'];
			if(isset($_POST['previewPost']))
				$post->validate();
			else if(isset($_POST['submitPost']) && $post->save())
				$this->redirect(array('show','id'=>$post->id));
		}
		$this->render('update',array('post'=>$post));
	}

	/**
	 * Deletes a particular post.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadPost()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all posts.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria;
		$criteria->condition='status='.Post::STATUS_PUBLISHED;
		$criteria->order='createTime DESC';
		if(!empty($_GET['tag']))
		{
			$criteria->join='JOIN PostTag ON PostTag.postId=Post.id JOIN Tag ON PostTag.tagId=Tag.id';
			$criteria->condition.=' AND Tag.name=:tag';
			$criteria->params[':tag']=$_GET['tag'];
		}

		$pages=new CPagination(Post::model()->count($criteria));
		$pages->pageSize=Yii::app()->params['postsPerPage'];
		$pages->applyLimit($criteria);

		$posts=Post::model()->with('author')->findAll($criteria);

		$this->render('list',array(
			'posts'=>$posts,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all posts.
	 */
	public function actionAdmin()
	{
		$criteria=new CDbCriteria;

		$pages=new CPagination(Post::model()->count());
		$pages->applyLimit($criteria);

		$sort=new CSort('Post');
		$sort->defaultOrder='status ASC, createTime DESC';
		$sort->applyOrder($criteria);

		$posts=Post::model()->findAll($criteria);

		$this->render('admin',array(
			'posts'=>$posts,
			'pages'=>$pages,
			'sort'=>$sort,
		));
	}

	/**
	 * Generates the hyperlinks for post tags.
	 * This is mainly used by the view that displays a post.
	 * @param Post the post instance
	 * @return string the hyperlinks for the post tags
	 */
	public function getTagLinks($post)
	{
		$tags=$post->tags;
		$links=array();
		foreach($tags as $tag)
		{
			$url=$this->createUrl('list',array('tag'=>$tag->name));
			$links[]=CHtml::link(CHtml::encode($tag->name),$url);
		}
		return implode(', ',$links);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	protected function loadPost($id=null)
	{
		if($this->_post===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_post=Post::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_post===null || Yii::app()->user->isGuest && $this->_post->status!=Post::STATUS_PUBLISHED)
				throw new CHttpException(500,'The requested post does not exist.');
		}
		return $this->_post;
	}

	/**
	 * Creates a new comment.
	 * This method attempts to create a new comment based on the user input.
	 * If the comment is successfully created, the browser will be redirected
	 * to show the created comment.
	 * @param Post the post that the new comment belongs to
	 * @return Comment the comment instance
	 */
	protected function newComment($post)
	{
		$comment=new Comment;
		if(isset($_POST['Comment']))
		{
			$comment->attributes=$_POST['Comment'];
			$comment->postId=$post->id;
			if(Yii::app()->params['commentNeedApproval'])
				$comment->status=Comment::STATUS_PENDING;
			else
				$comment->status=Comment::STATUS_APPROVED;

			if(isset($_POST['previewComment']))
				$comment->validate(null,'insert');
			else if(isset($_POST['submitComment']) && $comment->save())
				$this->redirect(array('show','id'=>$post->id,'#'=>'c'.$comment->id));
		}
		return $comment;
	}
}

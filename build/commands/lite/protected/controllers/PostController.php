<?php

class PostController extends CController
{
	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='create';

	/**
	 * Specifies the action filters.
	 * This method overrides the parent implementation.
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
	 * This method overrides the parent implementation.
	 * It is only effective when 'accessControl' filter is enabled.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('deny',  // deny access to CUD for guest users
				'actions'=>array('delete'),
				'users'=>array('?'),
			),
		);
	}

	/**
	 * Lists all posts.
	 */
	public function actionList()
	{
		$pages=new CPagination(Post::model()->count());
		$postList=Post::model()->findAll($this->getListCriteria($pages));

		$this->render('list',array(
			'postList'=>$postList,
			'pages'=>$pages));
	}

	/**
	 * Shows a particular post.
	 */
	public function actionShow()
	{
		$this->render('show',array('post'=>$this->loadPost()));
	}

	/**
	 * Creates a new post.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$post=new Post;
		if(Yii::app()->request->isPostRequest)
		{
			if(isset($_POST['Post']))
				$post->setAttributes($_POST['Post']);
			if($post->save())
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
		if(Yii::app()->request->isPostRequest)
		{
			if(isset($_POST['Post']))
				$post->setAttributes($_POST['Post']);
			if($post->save())
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
	 * Loads the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 */
	protected function loadPost()
	{
		if(isset($_GET['id']))
			$post=Post::model()->findbyPk($_GET['id']);
		if(isset($post))
			return $post;
		else
			throw new CHttpException(500,'The requested post does not exist.');
	}

	/**
	 * @param CPagination the pagination information
	 * @return CDbCriteria the query criteria for Post list.
	 * It includes the ORDER BY and LIMIT/OFFSET information.
	 */
	protected function getListCriteria($pages)
	{
		$criteria=new CDbCriteria;
		$columns=Post::model()->tableSchema->columns;
		if(isset($_GET['sort']) && isset($columns[$_GET['sort']]))
		{
			$criteria->order=$columns[$_GET['sort']]->rawName;
			if(isset($_GET['desc']))
				$criteria->order.=' DESC';
		}
		$criteria->limit=$pages->pageSize;
		$criteria->offset=$pages->currentPage*$pages->pageSize;
		return $criteria;
	}

	/**
	 * Generates the header cell for the specified column.
	 * This method will generate a hyperlink for the column.
	 * Clicking on the link will cause the data to be sorted according to the column.
	 * @param string the column name
	 * @return string the generated header cell content
	 */
	protected function generateColumnHeader($column)
	{
		$params=$_GET;
		if(isset($params['sort']) && $params['sort']===$column)
		{
			if(isset($params['desc']))
				unset($params['desc']);
			else
				$params['desc']=1;
		}
		else
		{
			$params['sort']=$column;
			unset($params['desc']);
		}
		$url=$this->createUrl('list',$params);
		return CHtml::link(Post::model()->getAttributeLabel($column),$url);
	}
}

<?php
/**
 * CommentController class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CommentController controls the CRUD operations for comments.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 */
class CommentController extends CController
{
	const PAGE_SIZE=10;

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_comment;

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
			array('deny',  // deny guest users
				'users'=>array('?'),
			),
		);
	}

	/**
	 * Updates a particular comment.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$comment=$this->loadComment();

		if(isset($_POST['Comment']))
		{
			$comment->attributes=$_POST['Comment'];
			if(isset($_POST['previewComment']))
				$comment->validate('update');
			else if(isset($_POST['submitComment']) && $comment->save())
				$this->redirect(array('post/show','id'=>$comment->postId,'#'=>'c'.$comment->id));
		}

		$this->render('update',array('comment'=>$comment));
	}

	/**
	 * Deletes a particular comment.
	 * If deletion is successful, the browser will be redirected to the post page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$comment=$this->loadComment();
			$comment->delete();
			$this->redirect(array('post/show','id'=>$comment->postId));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Approves a particular comment.
	 * If approval is successful, the browser will be redirected to the post page.
	 */
	public function actionApprove()
	{
		if(Yii::app()->request->isPostRequest)
		{
			$comment=$this->loadComment();
			$comment->approve();
			$this->redirect(array('post/show','id'=>$comment->postId,'#'=>'c'.$comment->id));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all pending comments.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria;
		$criteria->condition='Comment.status='.Comment::STATUS_PENDING;

		$pages=new CPagination(Comment::model()->count());
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$comments=Comment::model()->with('post')->findAll($criteria);

		$this->render('list',array(
			'comments'=>$comments,
			'pages'=>$pages,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadComment($id=null)
	{
		if($this->_comment===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_comment=Comment::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_comment===null)
				throw new CHttpException(500,'The requested comment does not exist.');
		}
		return $this->_comment;
	}
}

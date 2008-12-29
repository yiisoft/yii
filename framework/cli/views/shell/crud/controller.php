<?php

class {ClassName} extends CController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_{ModelVar};

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
				'actions'=>array('list','show'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Shows a particular {ModelVar}.
	 */
	public function actionShow()
	{
		$this->render('show',array('{ModelVar}'=>$this->get{ModelClass}()));
	}

	/**
	 * Creates a new {ModelVar}.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		${ModelVar}=new {ModelClass};
		if(isset($_POST['{ModelClass}']))
		{
			${ModelVar}->attributes=$_POST['{ModelClass}'];
			if(${ModelVar}->save())
				$this->redirect(array('show','id'=>${ModelVar}->{ID}));
		}
		$this->render('create',array('{ModelVar}'=>${ModelVar}));
	}

	/**
	 * Updates a particular {ModelVar}.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		${ModelVar}=$this->get{ModelClass}();
		if(isset($_POST['{ModelClass}']))
		{
			${ModelVar}->attributes=$_POST['{ModelClass}'];
			if(${ModelVar}->save())
				$this->redirect(array('show','id'=>${ModelVar}->{ID}));
		}
		$this->render('update',array('{ModelVar}'=>${ModelVar}));
	}

	/**
	 * Deletes a particular {ModelVar}.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->get{ModelClass}()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all {ModelVar}s.
	 */
	public function actionList()
	{
		$pages=new CPagination({ModelClass}::model()->count());
		$pages->pageSize=self::PAGE_SIZE;
		${ModelVar}List={ModelClass}::model()->findAll($this->getListCriteria($pages));

		$this->render('list',array(
			'{ModelVar}List'=>${ModelVar}List,
			'pages'=>$pages));
	}

	/**
	 * Manages all {ModelVar}s.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$pages=new CPagination({ModelClass}::model()->count());
		$pages->pageSize=self::PAGE_SIZE;
		${ModelVar}List={ModelClass}::model()->findAll($this->getListCriteria($pages));

		$this->render('admin',array(
			'{ModelVar}List'=>${ModelVar}List,
			'pages'=>$pages));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function get{ModelClass}($id=null)
	{
		if($this->_{ModelVar}===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_{ModelVar}={ModelClass}::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_{ModelVar}===null)
				throw new CHttpException(500,'The requested {ModelName} does not exist.');
		}
		return $this->_{ModelVar};
	}

	/**
	 * @param CPagination the pagination information
	 * @return CDbCriteria the query criteria for {ModelClass} list.
	 * It includes the ORDER BY and LIMIT/OFFSET information.
	 */
	protected function getListCriteria($pages)
	{
		$criteria=new CDbCriteria;
		$columns={ModelClass}::model()->tableSchema->columns;
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
		$url=$this->createUrl('admin',$params);
		return CHtml::link({ModelClass}::model()->getAttributeLabel($column),$url);
	}

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->get{ModelClass}($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}
	}
}

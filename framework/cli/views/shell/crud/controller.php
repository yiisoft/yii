<?php

class {ClassName} extends CController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

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
			array('deny',  // deny access to create, update, delete operations for guest users
				'actions'=>array('create','update','delete'),
				'users'=>array('?'),
			),
			array('allow', // allow access to admin operation for admin user
				'actions'=>array('admin'),
				'users'=>array('admin'),
			),
			array('deny',  // deny access to admin operation for all users
				'actions'=>array('admin'),
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Shows a particular {ModelVar}.
	 */
	public function actionShow()
	{
		$this->render('show',array('{ModelVar}'=>$this->load{ModelClass}()));
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
		${ModelVar}=$this->load{ModelClass}();
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
			$this->load{ModelClass}()->delete();
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
		$pages=$this->paginate({ModelClass}::model()->count(), self::PAGE_SIZE);
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

		$pages=$this->paginate({ModelClass}::model()->count(), self::PAGE_SIZE);
		${ModelVar}List={ModelClass}::model()->findAll($this->getListCriteria($pages));

		$this->render('admin',array(
			'{ModelVar}List'=>${ModelVar}List,
			'pages'=>$pages));
	}

	/**
	 * Loads the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 */
	protected function load{ModelClass}()
	{
		if(isset($_GET['id']))
			${ModelVar}={ModelClass}::model()->findbyPk($_GET['id']);
		if(isset(${ModelVar}))
			return ${ModelVar};
		else
			throw new CHttpException(500,'The requested {ModelName} does not exist.');
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
			if(Yii::app()->user->isGuest)
				Yii::app()->user->loginRequired();

			if((${ModelVar}={ModelClass}::model()->findbyPk($_POST['id']))!==null)
			{
				${ModelVar}->delete();
				// reload the current page to avoid duplicated delete actions
				$this->refresh();
			}
			else
				throw new CHttpException(500,'The requested {ModelName} does not exist.');
		}
	}
}

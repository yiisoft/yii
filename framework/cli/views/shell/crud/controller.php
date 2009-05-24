<?php
/**
 * This is the template for generating the controller class file for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $model: the finder object
 * - $modelClass: the model class name
 * - $modelVar: the PHP variable name storing the model instance
 * - $modelName: the model name
 */
?>
<?php echo "<?php\n"; ?>

class <?php echo $controllerClass; ?> extends CController
{
	const PAGE_SIZE=10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction='list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_<?php echo $modelVar; ?>;

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
	 * Shows a particular <?php echo $modelVar; ?>.
	 */
	public function actionShow()
	{
		$this->render('show',array('<?php echo $modelVar; ?>'=>$this->load<?php echo $modelClass; ?>()));
	}

	/**
	 * Creates a new <?php echo $modelVar; ?>.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$<?php echo $modelVar; ?>=new <?php echo $modelClass; ?>;
		if(isset($_POST['<?php echo $modelClass; ?>']))
		{
			$<?php echo $modelVar; ?>->attributes=$_POST['<?php echo $modelClass; ?>'];
			if($<?php echo $modelVar; ?>->save())
				$this->redirect(array('show','id'=>$<?php echo $modelVar; ?>-><?php echo $ID; ?>));
		}
		$this->render('create',array('<?php echo $modelVar; ?>'=>$<?php echo $modelVar; ?>));
	}

	/**
	 * Updates a particular <?php echo $modelVar; ?>.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$<?php echo $modelVar; ?>=$this->load<?php echo $modelClass; ?>();
		if(isset($_POST['<?php echo $modelClass; ?>']))
		{
			$<?php echo $modelVar; ?>->attributes=$_POST['<?php echo $modelClass; ?>'];
			if($<?php echo $modelVar; ?>->save())
				$this->redirect(array('show','id'=>$<?php echo $modelVar; ?>-><?php echo $ID; ?>));
		}
		$this->render('update',array('<?php echo $modelVar; ?>'=>$<?php echo $modelVar; ?>));
	}

	/**
	 * Deletes a particular <?php echo $modelVar; ?>.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->load<?php echo $modelClass; ?>()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all <?php echo $modelVar; ?>s.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria;

		$pages=new CPagination(<?php echo $modelClass; ?>::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$<?php echo $modelVar; ?>List=<?php echo $modelClass; ?>::model()->findAll($criteria);

		$this->render('list',array(
			'<?php echo $modelVar; ?>List'=>$<?php echo $modelVar; ?>List,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all <?php echo $modelVar; ?>s.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$criteria=new CDbCriteria;

		$pages=new CPagination(<?php echo $modelClass; ?>::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$sort=new CSort('<?php echo $modelClass; ?>');
		$sort->applyOrder($criteria);

		$<?php echo $modelVar; ?>List=<?php echo $modelClass; ?>::model()->findAll($criteria);

		$this->render('admin',array(
			'<?php echo $modelVar; ?>List'=>$<?php echo $modelVar; ?>List,
			'pages'=>$pages,
			'sort'=>$sort,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function load<?php echo $modelClass; ?>($id=null)
	{
		if($this->_<?php echo $modelVar; ?>===null)
		{
			if($id!==null || isset($_GET['id']))
				$this->_<?php echo $modelVar; ?>=<?php echo $modelClass; ?>::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_<?php echo $modelVar; ?>===null)
				throw new CHttpException(404,'The requested <?php echo $modelName; ?> does not exist.');
		}
		return $this->_<?php echo $modelVar; ?>;
	}

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->load<?php echo $modelClass; ?>($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}
	}
}

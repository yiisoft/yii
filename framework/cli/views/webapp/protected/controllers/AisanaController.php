<?php

class AisanaController extends Controller
{
	public $layout='admin';

	/**
	 * Filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control
		);
	}

	/**
	 * Specifies the access control rules.
	 */
	public function accessRules()
	{
		return array(
		array('allow',
			'actions'=>array('login', 'news', 'newsView', 'courses'),
			'users'=>array('*'),
		),
			array('allow',
				'actions'=>array('index'),
				'users'=>array('*'),
			),
		array('allow',
			'actions'=>array('dashboard', 'logout', 'newsAdmin', 'newsCreate', 'newsUpdate', 'newsDelete', 'coursesAdmin', 'courseCreate', 'courseUpdate', 'courseDelete'),
			'users'=>array('@'),
		),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		// If already logged in, redirect to admin dashboard
		if(!Yii::app()->user->isGuest)
		{
			$this->redirect(array('aisana/dashboard'));
		}

		$model=new LoginForm;

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(array('aisana/dashboard'));
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(array('aisana/login'));
	}

	/**
	 * Главная страница (публичная)
	 */
	public function actionIndex()
	{
		$this->layout = 'main';
		$this->render('index');
	}

	/**
	 * Admin dashboard
	 */
	public function actionDashboard()
	{
		$this->render('dashboard');
	}

	/**
	 * Lists all news for public view
	 */
	public function actionNews()
	{
		$this->layout = 'main';
		$criteria = new CDbCriteria;
		$criteria->condition = 'published = 1';
		$criteria->order = 'created_at DESC';
		
		$dataProvider = new CActiveDataProvider('News', array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize' => 10,
			),
		));
		
		$this->render('news', array(
			'dataProvider' => $dataProvider,
		));
	}

	/**
	 * Displays a particular news item
	 */
	public function actionNewsView($slug)
	{
		$this->layout = 'main';
		$model = News::model()->find('slug=:slug', array(':slug'=>$slug));
		
		if($model === null)
			throw new CHttpException(404, 'Новость не найдена.');
		
		$this->render('newsView', array(
			'model' => $model,
		));
	}

	/**
	 * Manages all news (admin)
	 */
	public function actionNewsAdmin()
	{
		$model=new News('search');
		$model->unsetAttributes();
		if(isset($_GET['News']))
			$model->attributes=$_GET['News'];

		$this->render('newsAdmin',array(
			'model'=>$model,
		));
	}

	/**
	 * Creates a new news.
	 */
	public function actionNewsCreate()
	{
		$model=new News;

		if(isset($_POST['News']))
		{
			$model->attributes=$_POST['News'];
			$model->imageFile = CUploadedFile::getInstance($model, 'imageFile');

			if($model->imageFile instanceof CUploadedFile)
			{
				$storedPath = $this->saveUploadedImage($model->imageFile);
				if($storedPath !== null)
				{
					$model->image = $storedPath;
				}
				else
				{
					Yii::app()->user->setFlash('error', 'Не удалось сохранить изображение. Попробуйте снова.');
				}
			}

			if($model->save())
			{
				Yii::app()->user->setFlash('success', 'Новость успешно создана!');
				$this->redirect(array('aisana/newsAdmin'));
			}
		}

		$this->render('newsCreate',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular news.
	 */
	public function actionNewsUpdate($id)
	{
		$model=$this->loadNewsModel($id);

		if(isset($_POST['News']))
		{
			$model->attributes=$_POST['News'];
			$model->imageFile = CUploadedFile::getInstance($model, 'imageFile');

			if($model->imageFile instanceof CUploadedFile)
			{
				$storedPath = $this->saveUploadedImage($model->imageFile);
				if($storedPath !== null)
				{
					$model->image = $storedPath;
				}
				else
				{
					Yii::app()->user->setFlash('error', 'Не удалось сохранить изображение. Попробуйте снова.');
				}
			}

			if($model->save())
			{
				Yii::app()->user->setFlash('success', 'Новость успешно обновлена!');
				$this->redirect(array('aisana/newsAdmin'));
			}
		}

		$this->render('newsUpdate',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular news.
	 */
	public function actionNewsDelete($id)
	{
		$this->loadNewsModel($id)->delete();
		Yii::app()->user->setFlash('success', 'Новость удалена!');
		$this->redirect(array('aisana/newsAdmin'));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 */
	public function loadNewsModel($id)
	{
		$model=News::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'Запрашиваемая страница не существует.');
		return $model;
	}

	/**
	 * Handles upload of news images and returns relative path.
	 */
	protected function saveUploadedImage(CUploadedFile $file)
	{
		$uploadDir = Yii::getPathOfAlias('webroot') . '/uploads/news';

		if(!is_dir($uploadDir))
		{
			CFileHelper::createDirectory($uploadDir, 0755, true);
		}

		$filename = uniqid('news_', true) . '.' . $file->getExtensionName();
		$fullPath = $uploadDir . '/' . $filename;

		if($file->saveAs($fullPath))
		{
			return '/uploads/news/' . $filename;
		}

		return null;
	}

	/**
	 * Lists all courses for public view
	 */
	public function actionCourses()
	{
		$this->layout = 'main';
		$criteria = new CDbCriteria;
		$criteria->condition = 'published = 1';
		$criteria->order = 'created_at DESC';
		
		$courses = Course::model()->findAll($criteria);
		
		$this->render('courses', array(
			'courses' => $courses,
		));
	}

	/**
	 * Manages all courses (admin)
	 */
	public function actionCoursesAdmin()
	{
		$model=new Course('search');
		$model->unsetAttributes();
		if(isset($_GET['Course']))
			$model->attributes=$_GET['Course'];

		$this->render('coursesAdmin',array(
			'model'=>$model,
		));
	}

	/**
	 * Creates a new course.
	 */
	public function actionCourseCreate()
	{
		$model=new Course;

		if(isset($_POST['Course']))
		{
			$model->attributes=$_POST['Course'];
			if($model->save())
			{
				Yii::app()->user->setFlash('success', 'Курс успешно создан!');
				$this->redirect(array('aisana/coursesAdmin'));
			}
		}

		$this->render('courseCreate',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular course.
	 */
	public function actionCourseUpdate($id)
	{
		$model=$this->loadCourseModel($id);

		if(isset($_POST['Course']))
		{
			$model->attributes=$_POST['Course'];
			if($model->save())
			{
				Yii::app()->user->setFlash('success', 'Курс успешно обновлен!');
				$this->redirect(array('aisana/coursesAdmin'));
			}
		}

		$this->render('courseUpdate',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular course.
	 */
	public function actionCourseDelete($id)
	{
		$this->loadCourseModel($id)->delete();
		Yii::app()->user->setFlash('success', 'Курс удален!');
		$this->redirect(array('aisana/coursesAdmin'));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 */
	public function loadCourseModel($id)
	{
		$model=Course::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'Запрашиваемая страница не существует.');
		return $model;
	}
}


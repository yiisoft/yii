<?php
/**
 * This file contains the error handler application component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('CHtml',true);

/**
 * CErrorHandler handles uncaught PHP errors and exceptions.
 *
 * It displays these errors using appropriate views based on the
 * nature of the error and the mode the application runs at.
 * It also chooses the most preferred language for displaying the error.
 *
 * CErrorHandler uses two sets of views:
 * <ul>
 * <li>development views, named as <code>exception.php</code>;
 * <li>production views, named as <code>error&lt;StatusCode&gt;.php</code>;
 * </ul>
 * where &lt;StatusCode&gt; stands for the HTTP error code (e.g. error500.php).
 * Localized views are named similarly but located under a subdirectory
 * whose name is the language code (e.g. zh_cn/error500.php).
 *
 * Development views are displayed when the application is in debug mode
 * (i.e. YII_DEBUG is defined as true). Detailed error information with source code
 * are displayed in these views. Production views are meant to be shown
 * to end-users and are used when the application is in production mode.
 * For security reasons, they only display the error message without any
 * sensitive information.
 *
 * CErrorHandler looks for the view templates from the following locations in order:
 * <ol>
 * <li><code>themes/ThemeName/views/system</code>: when a theme is active.</li>
 * <li><code>protected/views/system</code></li>
 * <li><code>framework/views</code></li>
 * </ol>
 * If the view is not found in a directory, it will be looked for in the next directory.
 *
 * The property {@link maxSourceLines} can be changed to specify the number
 * of source code lines to be displayed in development views.
 *
 * CErrorHandler is a core application component that can be accessed via
 * {@link CApplication::getErrorHandler()}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.base
 * @since 1.0
 */
class CErrorHandler extends CApplicationComponent
{
	/**
	 * @var integer maximum number source code lines to be displayed. Defaults to 25.
	 */
	public $maxSourceLines=25;
	/**
	 * @var string the application administrator information (could be a name or email link). It is displayed in error pages to end users. Defaults to 'the webmaster'.
	 */
	public $adminInfo='the webmaster';
	/**
	 * @var boolean whether to discard any existing page output before error display. Defaults to true.
	 */
	public $discardOutput=true;
	/**
	 * @var string the route (eg 'site/error') to the controller action that will be used to display external errors.
	 * Inside the action, it can retrieve the error information by Yii::app()->errorHandler->error.
	 * This property defaults to null, meaning CErrorHandler will handle the error display.
	 * @since 1.0.6
	 */
	public $errorAction;

	private $_error;

	/**
	 * Handles the exception/error event.
	 * This method is invoked by the application whenever it captures
	 * an exception or PHP error.
	 * @param CEvent $event the event containing the exception/error information
	 */
	public function handle($event)
	{
		// set event as handled to prevent it from being handled by other event handlers
		$event->handled=true;

		if($this->discardOutput)
		{
			while(@ob_end_clean()) ;
		}

		if($event instanceof CExceptionEvent)
			$this->handleException($event->exception);
		else // CErrorEvent
			$this->handleError($event);
	}

	/**
	 * Returns the details about the error that is currently being handled.
	 * The error is returned in terms of an array, with the following information:
	 * <ul>
	 * <li>code - the HTTP status code (e.g. 403, 500)</li>
	 * <li>type - the error type (e.g. 'CHttpException', 'PHP Error')</li>
	 * <li>message - the error message</li>
	 * <li>file - the name of the PHP script file where the error occurs</li>
	 * <li>line - the line number of the code where the error occurs</li>
	 * <li>trace - the call stack of the error</li>
	 * <li>source - the context source code where the error occurs</li>
	 * </ul>
	 * @return array the error details. Null if there is no error.
	 * @since 1.0.6
	 */
	public function getError()
	{
		return $this->_error;
	}

	/**
	 * Handles the exception.
	 * @param Exception $exception the exception captured
	 */
	protected function handleException($exception)
	{
		$app=Yii::app();
		if($app instanceof CWebApplication)
		{
			if(($trace=$this->getExactTrace($exception))===null)
			{
				$fileName=$exception->getFile();
				$errorLine=$exception->getLine();
			}
			else
			{
				$fileName=$trace['file'];
				$errorLine=$trace['line'];
			}
			$this->_error=$data=array(
				'code'=>($exception instanceof CHttpException)?$exception->statusCode:500,
				'type'=>get_class($exception),
				'errorCode'=>$exception->getCode(),
				'message'=>$exception->getMessage(),
				'file'=>$fileName,
				'line'=>$errorLine,
				'trace'=>$exception->getTraceAsString(),
				'source'=>$this->getSourceLines($fileName,$errorLine),
			);

			if(!headers_sent())
				header("HTTP/1.0 {$data['code']} ".get_class($exception));
			if($exception instanceof CHttpException || !YII_DEBUG)
				$this->render('error',$data);
			else
				$this->render('exception',$data);
		}
		else
			$app->displayException($exception);
	}

	/**
	 * Handles the PHP error.
	 * @param CErrorEvent $event the PHP error event
	 */
	protected function handleError($event)
	{
		$trace=debug_backtrace();
		// skip the first 3 stacks as they do not tell the error position
		if(count($trace)>3)
			$trace=array_slice($trace,3);
		$traceString='';
		foreach($trace as $i=>$t)
		{
			if(!isset($t['file']))
				$t['file']='unknown';
			if(!isset($t['line']))
				$t['line']=0;
			if(!isset($t['function']))
				$t['function']='unknown';
			$traceString.="#$i {$t['file']}({$t['line']}): ";
			if(isset($t['object']) && is_object($t['object']))
				$traceString.=get_class($t['object']).'->';
			$traceString.="{$t['function']}()\n";
		}

		$app=Yii::app();
		if($app instanceof CWebApplication)
		{
			$this->_error=$data=array(
				'code'=>500,
				'type'=>'PHP Error',
				'message'=>$event->message,
				'file'=>$event->file,
				'line'=>$event->line,
				'trace'=>$traceString,
				'source'=>$this->getSourceLines($event->file,$event->line),
			);
			if(!headers_sent())
				header("HTTP/1.0 500 PHP Error");
			if(YII_DEBUG)
				$this->render('exception',$data);
			else
				$this->render('error',$data);
		}
		else
			$app->displayError($event->code,$event->message,$event->file,$event->line);
	}

	/**
	 * @param Exception $exception the uncaught exception
	 * @return array the exact trace where the problem occurs
	 */
	protected function getExactTrace($exception)
	{
		$traces=$exception->getTrace();

		foreach($traces as $trace)
		{
			// property access exception
			if(isset($trace['function']) && ($trace['function']==='__get' || $trace['function']==='__set'))
				return $trace;
		}
		return null;
	}

	/**
	 * Renders the view.
	 * @param string $view the view name (file name without extension).
	 * See {@link getViewFile} for how a view file is located given its name.
	 * @param array $data data to be passed to the view
	 */
	protected function render($view,$data)
	{
		if($view==='error' && $this->errorAction!==null)
			Yii::app()->runController($this->errorAction);
		else
		{
			// additional information to be passed to view
			$data['version']=$this->getVersionInfo();
			$data['time']=time();
			$data['admin']=$this->adminInfo;
			include($this->getViewFile($view,$data['code']));
		}
	}

	/**
	 * Determines which view file should be used.
	 * @param string $view view name (either 'exception' or 'error')
	 * @param integer $code HTTP status code
	 * @return string view file path
	 */
	protected function getViewFile($view,$code)
	{
		$viewPaths=array(
			Yii::app()->getTheme()===null ? null :  Yii::app()->getTheme()->getSystemViewPath(),
			Yii::app() instanceof CWebApplication ? Yii::app()->getSystemViewPath() : null,
			YII_PATH.DIRECTORY_SEPARATOR.'views',
		);

		foreach($viewPaths as $i=>$viewPath)
		{
			if($viewPath!==null)
			{
				 $viewFile=$this->getViewFileInternal($viewPath,$view,$code,$i===2?'en_us':null);
				 if(is_file($viewFile))
				 	 return $viewFile;
			}
		}
	}

	/**
	 * Looks for the view under the specified directory.
	 * @param string $viewPath the directory containing the views
	 * @param string $view view name (either 'exception' or 'error')
	 * @param integer $code HTTP status code
	 * @param string $srcLanguage the language that the view file is in
	 * @return string view file path
	 */
	protected function getViewFileInternal($viewPath,$view,$code,$srcLanguage=null)
	{
		$app=Yii::app();
		if($view==='error')
		{
			$viewFile=$app->findLocalizedFile($viewPath.DIRECTORY_SEPARATOR."error{$code}.php",$srcLanguage);
			if(!is_file($viewFile))
				$viewFile=$app->findLocalizedFile($viewPath.DIRECTORY_SEPARATOR.'error.php',$srcLanguage);
		}
		else
			$viewFile=$app->findLocalizedFile($viewPath.DIRECTORY_SEPARATOR."exception.php",$srcLanguage);
		return $viewFile;
	}

	/**
	 * @return string server version information. If the application is in production mode, nothing is returned.
	 */
	protected function getVersionInfo()
	{
		if(YII_DEBUG)
		{
			$version='<a href="http://www.yiiframework.com/">Yii Framework</a>/'.Yii::getVersion();
			if(isset($_SERVER['SERVER_SOFTWARE']))
				$version=$_SERVER['SERVER_SOFTWARE'].' '.$version;
		}
		else
			$version='';
		return $version;
	}

	/**
	 * Returns the source lines around the error line.
	 * At most {@link maxSourceLines} lines will be returned.
	 * @param string $file source file path
	 * @param integer $line the error line number
	 * @return array source lines around the error line, indxed by line numbers
	 */
	protected function getSourceLines($file,$line)
	{
		// determine the max number of lines to display
		$maxLines=$this->maxSourceLines;
		if($maxLines<1)
			$maxLines=1;
		else if($maxLines>100)
			$maxLines=100;

		$line--;	// adjust line number to 0-based from 1-based
		if($line<0 || ($lines=@file($file))===false || ($lineCount=count($lines))<=$line)
			return array();

		$halfLines=(int)($maxLines/2);
		$beginLine=$line-$halfLines>0?$line-$halfLines:0;
		$endLine=$line+$halfLines<$lineCount?$line+$halfLines:$lineCount-1;

		$sourceLines=array();
		for($i=$beginLine;$i<=$endLine;++$i)
			$sourceLines[$i+1]=$lines[$i];
		return $sourceLines;
	}
}

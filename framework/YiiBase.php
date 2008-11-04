<?php
/**
 * YiiBase class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 * @package system
 * @since 1.0
 */

/**
 * Gets the application start timestamp.
 */
defined('YII_BEGIN_TIME') or define('YII_BEGIN_TIME',microtime(true));
/**
 * This constant defines whether the application should be in debug mode or not. Defaults to false.
 */
defined('YII_DEBUG') or define('YII_DEBUG',false);
/**
 * This constant defines whether exception handling should be enabled. Defaults to true.
 */
defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER',true);
/**
 * This constant defines whether error handling should be enabled. Defaults to true.
 */
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER',true);
/**
 * Defines the Yii framework installation path.
 */
defined('YII_PATH') or define('YII_PATH',dirname(__FILE__));

/**
 * YiiBase is a helper class serving common framework functionalities.
 *
 * Do not use YiiBase directly. Instead, use its child class {@link Yii} where
 * you can customize methods of YiiBase.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system
 * @since 1.0
 */
class YiiBase
{
	private static $_aliases=array('system'=>YII_PATH); // alias => path
	private static $_imports=array();					// alias => class name or directory
	private static $_classes=array();
	private static $_app;
	private static $_logger;


	/**
	 * @return string the version of Yii framework
	 */
	public static function getVersion()
	{
		return '1.0rc';
	}

	/**
	 * Creates a Web application instance.
	 * @param mixed application configuration.
	 * If a string, it is treated as the path of the file that contains the configuration;
	 * If an array or CConfiguration, it is the actual configuration information.
	 * Please make sure you specify the {@link CApplication::basePath basePath} property in the configuration,
	 * which should point to the directory containing all application logic, template and data.
	 * If not, the directory will be defaulted to 'protected'.
	 */
	public static function createWebApplication($config=null)
	{
		return new CWebApplication($config);
	}

	/**
	 * Creates a console application instance.
	 * @param mixed application configuration.
	 * If a string, it is treated as the path of the file that contains the configuration;
	 * If an array or CConfiguration, it is the actual configuration information.
	 * Please make sure you specify the {@link CApplication::basePath basePath} property in the configuration,
	 * which should point to the directory containing all application logic, template and data.
	 * If not, the directory will be defaulted to 'protected'.
	 */
	public static function createConsoleApplication($config=null)
	{
		return new CConsoleApplication($config);
	}

	/**
	 * @return CApplication the application singleton, null if the singleton has not been created yet.
	 */
	public static function app()
	{
		return self::$_app;
	}

	/**
	 * Stores the application instance in the class static member.
	 * This method helps implement a singleton pattern for CApplication.
	 * Repeated invocation of this method or the CApplication constructor
	 * will cause the throw of an exception.
	 * To retrieve the application instance, use {@link app()}.
	 * @param CApplication the application instance. If this is null, the existing
	 * application singleton will be removed.
	 * @throws CException if multiple application instances are registered.
	 */
	public static function setApplication($app)
	{
		if(self::$_app===null || $app===null)
			self::$_app=$app;
		else
			throw new CException(Yii::t('yii','Yii application can only be created once.'));
	}

	/**
	 * @return string the path of the framework
	 */
	public static function getFrameworkPath()
	{
		return YII_PATH;
	}

	/**
	 * Creates a component with the specified type.
	 * A component type can be either the component class name
	 * or an alias referring to the path of the component class file.
	 * For example, 'MyClass', 'application.controllers.MyController' are both
	 * valid component type.
	 * This method can also pass parameters to the component constructor.
	 * All parameters passed to this method except the first one (the component type)
	 * will be supplied as component constructor parameters.
	 * @param string component type
	 * @return CComponent component instance of the specified type
	 * @throws CException if the component type is unknown
	 */
	public static function createComponent($type)
	{
		$type=self::import($type,true);
		if(($n=func_num_args())>1)
		{
			$args=func_get_args();
			for($s='$args[1]',$i=2;$i<$n;++$i)
				$s.=",\$args[$i]";
			eval("\$component=new $type($s);");
			return $component;
		}
		else
			return new $type;
	}

	/**
	 * Imports the definition of a class or a directory of class files.
	 *
	 * Path aliases are used to refer to the class file or directory being imported.
	 * If importing a path alias ending with '.*', the alias is considered as a directory
	 * which will be added to the PHP include paths; Otherwise, the alias is translated
	 * to the path of a class file which is included when needed.
	 *
	 * For example, importing 'system.web.*' will add the 'web' directory of the framework
	 * to the PHP include paths; while importing 'system.web.CController' will include
	 * the class file 'web/CController.php' when needed.
	 *
	 * The same alias can be imported multiple times, but only the first time is effective.
	 *
	 * @param string path alias to be imported
	 * @param boolean whether to include the class file immediately. If false, the class file
	 * will be included only when the class is being used.
	 * @return string the class name or the directory that this alias refers to
	 * @throws CException if the alias is invalid
	 */
	public static function import($alias,$forceInclude=false)
	{
		if(isset(self::$_imports[$alias]))  // previously imported
			return self::$_imports[$alias];

		if(isset(self::$_coreClasses[$alias]) || ($pos=strrpos($alias,'.'))===false)  // a simple class name
		{
			self::$_imports[$alias]=$alias;
			if($forceInclude && !class_exists($alias,false))
			{
				if(isset(self::$_coreClasses[$alias])) // a core class
					require_once(YII_PATH.self::$_coreClasses[$alias]);
				else
					require_once($alias.'.php');
			}
			return $alias;
		}

		if(($className=(string)substr($alias,$pos+1))!=='*' && class_exists($className,false))
			return self::$_imports[$alias]=$className;

		if(($path=self::getPathOfAlias($alias))!==false)
		{
			if($className!=='*')
			{
				self::$_imports[$alias]=$className;
				if($forceInclude)
					require_once($path.'.php');
				else
					self::$_classes[$className]=$path.'.php';
				return $className;
			}
			else  // a directory
			{
				set_include_path(get_include_path().PATH_SEPARATOR.$path);
				return self::$_imports[$alias]=$path;
			}
		}
		else
			throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing directory or file.',
				array('{alias}'=>$alias)));
	}

	/**
	 * Translates an alias into a file path.
	 * Note, this method does not ensure the existence of the resulting file path.
	 * It only checks if the root alias is valid or not.
	 * @param string alias (e.g. system.web.CController)
	 * @return mixed file path corresponding to the alias, false if the alias is invalid.
	 */
	public static function getPathOfAlias($alias)
	{
		if(isset(self::$_aliases[$alias]))
			return self::$_aliases[$alias];
		else if(($pos=strpos($alias,'.'))!==false)
		{
			$rootAlias=substr($alias,0,$pos);
			if(isset(self::$_aliases[$rootAlias]))
				return self::$_aliases[$alias]=rtrim(self::$_aliases[$rootAlias].DIRECTORY_SEPARATOR.str_replace('.',DIRECTORY_SEPARATOR,substr($alias,$pos+1)),'*'.DIRECTORY_SEPARATOR);
		}
		return self::$_aliases[$alias]=false;
	}

	/**
	 * Create a path alias.
	 * @param string alias to the path
	 * @param string the path corresponding to the alias. If this is null, the corresponding
	 * path alias will be removed.
	 * @throws CException if the alias is already defined or the path is not valid directory
	 */
	public static function setPathOfAlias($alias,$path)
	{
		if($path===null)
			unset(self::$_aliases[$alias]);
		else if(!isset(self::$_aliases[$alias]) && ($rp=realpath($path))!==false)
			self::$_aliases[$alias]=rtrim($rp,'\\/');
		else if(isset(self::$_aliases[$alias]))
			throw new CException(Yii::t('yii','Path alias "{alias}" is redefined.',
				array('{alias}'=>$alias)));
		else
			throw new CException(Yii::t('yii','Path alias "{alias}" points to an invalid directory "{path}".',
				array('{alias}'=>$alias, '{path}'=>$path)));
	}

	/**
	 * Class autoload loader.
	 * This method is provided to be invoked within an __autoload() magic method.
	 * @param string class name
	 */
	public static function autoload($className)
	{
		// use include_once so that the error PHP file may appear
		if(isset(self::$_coreClasses[$className]))
			include_once(YII_PATH.self::$_coreClasses[$className]);
		else if(isset(self::$_classes[$className]))
			include_once(self::$_classes[$className]);
		else
			include_once($className.'.php');
	}

	/**
	 * Writes a trace message.
	 * This method will only log a message when the application is in debug mode.
	 * @param string message to be logged
	 * @param string category of the message
	 * @see log
	 */
	public static function trace($msg,$category='application')
	{
		if(YII_DEBUG)
			self::log($msg,CLogger::LEVEL_TRACE,$category);
	}

	/**
	 * Logs a message.
	 * Messages logged by this method may be retrieved via {@link CLogger::getLogs}
	 * and may be recorded in different media, such as file, email, database, using
	 * {@link CLogRouter}.
	 * @param string message to be logged
	 * @param string level of the message (e.g. 'trace', 'warning', 'error'). It is case-insensitive.
	 * @param string category of the message (e.g. 'system.web'). It is case-insensitive.
	 */
	public static function log($msg,$level=CLogger::LEVEL_INFO,$category='application')
	{
		if(self::$_logger===null)
			self::$_logger=new CLogger;
		self::$_logger->log($msg,$level,$category);
	}

	/**
	 * Marks the begin of a code block for profiling.
	 * This has to be matched with a call to {@link endProfile()} with the same token.
	 * The begin- and end- calls must also be properly nested, e.g.,
	 * <pre>
	 * Yii::beginProfile('block1');
	 * Yii::beginProfile('block2');
	 * Yii::endProfile('block2');
	 * Yii::endProfile('block1');
	 * </pre>
	 * The following sequence is not valid:
	 * <pre>
	 * Yii::beginProfile('block1');
	 * Yii::beginProfile('block2');
	 * Yii::endProfile('block1');
	 * Yii::endProfile('block2');
	 * </pre>
	 * @param string token for the code block
	 * @param string the category of this log message
	 * @see endProfile
	 */
	public static function beginProfile($token,$category='application')
	{
		self::log('begin:'.$token,CLogger::LEVEL_PROFILE,$category);
	}

	/**
	 * Marks the end of a code block for profiling.
	 * This has to be matched with a previous call to {@link beginProfile()} with the same token.
	 * @param string token for the code block
	 * @param string the category of this log message
	 * @see beginProfile
	 */
	public static function endProfile($token,$category='application')
	{
		self::log('end:'.$token,CLogger::LEVEL_PROFILE,$category);
	}

	/**
	 * @return CLogger message logger
	 */
	public static function getLogger()
	{
		if(self::$_logger!==null)
			return self::$_logger;
		else
			return self::$_logger=new CLogger;
	}

	/**
	 * @return string a string that can be displayed on your Web page showing Powered-by-Yii information
	 */
	public static function powered()
	{
		return 'Powered by <a href="http://www.yiiframework.com/">Yii Framework</a>.';
	}

	/**
	 * Translates a message to the {@link CApplication::getLanguage application language}.
	 * @param string message category. Please use only word letters. Note, category 'yii' is
	 * reserved for Yii framework core code use. See {@link CPhpMessageSource} for
	 * more interpretation about message category.
	 * @param string the original message
	 * @param array parameters to be applied to the message using <code>strtr</code>.
	 * @param string which message source application component to use.
	 * Defaults to null, meaning using 'coreMessages' for messages belonging to
	 * the 'yii' category and using 'messages' for the rest messages.
	 * @return string the translated message
	 * @see CMessageSource
	 */
	public static function t($category,$message,$params=array(),$source=null)
	{
		if(self::$_app!==null)
		{
			if($source===null)
				$source=$category==='yii'?'coreMessages':'messages';
			if(($source=self::$_app->getComponent($source))!==null)
				$message=$source->translate($category,$message);
		}
		return $params!==array() ? strtr($message,$params) : $message;
	}

	/**
	 * @var array class map for core Yii classes.
	 * NOTE, DO NOT MODIFY THIS ARRAY MANUALLY. IF YOU CHANGE OR ADD SOME CORE CLASSES,
	 * PLEASE RUN 'build autoload' COMMAND TO UPDATE THIS ARRAY.
	 */
	private static $_coreClasses=array(
		'CApcCache' => '/caching/CApcCache.php',
		'CCache' => '/caching/CCache.php',
		'CDbCache' => '/caching/CDbCache.php',
		'CMemCache' => '/caching/CMemCache.php',
		'CCacheDependency' => '/caching/dependencies/CCacheDependency.php',
		'CChainedCacheDependency' => '/caching/dependencies/CChainedCacheDependency.php',
		'CDbCacheDependency' => '/caching/dependencies/CDbCacheDependency.php',
		'CDirectoryCacheDependency' => '/caching/dependencies/CDirectoryCacheDependency.php',
		'CFileCacheDependency' => '/caching/dependencies/CFileCacheDependency.php',
		'CGlobalStateCacheDependency' => '/caching/dependencies/CGlobalStateCacheDependency.php',
		'CAttributeCollection' => '/collections/CAttributeCollection.php',
		'CConfiguration' => '/collections/CConfiguration.php',
		'CList' => '/collections/CList.php',
		'CMap' => '/collections/CMap.php',
		'CQueue' => '/collections/CQueue.php',
		'CStack' => '/collections/CStack.php',
		'CTypedList' => '/collections/CTypedList.php',
		'CConsoleApplication' => '/console/CConsoleApplication.php',
		'CConsoleCommand' => '/console/CConsoleCommand.php',
		'CConsoleCommandRunner' => '/console/CConsoleCommandRunner.php',
		'CHelpCommand' => '/console/CHelpCommand.php',
		'CApplication' => '/core/CApplication.php',
		'CApplicationComponent' => '/core/CApplicationComponent.php',
		'CComponent' => '/core/CComponent.php',
		'CErrorHandler' => '/core/CErrorHandler.php',
		'CException' => '/core/CException.php',
		'CHttpException' => '/core/CHttpException.php',
		'CModel' => '/core/CModel.php',
		'CSecurityManager' => '/core/CSecurityManager.php',
		'CStatePersister' => '/core/CStatePersister.php',
		'CDbLogRoute' => '/core/log/CDbLogRoute.php',
		'CEmailLogRoute' => '/core/log/CEmailLogRoute.php',
		'CFileLogRoute' => '/core/log/CFileLogRoute.php',
		'CLogRoute' => '/core/log/CLogRoute.php',
		'CLogRouter' => '/core/log/CLogRouter.php',
		'CLogger' => '/core/log/CLogger.php',
		'CProfileLogRoute' => '/core/log/CProfileLogRoute.php',
		'CWebLogRoute' => '/core/log/CWebLogRoute.php',
		'CDbCommand' => '/db/CDbCommand.php',
		'CDbConnection' => '/db/CDbConnection.php',
		'CDbDataReader' => '/db/CDbDataReader.php',
		'CDbException' => '/db/CDbException.php',
		'CDbTransaction' => '/db/CDbTransaction.php',
		'CActiveFinder' => '/db/ar/CActiveFinder.php',
		'CActiveRecord' => '/db/ar/CActiveRecord.php',
		'CDbColumnSchema' => '/db/schema/CDbColumnSchema.php',
		'CDbCommandBuilder' => '/db/schema/CDbCommandBuilder.php',
		'CDbCriteria' => '/db/schema/CDbCriteria.php',
		'CDbSchema' => '/db/schema/CDbSchema.php',
		'CDbTableSchema' => '/db/schema/CDbTableSchema.php',
		'CMysqlColumnSchema' => '/db/schema/mysql/CMysqlColumnSchema.php',
		'CMysqlSchema' => '/db/schema/mysql/CMysqlSchema.php',
		'CMysqlTableSchema' => '/db/schema/mysql/CMysqlTableSchema.php',
		'CPgsqlColumnSchema' => '/db/schema/pgsql/CPgsqlColumnSchema.php',
		'CPgsqlSchema' => '/db/schema/pgsql/CPgsqlSchema.php',
		'CPgsqlTableSchema' => '/db/schema/pgsql/CPgsqlTableSchema.php',
		'CSqliteColumnSchema' => '/db/schema/sqlite/CSqliteColumnSchema.php',
		'CSqliteCommandBuilder' => '/db/schema/sqlite/CSqliteCommandBuilder.php',
		'CSqliteSchema' => '/db/schema/sqlite/CSqliteSchema.php',
		'CDateFormatter' => '/i18n/CDateFormatter.php',
		'CDbMessageSource' => '/i18n/CDbMessageSource.php',
		'CGettextMessageSource' => '/i18n/CGettextMessageSource.php',
		'CLocale' => '/i18n/CLocale.php',
		'CMessageSource' => '/i18n/CMessageSource.php',
		'CNumberFormatter' => '/i18n/CNumberFormatter.php',
		'CPhpMessageSource' => '/i18n/CPhpMessageSource.php',
		'CGettextFile' => '/i18n/gettext/CGettextFile.php',
		'CGettextMoFile' => '/i18n/gettext/CGettextMoFile.php',
		'CGettextPoFile' => '/i18n/gettext/CGettextPoFile.php',
		'CDateParser' => '/utils/CDateParser.php',
		'CFileHelper' => '/utils/CFileHelper.php',
		'CTimestamp' => '/utils/CTimestamp.php',
		'CVarDumper' => '/utils/CVarDumper.php',
		'CCaptchaValidator' => '/validators/CCaptchaValidator.php',
		'CCompareValidator' => '/validators/CCompareValidator.php',
		'CEmailValidator' => '/validators/CEmailValidator.php',
		'CFilterValidator' => '/validators/CFilterValidator.php',
		'CInlineValidator' => '/validators/CInlineValidator.php',
		'CNumberValidator' => '/validators/CNumberValidator.php',
		'CRangeValidator' => '/validators/CRangeValidator.php',
		'CRegularExpressionValidator' => '/validators/CRegularExpressionValidator.php',
		'CRequiredValidator' => '/validators/CRequiredValidator.php',
		'CStringValidator' => '/validators/CStringValidator.php',
		'CTypeValidator' => '/validators/CTypeValidator.php',
		'CUniqueValidator' => '/validators/CUniqueValidator.php',
		'CUrlValidator' => '/validators/CUrlValidator.php',
		'CValidator' => '/validators/CValidator.php',
		'CAssetManager' => '/web/CAssetManager.php',
		'CBaseController' => '/web/CBaseController.php',
		'CCacheHttpSession' => '/web/CCacheHttpSession.php',
		'CClientScript' => '/web/CClientScript.php',
		'CController' => '/web/CController.php',
		'CDbHttpSession' => '/web/CDbHttpSession.php',
		'CExtController' => '/web/CExtController.php',
		'CFormModel' => '/web/CFormModel.php',
		'CHttpCookie' => '/web/CHttpCookie.php',
		'CHttpRequest' => '/web/CHttpRequest.php',
		'CHttpSession' => '/web/CHttpSession.php',
		'COutputEvent' => '/web/COutputEvent.php',
		'CPagination' => '/web/CPagination.php',
		'CTheme' => '/web/CTheme.php',
		'CThemeManager' => '/web/CThemeManager.php',
		'CUrlManager' => '/web/CUrlManager.php',
		'CWebApplication' => '/web/CWebApplication.php',
		'CWebService' => '/web/CWebService.php',
		'CWsdlGenerator' => '/web/CWsdlGenerator.php',
		'CAction' => '/web/actions/CAction.php',
		'CCaptchaAction' => '/web/actions/CCaptchaAction.php',
		'CInlineAction' => '/web/actions/CInlineAction.php',
		'CViewAction' => '/web/actions/CViewAction.php',
		'CWebServiceAction' => '/web/actions/CWebServiceAction.php',
		'CAuthAssignment' => '/web/auth/CAuthAssignment.php',
		'CAuthItem' => '/web/auth/CAuthItem.php',
		'CAuthManager' => '/web/auth/CAuthManager.php',
		'CBaseUserIdentity' => '/web/auth/CBaseUserIdentity.php',
		'CDbAuthManager' => '/web/auth/CDbAuthManager.php',
		'CPhpAuthManager' => '/web/auth/CPhpAuthManager.php',
		'CUserIdentity' => '/web/auth/CUserIdentity.php',
		'CWebUser' => '/web/auth/CWebUser.php',
		'CAccessControlFilter' => '/web/filters/CAccessControlFilter.php',
		'CFilter' => '/web/filters/CFilter.php',
		'CFilterChain' => '/web/filters/CFilterChain.php',
		'CInlineFilter' => '/web/filters/CInlineFilter.php',
		'CHtml' => '/web/helpers/CHtml.php',
		'CJSON' => '/web/helpers/CJSON.php',
		'CJavaScript' => '/web/helpers/CJavaScript.php',
		'CPradoViewRenderer' => '/web/renderers/CPradoViewRenderer.php',
		'CViewRenderer' => '/web/renderers/CViewRenderer.php',
		'CAutoComplete' => '/web/widgets/CAutoComplete.php',
		'CCaptcha' => '/web/widgets/CCaptcha.php',
		'CClipWidget' => '/web/widgets/CClipWidget.php',
		'CContentDecorator' => '/web/widgets/CContentDecorator.php',
		'CFilterWidget' => '/web/widgets/CFilterWidget.php',
		'CFlexWidget' => '/web/widgets/CFlexWidget.php',
		'CHtmlPurifier' => '/web/widgets/CHtmlPurifier.php',
		'CInputWidget' => '/web/widgets/CInputWidget.php',
		'CMaskedTextField' => '/web/widgets/CMaskedTextField.php',
		'CMultiFileUpload' => '/web/widgets/CMultiFileUpload.php',
		'COutputCache' => '/web/widgets/COutputCache.php',
		'COutputProcessor' => '/web/widgets/COutputProcessor.php',
		'CTextHighlighter' => '/web/widgets/CTextHighlighter.php',
		'CTreeView' => '/web/widgets/CTreeView.php',
		'CWidget' => '/web/widgets/CWidget.php',
		'CBasePager' => '/web/widgets/pagers/CBasePager.php',
		'CLinkPager' => '/web/widgets/pagers/CLinkPager.php',
		'CListPager' => '/web/widgets/pagers/CListPager.php',
	);
}

spl_autoload_register(array('YiiBase','autoload'));
require_once(YII_PATH.'/core/interfaces.php');

<?php

/**
 * Yee Requirement Checker script
 *
 * This script will check if your system meets the requirements for running
 * Yee-powered Web applications.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yeeframework.com/
 * @copyright 2008-2013 Yee Software LLC
 * @license http://www.yeeframework.com/license/
 * @package system
 * @since 1.0
 */
/**
 * @var array List of requirements (name, required or not, result, used by, memo)
 */
$requirements=array(
	array(
		t('yee','PHP version'),
		true,
		version_compare(PHP_VERSION,"5.1.0",">="),
		'<a href="http://www.yeeframework.com">Yee Framework</a>',
		t('yee','PHP 5.1.0 or higher is required.')),
	array(
		t('yee','$_SERVER variable'),
		true,
		'' === $message=checkServerVar(),
		'<a href="http://www.yeeframework.com">Yee Framework</a>',
		$message),
	array(
		t('yee','Reflection extension'),
		true,
		class_exists('Reflection',false),
		'<a href="http://www.yeeframework.com">Yee Framework</a>',
		''),
	array(
		t('yee','PCRE extension'),
		true,
		extension_loaded("pcre"),
		'<a href="http://www.yeeframework.com">Yee Framework</a>',
		''),
	array(
		t('yee','SPL extension'),
		true,
		extension_loaded("SPL"),
		'<a href="http://www.yeeframework.com">Yee Framework</a>',
		''),
	array(
		t('yee','DOM extension'),
		false,
		class_exists("DOMDocument",false),
		'<a href="http://www.yeeframework.com/doc/api/CHtmlPurifier">CHtmlPurifier</a>, <a href="http://www.yeeframework.com/doc/api/CWsdlGenerator">CWsdlGenerator</a>',
		''),
	array(
		t('yee','PDO extension'),
		false,
		extension_loaded('pdo'),
		t('yee','All <a href="http://www.yeeframework.com/doc/api/#system.db">DB-related classes</a>'),
		''),
	array(
		t('yee','PDO SQLite extension'),
		false,
		extension_loaded('pdo_sqlite'),
		t('yee','All <a href="http://www.yeeframework.com/doc/api/#system.db">DB-related classes</a>'),
		t('yee','Required for SQLite database.')),
	array(
		t('yee','PDO MySQL extension'),
		false,
		extension_loaded('pdo_mysql'),
		t('yee','All <a href="http://www.yeeframework.com/doc/api/#system.db">DB-related classes</a>'),
		t('yee','Required for MySQL database.')),
	array(
		t('yee','PDO PostgreSQL extension'),
		false,
		extension_loaded('pdo_pgsql'),
		t('yee','All <a href="http://www.yeeframework.com/doc/api/#system.db">DB-related classes</a>'),
		t('yee','Required for PostgreSQL database.')),
	array(
		t('yee','PDO Oracle extension'),
		false,
		extension_loaded('pdo_oci'),
		t('yee','All <a href="http://www.yeeframework.com/doc/api/#system.db">DB-related classes</a>'),
		t('yee','Required for Oracle database.')),
	array(
		t('yee','PDO MSSQL extension (pdo_mssql)'),
		false,
		extension_loaded('pdo_mssql'),
		t('yee','All <a href="http://www.yeeframework.com/doc/api/#system.db">DB-related classes</a>'),
		t('yee','Required for MSSQL database from MS Windows')),
	array(
		t('yee','PDO MSSQL extension (pdo_dblib)'),
		false,
		extension_loaded('pdo_dblib'),
		t('yee','All <a href="http://www.yeeframework.com/doc/api/#system.db">DB-related classes</a>'),
		t('yee','Required for MSSQL database from GNU/Linux or other UNIX.')),
	array(
		t('yee','PDO MSSQL extension (<a href="http://sqlsrvphp.codeplex.com/">pdo_sqlsrv</a>)'),
		false,
		extension_loaded('pdo_sqlsrv'),
		t('yee','All <a href="http://www.yeeframework.com/doc/api/#system.db">DB-related classes</a>'),
		t('yee','Required for MSSQL database with the driver provided by Microsoft.')),
	array(
		t('yee','PDO ODBC extension'),
		false,
		extension_loaded('pdo_odbc'),
		t('yee','All <a href="http://www.yeeframework.com/doc/api/#system.db">DB-related classes</a>'),
		t('yee','Required in case database interaction will be through ODBC layer.')),
	array(
		t('yee','Memcache extension'),
		false,
		extension_loaded("memcache") || extension_loaded("memcached"),
		'<a href="http://www.yeeframework.com/doc/api/CMemCache">CMemCache</a>',
		extension_loaded("memcached") ? t('yee', 'To use memcached set <a href="http://www.yeeframework.com/doc/api/CMemCache#useMemcached-detail">CMemCache::useMemcached</a> to <code>true</code>.') : ''),
	array(
		t('yee','APC extension'),
		false,
		extension_loaded("apc"),
		'<a href="http://www.yeeframework.com/doc/api/CApcCache">CApcCache</a>',
		''),
	array(
		t('yee','Mcrypt extension'),
		false,
		extension_loaded("mcrypt"),
		'<a href="http://www.yeeframework.com/doc/api/CSecurityManager">CSecurityManager</a>',
		t('yee','Required by encrypt and decrypt methods.')),
	array(
		t('yee','crypt() CRYPT_BLOWFISH option'),
		false,
		function_exists('crypt') && defined('CRYPT_BLOWFISH') && CRYPT_BLOWFISH,
		'<a href="http://www.yeeframework.com/doc/api/1.1/CPasswordHelper">CPasswordHelper</a>',
		t('yee','Required for secure password storage.')),
	array(
		t('yee','SOAP extension'),
		false,
		extension_loaded("soap"),
		'<a href="http://www.yeeframework.com/doc/api/CWebService">CWebService</a>, <a href="http://www.yeeframework.com/doc/api/CWebServiceAction">CWebServiceAction</a>',
		''),
	array(
		t('yee','GD extension with<br />FreeType support<br />or ImageMagick<br />extension with<br />PNG support'),
		false,
		'' === $message=checkCaptchaSupport(),
		'<a href="http://www.yeeframework.com/doc/api/CCaptchaAction">CCaptchaAction</a>',
		$message),
	array(
		t('yee','Ctype extension'),
		false,
		extension_loaded("ctype"),
		'<a href="http://www.yeeframework.com/doc/api/CDateFormatter">CDateFormatter</a>, <a href="http://www.yeeframework.com/doc/api/CDateFormatter">CDateTimeParser</a>, <a href="http://www.yeeframework.com/doc/api/CTextHighlighter">CTextHighlighter</a>, <a href="http://www.yeeframework.com/doc/api/CHtmlPurifier">CHtmlPurifier</a>',
		''
	),
	array(
		t('yee','Fileinfo extension'),
		false,
		extension_loaded("fileinfo"),
		'<a href="http://www.yeeframework.com/doc/api/CFileValidator">CFileValidator</a>',
		t('yee','Required for MIME-type validation')
	),
);

function checkServerVar()
{
	$vars=array('HTTP_HOST','SERVER_NAME','SERVER_PORT','SCRIPT_NAME','SCRIPT_FILENAME','PHP_SELF','HTTP_ACCEPT','HTTP_USER_AGENT');
	$missing=array();
	foreach($vars as $var)
	{
		if(!isset($_SERVER[$var]))
			$missing[]=$var;
	}
	if(!empty($missing))
		return t('yee','$_SERVER does not have {vars}.',array('{vars}'=>implode(', ',$missing)));

	if(realpath($_SERVER["SCRIPT_FILENAME"]) !== realpath(__FILE__))
		return t('yee','$_SERVER["SCRIPT_FILENAME"] must be the same as the entry script file path.');

	if(!isset($_SERVER["REQUEST_URI"]) && isset($_SERVER["QUERY_STRING"]))
		return t('yee','Either $_SERVER["REQUEST_URI"] or $_SERVER["QUERY_STRING"] must exist.');

	if(!isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PHP_SELF"],$_SERVER["SCRIPT_NAME"]) !== 0)
		return t('yee','Unable to determine URL path info. Please make sure $_SERVER["PATH_INFO"] (or $_SERVER["PHP_SELF"] and $_SERVER["SCRIPT_NAME"]) contains proper value.');

	return '';
}

function checkCaptchaSupport()
{
	if(extension_loaded('imagick'))
	{
		$imagick=new Imagick();
		$imagickFormats=$imagick->queryFormats('PNG');
	}
	if(extension_loaded('gd'))
		$gdInfo=gd_info();
	if(isset($imagickFormats) && in_array('PNG',$imagickFormats))
		return '';
	elseif(isset($gdInfo))
	{
		if($gdInfo['FreeType Support'])
			return '';
		return t('yee','GD installed,<br />FreeType support not installed');
	}
	return t('yee','GD or ImageMagick not installed');
}

function getYeeVersion()
{
	$coreFile=dirname(__FILE__).'/../framework/YeeBase.php';
	if(is_file($coreFile))
	{
		$contents=file_get_contents($coreFile);
		$matches=array();
		if(preg_match('/public static function getVersion.*?return \'(.*?)\'/ms',$contents,$matches) > 0)
			return $matches[1];
	}
	return '';
}

/**
 * Returns a localized message according to user preferred language.
 * @param string message category
 * @param string message to be translated
 * @param array parameters to be applied to the translated message
 * @return string translated message
 */
function t($category,$message,$params=array())
{
	static $messages;

	if($messages === null)
	{
		$messages=array();
		if(($lang=getPreferredLanguage()) !== false)
		{
			$file=dirname(__FILE__)."/messages/$lang/yee.php";
			if(is_file($file))
				$messages=include($file);
		}
	}

	if(empty($message))
		return $message;

	if(isset($messages[$message]) && $messages[$message] !== '')
		$message=$messages[$message];

	return $params !== array() ? strtr($message,$params) : $message;
}

function getPreferredLanguage()
{
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && ($n=preg_match_all('/([\w\-]+)\s*(;\s*q\s*=\s*(\d*\.\d*))?/',$_SERVER['HTTP_ACCEPT_LANGUAGE'],$matches)) > 0)
	{
		$languages=array();
		for($i=0; $i < $n; ++$i)
			$languages[$matches[1][$i]]=empty($matches[3][$i]) ? 1.0 : floatval($matches[3][$i]);
		arsort($languages);
		foreach($languages as $language=>$pref)
		{
			$lang=strtolower(str_replace('-','_',$language));
			if (preg_match("/^en\_?/", $lang))
				return false;
			if (!is_file($viewFile=dirname(__FILE__)."/views/$lang/index.php"))
				$lang=false;
			else
				break;
		}
		return $lang;
	}
	return false;
}

function getServerInfo()
{
	$info[]=isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
	$info[]='<a href="http://www.yeeframework.com/">Yee Framework</a>/'.getYeeVersion();
	$info[]=@strftime('%Y-%m-%d %H:%M',time());

	return implode(' ',$info);
}

function renderFile($_file_,$_params_=array())
{
	extract($_params_);
	require($_file_);
}

$result=1;  // 1: all pass, 0: fail, -1: pass with warnings

foreach($requirements as $i=>$requirement)
{
	if($requirement[1] && !$requirement[2])
		$result=0;
	else if($result > 0 && !$requirement[1] && !$requirement[2])
		$result=-1;
	if($requirement[4] === '')
		$requirements[$i][4]='&nbsp;';
}

$lang=getPreferredLanguage();
$viewFile=dirname(__FILE__)."/views/$lang/index.php";
if(!is_file($viewFile))
	$viewFile=dirname(__FILE__).'/views/index.php';

renderFile($viewFile,array(
	'requirements'=>$requirements,
	'result'=>$result,
	'serverInfo'=>getServerInfo()));


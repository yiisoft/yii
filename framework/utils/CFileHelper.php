<?php
/**
 * CFileHelper class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFileHelper provides a set of helper methods for common file system operations.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.utils
 * @since 1.0
 */
class CFileHelper
{
	/**
	 * Returns the extension name of a file path.
	 * For example, the path "path/to/something.php" would return "php".
	 * @param string $path the file path
	 * @return string the extension name without the dot character.
	 * @since 1.1.2
	 */
	public static function getExtension($path)
	{
		return pathinfo($path,PATHINFO_EXTENSION);
	}

	/**
	 * Copies a directory recursively as another.
	 * If the destination directory does not exist, it will be created recursively.
	 * @param string $src the source directory
	 * @param string $dst the destination directory
	 * @param array $options options for directory copy. Valid options are:
	 * <ul>
	 * <li>fileTypes: array, list of file name suffix (without dot). Only files with these suffixes will be copied.</li>
	 * <li>exclude: array, list of directory and file exclusions. Each exclusion can be either a name or a path.
	 * If a file or directory name or path matches the exclusion, it will not be copied. For example, an exclusion of
	 * '.svn' will exclude all files and directories whose name is '.svn'. And an exclusion of '/a/b' will exclude
	 * file or directory '$src/a/b'. Note, that '/' should be used as separator regardless of the value of the DIRECTORY_SEPARATOR constant.
	 * </li>
	 * <li>level: integer, recursion depth, default=-1.
	 * Level -1 means copying all directories and files under the directory;
	 * Level 0 means copying only the files DIRECTLY under the directory;
	 * level N means copying those directories that are within N levels.
 	 * </li>
	 * <li>newDirMode - the permission to be set for newly copied directories (defaults to 0777);</li>
	 * <li>newFileMode - the permission to be set for newly copied files (defaults to the current environment setting).</li>
	 * </ul>
	 */
	public static function copyDirectory($src,$dst,$options=array())
	{
		$fileTypes=array();
		$exclude=array();
		$level=-1;
		extract($options);
		if(!is_dir($dst))
			self::createDirectory($dst,isset($options['newDirMode'])?$options['newDirMode']:null,true);

		self::copyDirectoryRecursive($src,$dst,'',$fileTypes,$exclude,$level,$options);
	}

	/**
	 * Removes a directory recursively.
	 * @param string $directory to be deleted recursively.
	 * @param array $options for the directory removal. Valid options are:
	 * <ul>
	 * <li>traverseSymlinks: boolean, whether symlinks to the directories should be traversed too.
	 * Defaults to `false`, meaning that the content of the symlinked directory would not be deleted.
	 * Only symlink would be removed in that default case.</li>
	 * </ul>
	 * Note, options parameter is available since 1.1.16
	 * @since 1.1.14
	 */
	public static function removeDirectory($directory,$options=array())
	{
		if(!isset($options['traverseSymlinks']))
			$options['traverseSymlinks']=false;
		$items=glob($directory.DIRECTORY_SEPARATOR.'{,.}*',GLOB_MARK | GLOB_BRACE);
		foreach($items as $item)
		{
			if(basename($item)=='.' || basename($item)=='..')
				continue;
			if(substr($item,-1)==DIRECTORY_SEPARATOR)
			{
				if(!$options['traverseSymlinks'] && is_link(rtrim($item,DIRECTORY_SEPARATOR)))
					unlink(rtrim($item,DIRECTORY_SEPARATOR));
				else
					self::removeDirectory($item,$options);
			}
			else
				unlink($item);
		}
		if(is_dir($directory=rtrim($directory,'\\/')))
		{
			if(is_link($directory))
				unlink($directory);
			else
				rmdir($directory);
		}
	}

	/**
	 * Returns the files found under the specified directory and subdirectories.
	 * @param string $dir the directory under which the files will be looked for
	 * @param array $options options for file searching. Valid options are:
	 * <ul>
	 * <li>fileTypes: array, list of file name suffix (without dot). Only files with these suffixes will be returned.</li>
	 * <li>exclude: array, list of directory and file exclusions. Each exclusion can be either a name or a path.
	 * If a file or directory name or path matches the exclusion, it will not be copied. For example, an exclusion of
	 * '.svn' will exclude all files and directories whose name is '.svn'. And an exclusion of '/a/b' will exclude
	 * file or directory '$src/a/b'. Note, that '/' should be used as separator regardless of the value of the DIRECTORY_SEPARATOR constant.
	 * </li>
	 * <li>level: integer, recursion depth, default=-1.
	 * Level -1 means searching for all directories and files under the directory;
	 * Level 0 means searching for only the files DIRECTLY under the directory;
	 * level N means searching for those directories that are within N levels.
 	 * </li>
 	 * <li>absolutePaths: boolean, whether to return absolute paths or relative ones, defaults to true.</li>
	 * </ul>
	 * @return array files found under the directory. The file list is sorted.
	 */
	public static function findFiles($dir,$options=array())
	{
		$fileTypes=array();
		$exclude=array();
		$level=-1;
		$absolutePaths=true;
		extract($options);
		$list=self::findFilesRecursive($dir,'',$fileTypes,$exclude,$level,$absolutePaths);
		sort($list);
		return $list;
	}

	/**
	 * Copies a directory.
	 * This method is mainly used by {@link copyDirectory}.
	 * @param string $src the source directory
	 * @param string $dst the destination directory
	 * @param string $base the path relative to the original source directory
	 * @param array $fileTypes list of file name suffix (without dot). Only files with these suffixes will be copied.
	 * @param array $exclude list of directory and file exclusions. Each exclusion can be either a name or a path.
	 * If a file or directory name or path matches the exclusion, it will not be copied. For example, an exclusion of
	 * '.svn' will exclude all files and directories whose name is '.svn'. And an exclusion of '/a/b' will exclude
	 * file or directory '$src/a/b'. Note, that '/' should be used as separator regardless of the value of the DIRECTORY_SEPARATOR constant.
	 * @param integer $level recursion depth. It defaults to -1.
	 * Level -1 means copying all directories and files under the directory;
	 * Level 0 means copying only the files DIRECTLY under the directory;
	 * level N means copying those directories that are within N levels.
	 * @param array $options additional options. The following options are supported:
	 * newDirMode - the permission to be set for newly copied directories (defaults to 0777);
	 * newFileMode - the permission to be set for newly copied files (defaults to the current environment setting).
	 * @throws Exception
	 */
	protected static function copyDirectoryRecursive($src,$dst,$base,$fileTypes,$exclude,$level,$options)
	{
		if(!is_dir($dst))
			self::createDirectory($dst,isset($options['newDirMode'])?$options['newDirMode']:null,false);

		$folder=opendir($src);
		if($folder===false)
			throw new Exception('Unable to open directory: ' . $src);
		while(($file=readdir($folder))!==false)
		{
			if($file==='.' || $file==='..')
				continue;
			$path=$src.DIRECTORY_SEPARATOR.$file;
			$isFile=is_file($path);
			if(self::validatePath($base,$file,$isFile,$fileTypes,$exclude))
			{
				if($isFile)
				{
					copy($path,$dst.DIRECTORY_SEPARATOR.$file);
					if(isset($options['newFileMode']))
						@chmod($dst.DIRECTORY_SEPARATOR.$file,$options['newFileMode']);
				}
				elseif($level)
					self::copyDirectoryRecursive($path,$dst.DIRECTORY_SEPARATOR.$file,$base.'/'.$file,$fileTypes,$exclude,$level-1,$options);
			}
		}
		closedir($folder);
	}

	/**
	 * Returns the files found under the specified directory and subdirectories.
	 * This method is mainly used by {@link findFiles}.
	 * @param string $dir the source directory
	 * @param string $base the path relative to the original source directory
	 * @param array $fileTypes list of file name suffix (without dot). Only files with these suffixes will be returned.
	 * @param array $exclude list of directory and file exclusions. Each exclusion can be either a name or a path.
	 * If a file or directory name or path matches the exclusion, it will not be copied. For example, an exclusion of
	 * '.svn' will exclude all files and directories whose name is '.svn'. And an exclusion of '/a/b' will exclude
	 * file or directory '$src/a/b'. Note, that '/' should be used as separator regardless of the value of the DIRECTORY_SEPARATOR constant.
	 * @param integer $level recursion depth. It defaults to -1.
	 * Level -1 means searching for all directories and files under the directory;
	 * Level 0 means searching for only the files DIRECTLY under the directory;
	 * level N means searching for those directories that are within N levels.
	 * @param boolean $absolutePaths whether to return absolute paths or relative ones
	 * @return array files found under the directory.
	 * @throws Exception
	 */
	protected static function findFilesRecursive($dir,$base,$fileTypes,$exclude,$level,$absolutePaths)
	{
		$list=array();
		$handle=opendir($dir.$base);
		if($handle===false)
			throw new Exception('Unable to open directory: ' . $dir);
		while(($file=readdir($handle))!==false)
		{
			if($file==='.' || $file==='..')
				continue;
			$path=substr($base.DIRECTORY_SEPARATOR.$file,1);
			$fullPath=$dir.DIRECTORY_SEPARATOR.$path;
			$isFile=is_file($fullPath);
			if(self::validatePath($base,$file,$isFile,$fileTypes,$exclude))
			{
				if($isFile)
					$list[]=$absolutePaths?$fullPath:$path;
				elseif($level)
					$list=array_merge($list,self::findFilesRecursive($dir,$base.DIRECTORY_SEPARATOR.$file,$fileTypes,$exclude,$level-1,$absolutePaths));
			}
		}
		closedir($handle);
		return $list;
	}

	/**
	 * Validates a file or directory.
	 * @param string $base the path relative to the original source directory
	 * @param string $file the file or directory name
	 * @param boolean $isFile whether this is a file
	 * @param array $fileTypes list of valid file name suffixes (without dot).
	 * @param array $exclude list of directory and file exclusions. Each exclusion can be either a name or a path.
	 * If a file or directory name or path matches the exclusion, false will be returned. For example, an exclusion of
	 * '.svn' will return false for all files and directories whose name is '.svn'. And an exclusion of '/a/b' will return false for
	 * file or directory '$src/a/b'. Note, that '/' should be used as separator regardless of the value of the DIRECTORY_SEPARATOR constant.
	 * @return boolean whether the file or directory is valid
	 */
	protected static function validatePath($base,$file,$isFile,$fileTypes,$exclude)
	{
		foreach($exclude as $e)
		{
			if($file===$e || strpos($base.'/'.$file,$e)===0)
				return false;
		}
		if(!$isFile || empty($fileTypes))
			return true;
		if(($type=self::getExtension($file))!=='')
			return in_array($type,$fileTypes);
		else
			return false;
	}

	/**
	 * Determines the MIME type of the specified file.
	 * This method will attempt the following approaches in order:
	 * <ol>
	 * <li>finfo</li>
	 * <li>mime_content_type</li>
	 * <li>{@link getMimeTypeByExtension}, when $checkExtension is set true.</li>
	 * </ol>
	 * @param string $file the file name.
	 * @param string $magicFile name of a magic database file, usually something like /path/to/magic.mime.
	 * This will be passed as the second parameter to {@link http://php.net/manual/en/function.finfo-open.php finfo_open}.
	 * Magic file format described in {@link http://linux.die.net/man/5/magic man 5 magic}, note that this file does not
	 * contain a standard PHP array as you might suppose. Specified magic file will be used only when fileinfo
	 * PHP extension is available. This parameter has been available since version 1.1.3.
	 * @param boolean $checkExtension whether to check the file extension in case the MIME type cannot be determined
	 * based on finfo and mime_content_type. Defaults to true. This parameter has been available since version 1.1.4.
	 * @return string the MIME type. Null is returned if the MIME type cannot be determined.
	 */
	public static function getMimeType($file,$magicFile=null,$checkExtension=true)
	{
		if(function_exists('finfo_open'))
		{
			$options=defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
			$info=$magicFile===null ? finfo_open($options) : finfo_open($options,$magicFile);

			if($info && ($result=finfo_file($info,$file))!==false)
				return $result;
		}

		if(function_exists('mime_content_type') && ($result=mime_content_type($file))!==false)
			return $result;

		return $checkExtension ? self::getMimeTypeByExtension($file) : null;
	}

	/**
	 * Determines the MIME type based on the extension name of the specified file.
	 * This method will use a local map between extension name and MIME type.
	 * @param string $file the file name.
	 * @param string $magicFile the path of the file that contains all available MIME type information.
	 * If this is not set, the default 'system.utils.mimeTypes' file will be used.
	 * This parameter has been available since version 1.1.3.
	 * @return string the MIME type. Null is returned if the MIME type cannot be determined.
	 */
	public static function getMimeTypeByExtension($file,$magicFile=null)
	{
		static $extensions,$customExtensions=array();
		if($magicFile===null && $extensions===null)
			$extensions=require(Yii::getPathOfAlias('system.utils.mimeTypes').'.php');
		elseif($magicFile!==null && !isset($customExtensions[$magicFile]))
			$customExtensions[$magicFile]=require($magicFile);
		if(($ext=self::getExtension($file))!=='')
		{
			$ext=strtolower($ext);
			if($magicFile===null && isset($extensions[$ext]))
				return $extensions[$ext];
			elseif($magicFile!==null && isset($customExtensions[$magicFile][$ext]))
				return $customExtensions[$magicFile][$ext];
		}
		return null;
	}

	/**
	 * Determines the file extension name based on its MIME type.
	 * This method will use a local map between MIME type and extension name.
	 * @param string $file the file name.
	 * @param string $magicFile the path of the file that contains all available extension information.
	 * If this is not set, the default 'system.utils.fileExtensions' file will be used.
	 * This parameter has been available since version 1.1.16.
	 * @return string extension name. Null is returned if the extension cannot be determined.
	 */
	public static function getExtensionByMimeType($file,$magicFile=null)
	{
		static $mimeTypes,$customMimeTypes=array();
		if($magicFile===null && $mimeTypes===null)
			$mimeTypes=require(Yii::getPathOfAlias('system.utils.fileExtensions').'.php');
		elseif($magicFile!==null && !isset($customMimeTypes[$magicFile]))
			$customMimeTypes[$magicFile]=require($magicFile);
		if(($mime=self::getMimeType($file))!==null)
		{
			$mime=strtolower($mime);
			if($magicFile===null && isset($mimeTypes[$mime]))
				return $mimeTypes[$mime];
			elseif($magicFile!==null && isset($customMimeTypes[$magicFile][$mime]))
				return $customMimeTypes[$magicFile][$mime];
		}
		return null;
	}

	/**
	 * Shared environment safe version of mkdir. Supports recursive creation.
	 * For avoidance of umask side-effects chmod is used.
	 *
	 * @param string $dst path to be created
	 * @param integer $mode the permission to be set for newly created directories, if not set - 0777 will be used
	 * @param boolean $recursive whether to create directory structure recursive if parent dirs do not exist
	 * @return boolean result of mkdir
	 * @see mkdir
	 */
	public static function createDirectory($dst,$mode=null,$recursive=false)
	{
		if($mode===null)
			$mode=0777;
		$prevDir=dirname($dst);
		if($recursive && !is_dir($dst) && !is_dir($prevDir))
			self::createDirectory(dirname($dst),$mode,true);
		$res=mkdir($dst, $mode);
		@chmod($dst,$mode);
		return $res;
	}
}

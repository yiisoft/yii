<?php
/**
 * CFileHelper class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFileHelper provides a set of helper methods for common file system operations.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.utils
 * @since 1.0
 */
class CFileHelper
{
	/**
	 * Copies a directory recursively as another.
	 * If the destination directory does not exist, it will be created.
	 * @param string the source directory
	 * @param string the destination directory
	 * @param array options for directory copy. Valid options are:
	 * <ul>
	 * <li>fileTypes: array, list of file name suffix (without dot). Only files with these suffixes will be copied.</li>
	 * <li>exclude: array, list of directory and file exclusions. Each exclusion can be either a name or a path.
	 * If a file or directory name or path matches the exclusion, it will not be copied. For example, an exclusion of
	 * '.svn' will exclude all files and directories whose name is '.svn'. And an exclusion of '/a/b' will exclude
	 * file or directory '$src/a/b'.</li>
	 * <li>level: integer, recursion depth, default=-1.
	 * Level -1 means copying all directories and files under the directory;
	 * Level 0 means copying only the files DIRECTLY under the directory;
	 * level N means copying those directories that are within N levels.
 	 * </li>
	 * </ul>
	 */
	public static function copyDirectory($src,$dst,$options=array())
	{
		$fileTypes=array();
		$exclude=array();
		$level=-1;
		extract($options);
		self::copyDirectoryRecursive($src,$dst,'',$fileTypes,$exclude,$level);
	}

	/**
	 * Returns the files found under the specified directory and subdirectories.
	 * @param string the directory under which the files will be looked for
	 * @param array options for file searching. Valid options are:
	 * <ul>
	 * <li>fileTypes: array, list of file name suffix (without dot). Only files with these suffixes will be returned.</li>
	 * <li>exclude: array, list of directory and file exclusions. Each exclusion can be either a name or a path.
	 * If a file or directory name or path matches the exclusion, it will not be copied. For example, an exclusion of
	 * '.svn' will exclude all files and directories whose name is '.svn'. And an exclusion of '/a/b' will exclude
	 * file or directory '$src/a/b'.</li>
	 * <li>level: integer, recursion depth, default=-1.
	 * Level -1 means searching for all directories and files under the directory;
	 * Level 0 means searching for only the files DIRECTLY under the directory;
	 * level N means searching for those directories that are within N levels.
 	 * </li>
	 * </ul>
	 * @return array files found under the directory. The file list is sorted.
	 */
	public static function findFiles($dir,$options=array())
	{
		$fileTypes=array();
		$exclude=array();
		$level=-1;
		extract($options);
		$list=self::findFilesRecursive($dir,'',$fileTypes,$exclude,$level);
		sort($list);
		return $list;
	}

	/**
	 * Copies a directory.
	 * This method is mainly used by {@link copyDirectory}.
	 * @param string the source directory
	 * @param string the destination directory
	 * @param string the path relative to the original source directory
	 * @param array list of file name suffix (without dot). Only files with these suffixes will be copied.
	 * @param array list of directory and file exclusions. Each exclusion can be either a name or a path.
	 * If a file or directory name or path matches the exclusion, it will not be copied. For example, an exclusion of
	 * '.svn' will exclude all files and directories whose name is '.svn'. And an exclusion of '/a/b' will exclude
	 * file or directory '$src/a/b'.
	 * @param integer recursion depth. It defaults to -1.
	 * Level -1 means copying all directories and files under the directory;
	 * Level 0 means copying only the files DIRECTLY under the directory;
	 * level N means copying those directories that are within N levels.
	 */
	protected static function copyDirectoryRecursive($src,$dst,$base,$fileTypes,$exclude,$level)
	{
		@mkdir($dst);
		@chmod($dst,0777);
		$folder=opendir($src);
		while($file=readdir($folder))
		{
			if($file==='.' || $file==='..')
				continue;
			$path=$src.DIRECTORY_SEPARATOR.$file;
			$isFile=is_file($path);
			if(self::validatePath($base,$file,$isFile,$fileTypes,$exclude))
			{
				if($isFile)
					copy($path,$dst.DIRECTORY_SEPARATOR.$file);
				else if($level)
					self::copyDirectoryRecursive($path,$dst.DIRECTORY_SEPARATOR.$file,$base.'/'.$file,$fileTypes,$exclude,$level-1);
			}
		}
		closedir($folder);
	}

	/**
	 * Returns the files found under the specified directory and subdirectories.
	 * This method is mainly used by {@link findFiles}.
	 * @param string the source directory
	 * @param string the path relative to the original source directory
	 * @param array list of file name suffix (without dot). Only files with these suffixes will be returned.
	 * @param array list of directory and file exclusions. Each exclusion can be either a name or a path.
	 * If a file or directory name or path matches the exclusion, it will not be copied. For example, an exclusion of
	 * '.svn' will exclude all files and directories whose name is '.svn'. And an exclusion of '/a/b' will exclude
	 * file or directory '$src/a/b'.
	 * @param integer recursion depth. It defaults to -1.
	 * Level -1 means searching for all directories and files under the directory;
	 * Level 0 means searching for only the files DIRECTLY under the directory;
	 * level N means searching for those directories that are within N levels.
	 * @return array files found under the directory.
	 */
	protected static function findFilesRecursive($dir,$base,$fileTypes,$exclude,$level)
	{
		$list=array();
		$handle=opendir($dir);
		while($file=readdir($handle))
		{
			if($file==='.' || $file==='..')
				continue;
			$path=$dir.DIRECTORY_SEPARATOR.$file;
			$isFile=is_file($path);
			if(self::validatePath($base,$file,$isFile,$fileTypes,$exclude))
			{
				if($isFile)
					$list[]=$path;
				else if($level)
					$list=array_merge($list,self::findFilesRecursive($path,$base.'/'.$file,$fileTypes,$exclude,$level-1));
			}
		}
		closedir($handle);
		return $list;
	}

	/**
	 * Validates a file or directory.
	 * @param string the path relative to the original source directory
	 * @param string the file or directory name
	 * @param boolean whether this is a file
	 * @param array list of file name suffix (without dot). Only files with these suffixes will be copied.
	 * @param array list of directory and file exclusions. Each exclusion can be either a name or a path.
	 * If a file or directory name or path matches the exclusion, it will not be copied. For example, an exclusion of
	 * '.svn' will exclude all files and directories whose name is '.svn'. And an exclusion of '/a/b' will exclude
	 * file or directory '$src/a/b'.
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
		if(($pos=strrpos($file,'.'))!==false)
		{
			$type=substr($file,$pos+1);
			return in_array($type,$fileTypes);
		}
		else
			return false;
	}
}

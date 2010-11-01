<?php
/**
 * CAssetManager class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CAssetManager is a Web application component that manages private files (called assets) and makes them accessible by Web clients.
 *
 * It achieves this goal by copying assets to a Web-accessible directory
 * and returns the corresponding URL for accessing them.
 *
 * To publish an asset, simply call {@link publish()}.
 *
 * The Web-accessible directory holding the published files is specified
 * by {@link setBasePath basePath}, which defaults to the "assets" directory
 * under the directory containing the application entry script file.
 * The property {@link setBaseUrl baseUrl} refers to the URL for accessing
 * the {@link setBasePath basePath}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CAssetManager extends CApplicationComponent
{
	/**
	 * Default web accessible base path for storing private files
	 */
	const DEFAULT_BASEPATH='assets';
	/**
	 * @var boolean whether to use symbolic link to publish asset files. Defaults to false, meaning
	 * asset files are copied to public folders. Using symbolic links has the benefit that the published
	 * assets will always be consistent with the source assets. This is especially useful during development.
	 *
	 * However, there are special requirements for hosting environments in order to use symbolic links.
	 * In particular, symbolic links are supported only on Linux/Unix, and Windows Vista/2008 or greater.
	 * The latter requires PHP 5.3 or greater.
	 *
	 * Moreover, some Web servers need to be properly configured so that the linked assets are accessible
	 * to Web users. For example, for Apache Web server, the following configuration directive should be added
	 * for the Web folder:
	 * <pre>
	 * Options FollowSymLinks
	 * </pre>
	 *
	 * @since 1.1.5
	 */
	public $linkAssets=false;
	/**
	 * @var string base web accessible path for storing private files
	 */
	private $_basePath;
	/**
	 * @var string base URL for accessing the publishing directory.
	 */
	private $_baseUrl;
	/**
	 * @var array published assets
	 */
	private $_published=array();

	/**
	 * @return string the root directory storing the published asset files. Defaults to 'WebRoot/assets'.
	 */
	public function getBasePath()
	{
		if($this->_basePath===null)
		{
			$request=Yii::app()->getRequest();
			$this->setBasePath(dirname($request->getScriptFile()).DIRECTORY_SEPARATOR.self::DEFAULT_BASEPATH);
		}
		return $this->_basePath;
	}

	/**
	 * Sets the root directory storing published asset files.
	 * @param string $value the root directory storing published asset files
	 * @throws CException if the base path is invalid
	 */
	public function setBasePath($value)
	{
		if(($basePath=realpath($value))!==false && is_dir($basePath) && is_writable($basePath))
			$this->_basePath=$basePath;
		else
			throw new CException(Yii::t('yii','CAssetManager.basePath "{path}" is invalid. Please make sure the directory exists and is writable by the Web server process.',
				array('{path}'=>$value)));
	}

	/**
	 * @return string the base url that the published asset files can be accessed.
	 * Note, the ending slashes are stripped off. Defaults to '/AppBaseUrl/assets'.
	 */
	public function getBaseUrl()
	{
		if($this->_baseUrl===null)
		{
			$request=Yii::app()->getRequest();
			$this->setBaseUrl($request->getBaseUrl().'/'.self::DEFAULT_BASEPATH);
		}
		return $this->_baseUrl;
	}

	/**
	 * @param string $value the base url that the published asset files can be accessed
	 */
	public function setBaseUrl($value)
	{
		$this->_baseUrl=rtrim($value,'/');
	}

	/**
	 * Publishes a file or a directory.
	 * This method will copy the specified asset to a web accessible directory
	 * and return the URL for accessing the published asset.
	 * <ul>
	 * <li>If the asset is a file, its file modification time will be checked
	 * to avoid unnecessary file copying;</li>
	 * <li>If the asset is a directory, all files and subdirectories under it will
	 * be published recursively. Note, in this case the method only checks the
	 * existence of the target directory to avoid repetitive copying.</li>
	 * </ul>
	 * @param string $path the asset (file or directory) to be published
	 * @param boolean $hashByName whether the published directory should be named as the hashed basename.
	 * If false, the name will be the hashed dirname of the path being published.
	 * Defaults to false. Set true if the path being published is shared among
	 * different extensions.
	 * @param integer $level level of recursive copying when the asset is a directory.
	 * Level -1 means publishing all subdirectories and files;
	 * Level 0 means publishing only the files DIRECTLY under the directory;
	 * level N means copying those directories that are within N levels.
	 * @param boolean $forceCopy whether we should copy the asset file or directory even if it is already published before.
	 * This parameter is set true mainly during development stage when the original
	 * assets are being constantly changed. The consequence is that the performance
	 * is degraded, which is not a concern during development, however.
	 * This parameter has been available since version 1.1.2.
	 * @return string an absolute URL to the published asset
	 * @throws CException if the asset to be published does not exist.
	 */
	public function publish($path,$hashByName=false,$level=-1,$forceCopy=false)
	{
		if(isset($this->_published[$path]))
			return $this->_published[$path];
		else if(($src=realpath($path))!==false)
		{
			if(is_file($src))
			{
				$dir=$this->hash($hashByName ? basename($src) : dirname($src));
				$fileName=basename($src);
				$dstDir=$this->getBasePath().DIRECTORY_SEPARATOR.$dir;
				$dstFile=$dstDir.DIRECTORY_SEPARATOR.$fileName;

				if($this->linkAssets)
				{
					if(!is_file($dstFile))
						symlink($src,$dstFile);
				}
				else if(@filemtime($dstFile)<@filemtime($src) || $forceCopy)
				{
					if(!is_dir($dstDir))
					{
						mkdir($dstDir);
						@chmod($dstDir,0777);
					}
					copy($src,$dstFile);
				}

				return $this->_published[$path]=$this->getBaseUrl()."/$dir/$fileName";
			}
			else if(is_dir($src))
			{
				$dir=$this->hash($hashByName ? basename($src) : $src);
				$dstDir=$this->getBasePath().DIRECTORY_SEPARATOR.$dir;

				if($this->linkAssets)
				{
					if(!is_dir($dstDir))
						symlink($src,$dstDir);
				}
				else if(!is_dir($dstDir) || $forceCopy)
					CFileHelper::copyDirectory($src,$dstDir,array('exclude'=>array('.svn'),'level'=>$level));

				return $this->_published[$path]=$this->getBaseUrl().'/'.$dir;
			}
		}
		throw new CException(Yii::t('yii','The asset "{asset}" to be published does not exist.',
			array('{asset}'=>$path)));
	}

	/**
	 * Returns the published path of a file path.
	 * This method does not perform any publishing. It merely tells you
	 * if the file or directory is published, where it will go.
	 * @param string $path directory or file path being published
	 * @param boolean $hashByName whether the published directory should be named as the hashed basename.
	 * If false, the name will be the hashed dirname of the path being published.
	 * Defaults to false. Set true if the path being published is shared among
	 * different extensions.
	 * @return string the published file path. False if the file or directory does not exist
	 */
	public function getPublishedPath($path,$hashByName=false)
	{
		if(($path=realpath($path))!==false)
		{
			$base=$this->getBasePath().DIRECTORY_SEPARATOR;
			if(is_file($path))
				return $base . $this->hash($hashByName ? basename($path) : dirname($path)) . DIRECTORY_SEPARATOR . basename($path);
			else
				return $base . $this->hash($hashByName ? basename($path) : $path);
		}
		else
			return false;
	}

	/**
	 * Returns the URL of a published file path.
	 * This method does not perform any publishing. It merely tells you
	 * if the file path is published, what the URL will be to access it.
	 * @param string $path directory or file path being published
	 * @param boolean $hashByName whether the published directory should be named as the hashed basename.
	 * If false, the name will be the hashed dirname of the path being published.
	 * Defaults to false. Set true if the path being published is shared among
	 * different extensions.
	 * @return string the published URL for the file or directory. False if the file or directory does not exist.
	 */
	public function getPublishedUrl($path,$hashByName=false)
	{
		if(isset($this->_published[$path]))
			return $this->_published[$path];
		if(($path=realpath($path))!==false)
		{
			if(is_file($path))
				return $this->getBaseUrl().'/'.$this->hash($hashByName ? basename($path) : dirname($path)).'/'.basename($path);
			else
				return $this->getBaseUrl().'/'.$this->hash($hashByName ? basename($path) : $path);
		}
		else
			return false;
	}

	/**
	 * Generate a CRC32 hash for the directory path. Collisions are higher
	 * than MD5 but generates a much smaller hash string.
	 * @param string $path string to be hashed.
	 * @return string hashed string.
	 */
	protected function hash($path)
	{
		return sprintf('%x',crc32($path.Yii::getVersion()));
	}
}

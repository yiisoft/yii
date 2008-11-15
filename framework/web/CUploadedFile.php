<?php
/**
 * CUploadedFile class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CUploadedFile represents the information for an uploaded file.
 *
 * Call {@link getInstance} to retrieve the instance of an uploaded file,
 * and then use {@link saveAs} to save it on the server.
 * You may also query other information about the file, including {@link name},
 * {@link tempName}, {@link type}, {@link size} and {@link error}
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CUploadedFile extends CComponent
{
	private $_name;
	private $_tempName;
	private $_type;
	private $_size;
	private $_error;

	/**
	 * Returns an instance of the specified uploaded file.
	 * The file should be uploaded using {@link CHtml::activeFileField}.
	 * @param CModel the model instance
	 * @param string the attribute name
	 * @return CUploadedFile the instance of the uploaded file.
	 * Null is returned if no file is uploaded for the specified model attribute.
	 * @see getInstanceByName
	 */
	public static function getInstance($model,$attribute)
	{
		return self::getInstanceByName(get_class($model).'['.$attribute.']');
	}

	/**
	 * Returns an instance of the specified uploaded file.
	 * @param string the name of the file input field.
	 * @return CUploadedFile the instance of the uploaded file.
	 * Null is returned if no file is uploaded for the specified name.
	 */
	public static function getInstanceByName($name)
	{
		static $files;
		if($files===null)
		{
			if(isset($_FILES) && is_array($_FILES))
			{
				foreach($_FILES as $key=>$info)
				{
					if(is_array($info['name']))
					{
						$subKeys=array_keys($info['name']);
						foreach($subKeys as $subKey)
							$files[$key.'['.$subKey.']']=new CUploadedFile($info['name'][$subKey],$info['tmp_name'][$subKey],$info['type'][$subKey],$info['size'][$subKey],$info['error'][$subKey]);
					}
					else
						$files[$key]=new CUploadedFile($info['name'],$info['tmp_name'],$info['type'],$info['size'],$info['error']);
				}
			}
			else
				$files=array();
		}
		return isset($files[$name]) ? $files[$name] : null;
	}

	/**
	 * Constructor.
	 * Use {@link getInstance} to get an instance of an uploaded file.
	 * @param string the original name of the file being uploaded
	 * @param string the path of the uploaded file on the server.
	 * @param string the MIME-type of the uploaded file (such as "image/gif").
	 * @param integer the actual size of the uploaded file in bytes
	 * @param integer the error code
	 */
	protected function __construct($name,$tempName,$type,$size,$error)
	{
		$this->_name=$name;
		$this->_tempName=$tempName;
		$this->_type=$type;
		$this->_size=$size;
		$this->_error=$error;
	}

	/**
	 * Saves the uploaded file.
	 * @param string the file path used to save the uploaded file
	 * @param boolean whether to delete the temporary file after saving.
	 * If true, you will not be able to save the uploaded file again in the current request.
	 * @return boolean true whether the file is saved successfully
	 */
	public function saveAs($file,$deleteTempFile=true)
	{
		if($this->_error===UPLOAD_ERR_OK)
		{
			if($deleteTempFile)
				return move_uploaded_file($this->_tempName,$file);
			else if(is_uploaded_file($this->_tempName))
				return file_put_contents($file,file_get_contents($this->_tempName))!==false;
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * @return string the original name of the file being uploaded
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * @return string the path of the uploaded file on the server.
	 * Note, this is a temporary file which will be automatically deleted by PHP
	 * after the current request is processed.
	 */
	public function getTempName()
	{
		return $this->_tempName;
	}

	/**
	 * @return string the MIME-type of the uploaded file (such as "image/gif").
	 * Since this mime type is not checked on the server side, do not take its value for granted.
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * @return integer the actual size of the uploaded file in bytes
	 */
	public function getSize()
	{
		return $this->_size;
	}

	/**
	 * Returns an error code describing the status of this file uploading.
	 * @return integer the error code
	 * @see http://www.php.net/manual/en/features.file-upload.errors.php
	 */
	public function getError()
	{
		return $this->_error;
	}

	/**
	 * @return boolean whether there is an error with the uploaded file.
	 * Check {@link error} for detailed error code information.
	 */
	public function getHasError()
	{
		return $this->_error!=UPLOAD_ERR_OK;
	}

	/**
	 * @return string the file extension name for {@link name}.
	 * The extension name does not include the dot character. An empty string
	 * is returned if {@link name} does not have an extension name.
	 */
	public function getExtensionName()
	{
		if(($pos=strrpos($this->_name,'.'))!==false)
			return (string)substr($this->_name,$pos+1);
		else
			return '';
	}
}
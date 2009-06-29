<?php
/**
 * CUploadedFile class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CUploadedFile represents the information for an uploaded file.
 *
 * Call {@link getInstance} to retrieve the instance of an uploaded file,
 * and then use {@link saveAs} to save it on the server.
 * You may also query other information about the file, including {@link name},
 * {@link tempName}, {@link type}, {@link size} and {@link error}.
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
	 * @param string the attribute name. For tabular file uploading, this can be in the format of "attributeName[$i]", where $i stands for an integer index.
	 * @return CUploadedFile the instance of the uploaded file.
	 * Null is returned if no file is uploaded for the specified model attribute.
	 * @see getInstanceByName
	 */
	public static function getInstance($model,$attribute)
	{
		if(($pos=strpos($attribute,'['))!==false)
			$name=get_class($model).substr($attribute,$pos).'['.substr($attribute,0,$pos).']';
		else
			$name=get_class($model).'['.$attribute.']';
		return self::getInstanceByName($name);
	}

	/**
	 * Returns an instance of the specified uploaded file.
	 * The name can be a plain string or a string like an array element (e.g. 'Post[imageFile]', or 'Post[0][imageFile]').
	 * @param string the name of the file input field.
	 * @return CUploadedFile the instance of the uploaded file.
	 * Null is returned if no file is uploaded for the specified name.
	 */
	public static function getInstanceByName($name)
	{
		static $files;
		if($files===null)
		{
			$files=array();
			if(isset($_FILES) && is_array($_FILES))
			{
				foreach($_FILES as $class=>$info)
				{
					if(is_array($info['name']))
					{
						$keys=array_keys($info['name']);
						foreach($keys as $key)
						{
							if(is_array($info['name'][$key]))
							{
								$subKeys=array_keys($info['name'][$key]);
								foreach($subKeys as $subKey)
									$files["{$class}[{$key}][{$subKey}]"]=new CUploadedFile($info['name'][$key][$subKey],$info['tmp_name'][$key][$subKey],$info['type'][$key][$subKey],$info['size'][$key][$subKey],$info['error'][$key][$subKey]);
							}
							else
								$files["{$class}[{$key}]"]=new CUploadedFile($info['name'][$key],$info['tmp_name'][$key],$info['type'][$key],$info['size'][$key],$info['error'][$key]);
						}
					}
					else
						$files[$class]=new CUploadedFile($info['name'],$info['tmp_name'],$info['type'],$info['size'],$info['error']);
				}
			}
		}

		return isset($files[$name]) && $files[$name]->getError()!=UPLOAD_ERR_NO_FILE ? $files[$name] : null;
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
	 * String output.
	 * This is PHP magic method that returns string representation of an object.
	 * The implementation here returns the uploaded file's name.
	 * @return string the string representation of the object
	 * @since 1.0.2
	 */
	public function __toString()
	{
		return $this->_name;
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
	 * Since this MIME type is not checked on the server side, do not take this value for granted.
	 * Instead, use {@link CFileHelper::getMimeType} to determine the exact MIME type.
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
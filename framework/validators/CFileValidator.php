<?php
/**
 * CFileValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFileValidator verifies if an attribute is receiving a valid uploaded file.
 *
 * It uses the model class and attribute name to retrieve the information
 * about the uploaded file. It then checks if a file is uploaded successfully,
 * if the file size is within the limit and if the file type is allowed.
 *
 * When using CFileValidator with an active record, the following code is often used:
 * <pre>
 *  // assuming the upload file field is generated using
 *  // CHtml::activeFileField($model,'file');
 *  $model->file=CUploadedFile::getInstance($model,'file');
 *  $model->fileSize=$file->size;
 *  if($model->save())
 *      $model->file->saveAs($path); // save the uploaded file
 * </pre>
 *
 * You can use {@link CFileValidator} to validate the file attribute.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class CFileValidator extends CValidator
{
	/**
	 * @var boolean whether the attribute requires a file to be uploaded or not.
	 * Defaults to false, meaning a file is required to be uploaded.
	 */
	public $allowEmpty=false;
	/**
	 * @var string a list of file name extensions that are allowed to be uploaded.
	 * Separate the extension names with space or comma, for example, "gif, jpg".
	 * Extension names are case-insensitive. Defaults to null, meaning all file name
	 * extensions are allowed.
	 */
	public $types;
	/**
	 * @var integer the minimum number of bytes required for the uploaded file.
	 * Defaults to null, meaning no limit.
	 * @see tooSmall
	 */
	public $minSize;
	/**
	 * @var integer the maximum number of bytes required for the uploaded file.
	 * Defaults to null, meaning no limit.
	 * Note, the size limit is also affected by 'upload_max_filesize' INI setting
	 * and the 'MAX_FILE_SIZE' hidden field value.
	 * @see tooLarge
	 */
	public $maxSize;
	/**
	 * @var string the error message used when the uploaded file is too large.
	 * @see maxSize
	 */
	public $tooLarge;
	/**
	 * @var string the error message used when the uploaded file is too small.
	 * @see minSize
	 */
	public $tooSmall;
	/**
	 * @var string the error message used when the uploaded file has an extension name
	 * that is not listed among {@link extensions}.
	 */
	public $wrongType;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel the object being validated
	 * @param string the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$file=$object->$attribute;
		if(!($file instanceof CUploadedFile))
			$file=CUploadedFile::getInstance($object,$attribute);

		if($this->allowEmpty && ($file===null || $file->getError()==UPLOAD_ERR_NO_FILE))
			return;

		if($file===null || $file->getError()==UPLOAD_ERR_NO_FILE)
		{
			$message=$this->message!==null?$this->message : Yii::t('yii','{attribute} cannot be blank.');
			$this->addError($object,$attribute,$message);
			return;
		}

		$error=$file->getError();
		if($error==UPLOAD_ERR_INI_SIZE || $error==UPLOAD_ERR_FORM_SIZE || $this->maxSize!==null && $file->getSize()>$this->maxSize)
		{
			$message=$this->tooLarge!==null?$this->tooLarge : Yii::t('yii','The file "{file}" is too large. Its size cannot exceed {limit} bytes.');
			$this->addError($object,$attribute,$message,array('{file}'=>$file->getName(), '{limit}'=>$this->getSizeLimit()));
		}
		else if($error==UPLOAD_ERR_PARTIAL)
			throw new CException(Yii::t('yii','The file "{file}" was only partially uploaded.',array('{file}'=>$file->getName())));
		else if($error==UPLOAD_ERR_NO_TMP_DIR)
			throw new CException(Yii::t('yii','Missing the temporary folder to store the uploaded file "{file}".',array('{file}'=>$file->getName())));
		else if($error==UPLOAD_ERR_CANT_WRITE)
			throw new CException(Yii::t('yii','Failed to write the uploaded file "{file}" to disk.',array('{file}'=>$file->getName())));
		else if(defined('UPLOAD_ERR_EXTENSION') && $error==UPLOAD_ERR_EXTENSION)  // available for PHP 5.2.0 or above
			throw new CException(Yii::t('yii','File upload was stopped by extension.'));

		if($this->minSize!==null && $file->getSize()<$this->minSize)
		{
			$message=$this->tooSmall!==null?$this->tooLarge : Yii::t('yii','The file "{file}" is too small. Its size cannot be smaller than {limit} bytes.');
			$this->addError($object,$attribute,$message,array('{file}'=>$file->getName(), '{limit}'=>$this->minSize));
		}

		if($this->types!==null)
		{
			$types=preg_split('/[\s,]+/',strtolower($this->types),-1,PREG_SPLIT_NO_EMPTY);
			if(!in_array(strtolower($file->getExtensionName()),$types))
			{
				$message=$this->wrongType!==null?$this->wrongType : Yii::t('yii','The file "{file}" cannot be uploaded. Only files with these extensions are allowed: {extensions}.');
				$this->addError($object,$attribute,$message,array('{file}'=>$file->getName(), '{extensions}'=>implode(', ',$types)));
			}
		}
	}

	/**
	 * Returns the maximum size allowed for uploaded files.
	 * This is determined based on three factors:
	 * <ul>
	 * <li>'upload_max_filesize' in php.ini</li>
	 * <li>'MAX_FILE_SIZE' hidden field</li>
	 * <li>{@link maxSize}</li>
	 * </ul>
	 *
	 * @return integer the size limit for uploaded files.
	 */
	protected function getSizeLimit()
	{
		$limit=ini_get('upload_max_filesize');
		if(strpos($limit,'M')!==false)
			$limit=$limit*1024*1024;
		if($this->maxSize!==null && $limit>0 && $this->maxSize<$limit)
			$limit=$this->maxSize;
		if(isset($_POST['MAX_FILE_SIZE']) && $_POST['MAX_FILE_SIZE']>0 && $_POST['MAX_FILE_SIZE']<$limit)
			$limit=$_POST['MAX_FILE_SIZE'];
		return $limit;
	}
}

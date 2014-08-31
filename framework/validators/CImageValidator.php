<?php
/**
 * CImageValidator class file.
 *
 * @author UA2004 <ua2004@ukr.net>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2014 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
 
/**
 * Validates images by scanning for malicious PHP code inside of them.
 * It includes all properties and methods of {@link CFileValidator}.
 * 
 * In addition to message properties from {@link CFileValidator}
 * for setting custom error messages, CImageValidator has a new custom
 * error message {@link CFileValidator}. It is shown when the uploaded image
 * contains malicious PHP code.
 * 
 * @author UA2004 <ua2004@ukr.net>
 * @package system.validators
 * @since 1.1.16
 */
class CImageValidator extends CFileValidator
{
    /**
	 * @var string the error message used when the uploaded images contains malicious PHP code.
	 */
    public $malicious;
    
    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     * @param CModel $object the object being validated
     * @param string $attribute the attribute being validated
     */
    protected function validateAttribute($object,$attribute)
    {
        parent::validateAttribute($object,$attribute);
        // no need to keep executing if there are already errors on this attribute
        if($object->hasErrors($attribute))
        {
            return;
        }
        
        // getting the uploaded file
        $file = CUploadedFile::getInstance($object, $attribute);
        
        $handle = fopen($file->tempName, 'r');
        $valid = true; // init as true
        while (($buffer = fgets($handle)) !== false)
        {
            // scanning for malicious PHP code
            if (strpos($buffer, '<?php') !== false)
            {
                $valid = false;
                break; // if the malicious string is found, we break the loop
            }      
        }
        fclose($handle);

        if(!$valid)
        {
            $message = $this->malicious !== null ? $this->malicious : Yii::t('yii','The file "{file}" cannot be uploaded. It contains malicious code.');
            $this->addError($object, $attribute, $message);
        }
    }
}

<?php
/**
 * CCaptchaValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCaptchaValidator validates that the attribute value is the same as the verification code displayed in the CAPTCHA.
 *
 * CCaptchaValidator should be used together with {@link CCaptchaAction}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class CCaptchaValidator extends CValidator
{
	/**
	 * @var boolean whether the comparison is case sensitive. Defaults to false.
	 */
	public $caseSensitive=false;
	/**
	 * @var string ID of the action that renders the CAPTCHA image. Defaults to 'captcha',
	 * meaning the 'captcha' action declared in the current controller.
	 * This can also be a route consisting of controller ID and action ID.
	 */
	public $captchaAction='captcha';
	/**
	 * @var boolean whether the attribute value can be null or empty.
	 * Defaults to false, meaning the attribute is invalid if it is empty.
	 */
	public $allowEmpty=false;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;

		if(($captcha=Yii::app()->getController()->createAction($this->captchaAction))===null)
		{
			if(strpos($this->captchaAction,'/')!==false) // contains controller or module
			{
				if(($ca=Yii::app()->createController($this->captchaAction))!==null)
				{
					list($controller,$actionID)=$ca;
					$captcha=$controller->createAction($actionID);
				}
			}
			if($captcha===null)
				throw new CException(Yii::t('yii','CCaptchaValidator.action "{id}" is invalid. Unable to find such an action in the current controller.',
						array('{id}'=>$this->captchaAction)));
		}
		if(!$captcha->validate($value,$this->caseSensitive))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','The verification code is incorrect.');
			$this->addError($object,$attribute,$message);
		}
	}
}


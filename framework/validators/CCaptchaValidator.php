<?php
/**
 * CCaptchaValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
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
	 * @var string ID of the action that renders the CAPTCHA image. Defaults to 'captcha'.
	 * Note, the action must belong to the current controller.
	 */
	public $actionID='captcha';

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel the object being validated
	 * @param string the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		if(($captcha=Yii::app()->getController()->createAction($this->actionID))===null)
			throw new CException(Yii::t('yii#CCaptchaValidator.action "{id}" is invalid. Unable to find such an action in the current controller.',
					array('{id}'=>$this->actionID)));
		$value=$object->$attribute;
		$code=$captcha->getVerifyCode();
		$valid=$this->caseSensitive?($value===$code):!strcasecmp($value,$code);
		if(!$valid)
			$this->addError($object,$attribute,$this->message,'yii##The verification code is incorrect.');
	}
}


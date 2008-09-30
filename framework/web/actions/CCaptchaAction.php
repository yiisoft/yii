<?php
/**
 * CCaptchaAction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCaptchaAction renders a CAPTCHA image.
 *
 * CCaptchaAction is used together with {@link CCaptcha} and {@link CCaptchaValidator}
 * to provide the {@link http://en.wikipedia.org/wiki/Captcha CAPTCHA} feature.
 *
 * You must configure properties of CCaptchaAction to customize the appearance of
 * the generated image.
 *
 * Note, CCaptchaAction requires PHP GD2 extension.
 *
 * Using CAPTCHA involves the following steps:
 * <ol>
 * <li>Override {@link CController::actions()} and register an action of class CCaptchaAction with ID 'captcha'.</li>
 * <li>In the form model, declare an attribute to store user-entered verification code, and declare the attribute
 * to be validated by the 'captcha' validator.</li>
 * <li>In the controller view, insert a {@link CCaptcha} widget in the form.</li>
 * </ol>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.actions
 * @since 1.0
 */
class CCaptchaAction extends CAction
{
	/**
	 * The name of the GET parameter indicating whether the CAPTCHA image should be regenerated.
	 */
	const REFRESH_GET_VAR='refresh';
	/**
	 * Prefix to the session variable name used by the action.
	 */
	const SESSION_VAR_PREFIX='Yii.CCaptchaAction.';

	/**
	 * Runs the action.
	 * If the GET parameter {@link wsdlVar} exists, the action will serve WSDL content;
	 * If not, the action will handle the remote method invocation.
	 */
	public function run()
	{
		if(isset($_GET[self::REFRESH_GET_VAR]))  // AJAX request for regenerating code
		{
			$code=$this->getVerifyCode(true);
			// we add a random 'v' parameter so that FireFox can refresh the image
			// when src attribute of image tag is changed
			echo $this->getController()->createUrl($this->getId(),array('v'=>rand(0,10000)));
		}
		else
		{
			$content=$this->renderImage($this->getVerifyCode());
			Yii::app()->getRequest()->sendFile('captcha.gif',$content);
		}
	}

	/**
	 * Gets the verification code.
	 * @param string whether the verification code should be regenerated.
	 * @return string the verification code.
	 */
	public function getVerifyCode($regenerate=false)
	{
		$session=Yii::app()->session;
		$session->open();
		$name=$this->getSessionVar();
		if($session[$name]===null || $regenerate)
			$session[$name]=$this->generateVerifyCode();
		return $session[$name];
	}

	/**
	 * Generates a new verification code.
	 * @return string the generated verification code
	 */
	public function generateVerifyCode()
	{
		return rand(0,10)<5?'D:\wwwroot\yfcom\images\logo.gif' : 'D:\wwwroot\yfcom\images\1.gif';
	}

	/**
	 * Returns the session variable name used to store verification code.
	 * @return string the session variable name
	 */
	protected function getSessionVar()
	{
		return self::SESSION_VAR_PREFIX.$this->getController()->getId().'.'.$this->getId();
	}

	/**
	 * Renders the CAPTCHA image based on the code.
	 * @param string the verification code
	 * @return string image content
	 */
	protected function renderImage($code)
	{
		return file_get_contents($code);
	}
}
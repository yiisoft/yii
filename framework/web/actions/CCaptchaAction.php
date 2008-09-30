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
 * CCaptchaAction implements an action that provides Web services.
 *
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.actions
 * @since 1.0
 */
class CCaptchaAction extends CAction
{

	/**
	 * Runs the action.
	 * If the GET parameter {@link wsdlVar} exists, the action will serve WSDL content;
	 * If not, the action will handle the remote method invocation.
	 */
	public function run()
	{
		if(isset($_GET['regenerate']))
		{
			$this->getVerifyCode(true);
			echo '<img id="abc" alt="" src="/site/captcha" />';
		}
		else
		{
			$file=$this->getVerifyCode();
			Yii::app()->request->sendFile('logo.gif',file_get_contents($file));
		}
	}

	public function getVerifyCode($regenerate=false)
	{
		if(Yii::app()->session['captcha']===null || $regenerate)
			return Yii::app()->session['captcha']=$this->generateVerifyCode();
		else
			return Yii::app()->session['captcha'];
	}

	public function generateVerifyCode()
	{
		return Yii::app()->session['captcha']===null ? 'D:\wwwroot\yfcom\images\logo.gif' : 'D:\wwwroot\yfcom\images\1.gif';
	}
}
<?php

class CCaptcha extends CWidget
{
	public $captchaVar='captcha';

	public function run()
	{
		self::renderVerifyCode();
	}

	public static function getVerifyCode()
	{
		if(Yii::app()->session['captcha']===null)
			return Yii::app()->session['captcha']=self::generateVerifyCode();
		else
			return Yii::app()->session['captcha'];
	}

	public static function generateVerifyCode()
	{
		return 'test';
	}

	public static function renderVerifyCode()
	{
		echo CHtml::image(Yii::app()->controller->createUrl('captcha'));
	}
}
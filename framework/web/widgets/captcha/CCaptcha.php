<?php
/**
 * CCaptcha class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCaptcha renders a CAPTCHA image element.
 *
 * CCaptcha is used together with {@link CCaptchaAction} to provide {@link http://en.wikipedia.org/wiki/Captcha CAPTCHA}
 * - a way of preventing site spam.
 *
 * The image element rendered by CCaptcha will display a CAPTCHA image generated
 * by an action of class {@link CCaptchaAction} belonging to the current controller.
 * By default, the action ID should be 'captcha', which can be changed by setting {@link captchaAction}.
 *
 * CCaptcha may also render a button next to the CAPTCHA image. Clicking on the button
 * will change the CAPTCHA image to be a new one in an AJAX way.
 *
 * Since version 1.0.8, if {@link clickableImage} is set true, clicking on the CAPTCHA image
 * will refresh the CAPTCHA.
 *
 * A {@link CCaptchaValidator} may be used to validate that the user enters
 * a verification code matching the code displayed in the CAPTCHA image.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.widgets.captcha
 * @since 1.0
 */
class CCaptcha extends CWidget
{
	/**
	 * @var string the ID of the action that should provide CAPTCHA image. Defaults to 'captcha',
	 * meaning the 'captcha' action of the current controller. This property may also
	 * be in the format of 'ControllerID/ActionID'. Underneath, this property is used
	 * by {@link CController::createUrl} to create the URL that would serve the CAPTCHA image.
	 * The action has to be of {@link CCaptchaAction}.
	 */
	public $captchaAction='captcha';
	/**
	 * @var boolean whether to display a button next to the CAPTCHA image. Clicking on the button
	 * will cause the CAPTCHA image to be changed to a new one. Defaults to true.
	 */
	public $showRefreshButton=true;
	/**
	 * @var boolean whether to allow clicking on the CAPTCHA image to refresh the CAPTCHA letters.
	 * Defaults to false. Hint: you may want to set {@link showRefreshButton} to false if you set
	 * this property to be true because they serve for the same purpose.
	 * To enhance accessibility, you may set {@link imageOptions} to provide hints to end-users that
	 * the image is clickable.
	 * @since 1.0.8
	 */
	public $clickableImage=false;
	/**
	 * @var string the label for the refresh button. Defaults to 'Get a new code'.
	 */
	public $buttonLabel;
	/**
	 * @var string the type of the refresh button. This should be either 'link' or 'button'.
	 * The former refers to hyperlink button while the latter a normal push button.
	 * Defaults to 'link'.
	 */
	public $buttonType='link';
	/**
	 * @var array HTML attributes to be applied to the rendered image element.
	 */
	public $imageOptions=array();
	/**
	 * @var array HTML attributes to be applied to the rendered refresh button element.
	 */
	public $buttonOptions=array();


	/**
	 * Renders the widget.
	 */
	public function run()
	{
	    if(self::checkRequirements())
	    {
			$this->renderImage();
			$this->registerClientScript();
	    }
		else
			throw new CException(Yii::t('yii','GD and FreeType PHP extensions are required.'));
	}

	/**
	 * Renders the CAPTCHA image.
	 */
	protected function renderImage()
	{
		if(isset($this->imageOptions['id']))
			$id=$this->imageOptions['id'];
		else
			$id=$this->imageOptions['id']=$this->getId();

		$url=$this->getController()->createUrl($this->captchaAction,array('v'=>uniqid()));
		$alt=isset($this->imageOptions['alt'])?$this->imageOptions['alt']:'';
		echo CHtml::image($url,$alt,$this->imageOptions);
	}

	/**
	 * Registers the needed client scripts.
	 * @since 1.0.2
	 */
	public function registerClientScript()
	{
		$cs=Yii::app()->clientScript;
		$id=$this->imageOptions['id'];
		$url=$this->getController()->createUrl($this->captchaAction,array(CCaptchaAction::REFRESH_GET_VAR=>true));

		if($this->showRefreshButton)
		{
			$cs->registerScript('Yii.CCaptcha#'.$id,'dummy');
			$label=$this->buttonLabel===null?Yii::t('yii','Get a new code'):$this->buttonLabel;
			$button=$this->buttonType==='button'?'ajaxButton':'ajaxLink';
			$html=CHtml::$button($label,$url,array('success'=>'js:function(html){jQuery("#'.$id.'").attr("src",html)}'),$this->buttonOptions);
			$js="jQuery('#$id').after(\"".CJavaScript::quote($html).'");';
			$cs->registerScript('Yii.CCaptcha#'.$id,$js);
		}

		if($this->clickableImage)
		{
			$js="jQuery('#$id').click(function(){"
				.CHtml::ajax(array(
					'url'=>$url,
					'success'=>"js:function(html){jQuery('#$id').attr('src',html)}",
				)).'});';
			$cs->registerScript('Yii.CCaptcha#2'.$id,$js);
		}
	}

	/*
	 * Checks if GD with FreeType support is loadded
	 * @return boolean true if GD with FreeType support is loaded, otherwise false
	 * @since 1.1.5
	 */
	public static function checkRequirements()
	{
		if (extension_loaded('gd'))
		{
			$gdinfo=gd_info();
			if( $gdinfo['FreeType Support'])
				return true;
		}
		return false;
	}
}

<?php
/**
 * CCaptcha class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
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
 * CCaptcha also renders a button next to the CAPTCHA image. Clicking on the button
 * will change the CAPTCHA image to be new one.
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
	 * @var sring the ID of the action that should provide CAPTCHA image. Defaults to 'captcha'.
	 * The action must belong to the controller rendering this widget.
	 */
	public $captchaAction='captcha';
	/**
	 * @var boolean whether to display a button next to the CAPTCHA image. Clicking on the button
	 * will cause the CAPTCHA image to be changed to a new one. Defaults to true.
	 */
	public $showRefreshButton=true;
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
		$this->renderImage();
		$this->registerClientScript();
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
		$url=$this->getController()->createUrl($this->captchaAction);
		$alt=isset($imageOptions['alt'])?$imageOptions['alt']:'';
		echo CHtml::image($url,$alt,$this->imageOptions);
	}

	/**
	 * Registers the needed client scripts.
	 * @since 1.0.2
	 */
	public function registerClientScript()
	{
		if($this->showRefreshButton)
		{
			$cs=Yii::app()->clientScript;
			$id=$this->imageOptions['id'];
			$cs->registerScript('Yii.CCaptcha#'.$id,'dummy');
			$label=$this->buttonLabel===null?Yii::t('yii','Get a new code'):$this->buttonLabel;
			$button=$this->buttonType==='button'?'ajaxButton':'ajaxLink';
			$url=$this->getController()->createUrl($this->captchaAction,array(CCaptchaAction::REFRESH_GET_VAR=>true));
			$html=CHtml::$button($label,$url,array('success'=>'js:function(html){jQuery("#'.$id.'").attr("src",html)}'));
			$js="jQuery('img#$id').after(\"".CJavaScript::quote($html).'");';
			$cs->registerScript('Yii.CCaptcha#'.$id,$js);
		}
	}
}

<?php

/**
 *
 */
class CFormCaptcha extends CCaptcha
{
	/**
	 * @var CModel
	 */
	public $model;
	/**
	 * @var string
	 */
	public $attribute;
	/**
	 * @var string
	 */
	public $separator="<br/>\n";
	/**
	 * @var array
	 */
	public $inputOptions=array();

	public function run()
	{
		if(self::checkRequirements())
		{
			$this->renderImage();
			$this->registerClientScript();

			echo $this->separator;
			echo CHtml::activeTextField($this->model,$this->attribute,$this->inputOptions);
		}
		else
			throw new CException(Yii::t('yii','GD and FreeType PHP extensions are required.'));
	}
}

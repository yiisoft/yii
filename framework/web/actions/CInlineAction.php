<?php
/**
 * CInlineAction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CInlineAction represents an action that is defined as a controller method.
 *
 * The method name is like 'actionXYZ' where 'XYZ' stands for the action name.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.actions
 * @since 1.0
 */
class CInlineAction extends CAction
{
	/**
	 * @var string the error message to display when action parameter binding fails.
	 * If not set, it will use "Your request is invalid."
	 * @since 1.1.7
	 */
	public $errorMessage;
	/**
	 * @var array the input parameters (name=>value) that will be used to populate action parameters.
	 * If not set, it will use $_GET.
	 * @since 1.1.7
	 */
	public $inputParams;

	/**
	 * Runs the action.
	 * The action method defined in the controller is invoked.
	 * This method is required by {@link CAction}.
	 */
	public function run()
	{
		$controller=$this->getController();
		$methodName='action'.$this->getId();
		$method=new ReflectionMethod($controller,$methodName);
		if(($n=$method->getNumberOfParameters())>0)
		{
			if($this->errorMessage===null)
				$this->errorMessage=Yii::t('yii','Your request is invalid.');
			if($this->inputParams===null)
				$this->inputParams=$_GET;
			$params=array();
			foreach($method->getParameters() as $i=>$param)
			{
				$name=$param->getName();
				if(isset($this->inputParams[$name]))
				{
					if($param->isArray())
						$params[]=is_array($this->inputParams[$name]) ? $this->inputParams[$name] : array($this->inputParams[$name]);
					else if(!is_array($this->inputParams[$name]))
						$params[]=$this->inputParams[$name];
					else
						throw new CHttpException(400, $this->errorMessage);
				}
				else if($param->isDefaultValueAvailable())
					$params[]=$param->getDefaultValue();
				else
					throw new CHttpException(400, $this->errorMessage);
			}
			$method->invokeArgs($controller,$params);
		}
		else
			$controller->$methodName();
	}
}

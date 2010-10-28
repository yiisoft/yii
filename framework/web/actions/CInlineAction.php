<?php
/**
 * CInlineAction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
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
			$params=array();
			foreach($method->getParameters() as $i=>$param)
			{
				$name=$param->getName();
				if(isset($_GET[$name]))
				{
					if($param->isArray())
						$params[]=is_array($_GET[$name]) ? $_GET[$name] : array($_GET[$name]);
					else if(!is_array($_GET[$name]))
						$params[]=$_GET[$name];
					else
						throw new CHttpException(400,Yii::t('yii','Your request is invalid.'));
				}
				else if($param->isDefaultValueAvailable())
					$params[]=$param->getDefaultValue();
				else
					throw new CHttpException(400,Yii::t('yii','Your request is invalid.'));
			}
			$method->invokeArgs($controller,$params);
		}
		else
			$controller->$methodName();
	}
}

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
			if(preg_match_all('/^\s*\**\s*@param\s+(\w+)\s+\$(\w+)/m',$method->getDocComment(),$matches))
			{
				$types=array_combine($matches[2],$matches[1]);
				if($n!==count($types))
				{
					throw new CException(Yii::t('yii','The {method} has {count} parameters, but {count2} are declared in the method comment.',array(
						'{method}'=>get_class($controller).'::'.$methodName.'()',
						'{count}'=>$n,
						'{count2}'=>count($types),
					)));
				}
			}
			$params=array();
			foreach($method->getParameters() as $i=>$param)
			{
				$name=$param->getName();
				if(isset($types) && !isset($types[$name]))
				{
					throw new CException(Yii::t('yii','The comment of {method} does not match its parameter declaration. Parameter {name} is not found in the method comment.',array(
						'{method}'=>get_class($controller).'::'.$methodName.'()',
						'{name}'=>'$'.$name,
					)));
				}
				if(isset($_GET[$name]))
				{
					$value=$_GET[$name];
					if(isset($types[$name]))
					{
						$type=$types[$name];
						if($type==='integer' || $type==='int')
							$value=(int)$value;
						else if($type==='float' || $type==='double')
							$value=(float)$value;
						else if($type==='boolean' || $type==='bool')
							$value=(bool)$value;
					}
					$params[]=$value;
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

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
		if($method->getNumberOfParameters()>0)
		{
			preg_match_all('/^\s*\**\s*@param\s+(\w+)\s*/m',$method->getDocComment(),$matches);
			$types=$matches[1];
			$params=array();
			foreach($method->getParameters() as $i=>$param)
			{
				$name=$param->getName();
				if(isset($_GET[$name]))
				{
					$value=$_GET[$name];
					if(isset($types[$i]))
					{
						if($types[$i]==='integer' || $types[$i]==='int')
							$value=(int)$value;
						else if($types[$i]==='float' || $types[$i]==='double')
							$value=(float)$value;
						else if($types[$i]==='boolean' || $types[$i]==='bool')
							$value=(bool)$value;
					}
					$params[]=$value;
				}
				else if($param->isDefaultValueAvailable())
					$params[]=$param->getDefaultValue();
				else
					$controller->missingAction($this->getId());
			}
			$method->invokeArgs($controller,$params);
		}
		else
			$controller->$methodName();
	}
}

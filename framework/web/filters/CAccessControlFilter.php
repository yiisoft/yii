<?php
/**
 * CAccessControlFilter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CAccessControlFilter performs authorization checks for the specified actions.
 *
 * By enabling this filter, controller actions can be checked for access permissions.
 * Only when the user is allowed by one of the security rules, will he be able
 * to access the action.
 *
 * To specify the access rules, set the {@link setRules rules} property, which should
 * be an array of the rules. Each rule is specified as an array of the following structure:
 * <pre>
 * array('allow',                  // or 'deny'
 *      'actions'=>'edit, delete', // optional, list of action names (case insensitive) that this rule applies to
 *      'roles'=>'@',              // optional, list of roles (case sensitive) that this rule applies to.
 *                                 // Use * to represent all users, ? guest users, and @ authenticated users.
 *      'ips'=>'127.0.0.1',        // optional, list of IP address/patterns that this rule applies to
 *                                 // e.g. 127.0.0.1, 127.0.0.*
 *      'verbs'=>'GET, POST',      // optional, list of request types (case insensitive) that this rule applies to
 * )
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.filters
 * @since 1.0
 */
class CAccessControlFilter extends CFilter
{
	private $_rules=array();

	/**
	 * @return array list of access rules.
	 */
	public function getRules()
	{
		return $this->_rules;
	}

	/**
	 * @param array list of access rules.
	 */
	public function setRules($rules)
	{
		foreach($rules as $rule)
		{
			if(is_array($rule) && isset($rule[0]))
			{
				$r=new CAccessRule;
				$r->allow=$rule[0]==='allow';
				foreach(array_slice($rule,1) as $name=>$value)
					$r->$name=$value;
				$this->_rules[]=$r;
			}
		}
	}

	/**
	 * Performs the filtering.
	 * This method ensures that all access rules are passed before the action is executed.
	 * @param CFilterChain the filter chain that the filter is on.
	 */
	public function filter($filterChain)
	{
		$app=Yii::app();
		$request=$app->getRequest();
		$user=$app->getUser();
		$verb=$request->getRequestType();
		$ip=$request->getUserHostAddress();
		$action=$filterChain->action;

		foreach($this->_rules as $rule)
		{
			if(($allow=$rule->isUserAllowed($user,$action,$ip,$verb))>0) // allowed
				break;
			else if($allow<0) // denied
			{
				if($user->getIsGuest())
				{
					$user->loginRequired();
					return;
				}
				else
					throw new CHttpException(401,Yii::t('yii##Credential Required'));
			}
		}

		$filterChain->run();
	}
}


/**
 * CAccessRule represents an access rule that is managed by {@link CAccessControlFilter}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.filters
 * @since 1.0
 */
class CAccessRule extends CComponent
{
	/**
	 * @var boolean whether this is an 'allow' rule or 'deny' rule.
	 */
	public $allow;
	/**
	 * @var array list of actions that this rule applies to.
	 */
	public $actions;
	/**
	 * @var array list of user roles that this rule applies to.
	 */
	public $roles;
	/**
	 * @var array IP patterns.
	 */
	public $ips;
	/**
	 * @var array list of request types (e.g. GET, POST) that this rule applies to.
	 */
	public $verbs;


	/**
	 * Checks whether the Web user is allowed to perform the specified action.
	 * @param CWebUser the user object
	 * @param CAction the action to be performed
	 * @param string the request IP address
	 * @param string the request verb (GET, POST, etc.)
	 * @return integer 1 if the user is allowed, -1 if the user is denied, 0 if the rule does not apply to the user
	 */
	public function isUserAllowed($user,$action,$ip,$verb)
	{
		if($this->isActionMatched($action)
			&& $this->isRoleMatched($user)
			&& $this->isIpMatched($ip)
			&& $this->isVerbMatched($verb))
			return $this->allow ? 1 : -1;
		else
			return 0;
	}

	private function isActionMatched($action)
	{
		if(empty($this->actions))
			return true;
		if(is_string($this->actions))
			$this->actions=preg_split('/[\s,]+/',strtolower($this->actions),-1,PREG_SPLIT_NO_EMPTY);
		else
			$this->actions=array_map('strtolower',$this->actions);
		return in_array(strtolower($action->getId()),$this->actions);
	}

	private function isRoleMatched($user)
	{
		if(empty($this->roles))
			return true;
		if(is_string($this->roles))
			$this->roles=preg_split('/[\s,]+/',$this->roles,-1,PREG_SPLIT_NO_EMPTY);
		foreach($this->roles as $role)
		{
			if($role==='*')
				return true;
			else if($role==='?' && $user->getIsGuest())
				return true;
			else if($role==='@' && !$user->getIsGuest())
				return true;
			else if($user->isInRole($role))
				return true;
		}
		return false;
	}

	private function isIpMatched($ip)
	{
		if(empty($this->ips))
			return true;
		if(is_string($this->ips))
			$this->ips=preg_split('/[\s,]+/',strtolower($this->ips),-1,PREG_SPLIT_NO_EMPTY);
		foreach($this->ips as $rule)
		{
			if($rule==='*' || $rule===$ip || (($pos=strpos($rule,'*'))!==false && !strncmp($ip,$rule,$pos)))
				return true;
		}
		return false;
	}

	private function isVerbMatched($verb)
	{
		if(empty($this->verbs))
			return true;
		if(is_string($this->verbs))
			$this->verbs=preg_split('/[\s,]+/',strtoupper($this->verbs),-1,PREG_SPLIT_NO_EMPTY);
		return in_array($verb,$this->verbs);
	}
}

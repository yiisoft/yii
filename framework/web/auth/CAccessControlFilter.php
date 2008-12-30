<?php
/**
 * CAccessControlFilter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
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
 * array(
 *   'allow',  // or 'deny'
 *   // optional, list of action names (case insensitive) that this rule applies to
 *   'actions'=>array('edit, delete'),
 *   // optional, list of usernames (case insensitive) that this rule applies to
 *   // Use * to represent all users, ? guest users, and @ authenticated users
 *   'users'=>array('thomas', 'kevin'),
 *   // optional, list of roles that this rule applies to
 *   'roles'=>array('admin', 'editor'),
 *   // optional, list of IP address/patterns that this rule applies to
 *   // e.g. 127.0.0.1, 127.0.0.*
 *   'ips'=>array('127.0.0.1'),
 *   // optional, list of request types (case insensitive) that this rule applies to
 *   'verbs'=>array('GET', 'POST'),
 * )
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.auth
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
					$r->$name=array_map('strtolower',$value);
				$this->_rules[]=$r;
			}
		}
	}

	/**
	 * Performs the pre-action filtering.
	 * @param CFilterChain the filter chain that the filter is on.
	 * @return boolean whether the filtering process should continue and the action
	 * should be executed.
	 */
	protected function preFilter($filterChain)
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
					return false;
				}
				else
					throw new CHttpException(401,Yii::t('yii','You are not authorized to perform this action.'));
			}
		}

		return true;
	}
}


/**
 * CAccessRule represents an access rule that is managed by {@link CAccessControlFilter}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.auth
 * @since 1.0
 */
class CAccessRule extends CComponent
{
	/**
	 * @var boolean whether this is an 'allow' rule or 'deny' rule.
	 */
	public $allow;
	/**
	 * @var array list of actions that this rule applies to. The comparison is case-insensitive.
	 */
	public $actions;
	/**
	 * @var array list of user names that this rule applies to. The comparison is case-insensitive.
	 */
	public $users;
	/**
	 * @var array list of roles this rule applies to. For each role, the current user's
	 * {@link CWebUser::checkAccess} method will be invoked. If one of the invocations
	 * returns true, the rule will be applied.
	 * Note, you should mainly use roles in an "allow" rule because by definition,
	 * a role represents a permission collection.
	 * @see CAuthManager
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
			&& $this->isUserMatched($user)
			&& $this->isRoleMatched($user)
			&& $this->isIpMatched($ip)
			&& $this->isVerbMatched($verb))
			return $this->allow ? 1 : -1;
		else
			return 0;
	}

	private function isActionMatched($action)
	{
		return empty($this->actions) || in_array(strtolower($action->getId()),$this->actions);
	}

	private function isUserMatched($user)
	{
		if(empty($this->users))
			return true;
		foreach($this->users as $u)
		{
			if($u==='*')
				return true;
			else if($u==='?' && $user->getIsGuest())
				return true;
			else if($u==='@' && !$user->getIsGuest())
				return true;
			else if(!strcasecmp($u,$user->getName()))
				return true;
		}
		return false;
	}

	private function isRoleMatched($user)
	{
		if(empty($this->roles))
			return true;
		foreach($this->roles as $role)
		{
			if($user->checkAccess($role))
				return true;
		}
		return false;
	}

	private function isIpMatched($ip)
	{
		if(empty($this->ips))
			return true;
		foreach($this->ips as $rule)
		{
			if($rule==='*' || $rule===$ip || (($pos=strpos($rule,'*'))!==false && !strncmp($ip,$rule,$pos)))
				return true;
		}
		return false;
	}

	private function isVerbMatched($verb)
	{
		return empty($this->verbs) || in_array(strtolower($verb),$this->verbs);
	}
}

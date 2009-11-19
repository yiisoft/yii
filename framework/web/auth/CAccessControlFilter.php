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
 *   // optional, list of action IDs (case insensitive) that this rule applies to
 *   'actions'=>array('edit', 'delete'),
 *   // optional, list of controller IDs (case insensitive) that this rule applies to
 *   // This option is available since version 1.0.3.
 *   'controllers'=>array('post', 'admin/user'),
 *   // optional, list of usernames (case insensitive) that this rule applies to
 *   // Use * to represent all users, ? guest users, and @ authenticated users
 *   'users'=>array('thomas', 'kevin'),
 *   // optional, list of roles (case sensitive!) that this rule applies to.
 *   'roles'=>array('admin', 'editor'),
 *   // optional, list of IP address/patterns that this rule applies to
 *   // e.g. 127.0.0.1, 127.0.0.*
 *   'ips'=>array('127.0.0.1'),
 *   // optional, list of request types (case insensitive) that this rule applies to
 *   'verbs'=>array('GET', 'POST'),
 *   // optional, a PHP expression whose value indicates whether this rule applies
 *   // This option is available since version 1.0.3.
 *   'expression'=>'!$user->isGuest && $user->level==2',
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
				{
					if($name==='expression' || $name==='roles')
						$r->$name=$value;
					else
						$r->$name=array_map('strtolower',$value);
				}
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

		foreach($this->getRules() as $rule)
		{
			if(($allow=$rule->isUserAllowed($user,$filterChain->controller,$filterChain->action,$ip,$verb))>0) // allowed
				break;
			else if($allow<0) // denied
			{
				$this->accessDenied($user);
				return false;
			}
		}

		return true;
	}

	/**
	 * Denies the access of the user.
	 * This method is invoked when access check fails.
	 * @param IWebUser the current user
	 * @since 1.0.5
	 */
	protected function accessDenied($user)
	{
		if($user->getIsGuest())
			$user->loginRequired();
		else
			throw new CHttpException(403,Yii::t('yii','You are not authorized to perform this action.'));
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
	 * @var array list of action IDs that this rule applies to. The comparison is case-insensitive.
	 */
	public $actions;
	/**
	 * @var array list of controler IDs that this rule applies to. The comparison is case-insensitive.
	 * @since 1.0.4
	 */
	public $controllers;
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
	 * @var string a PHP expression whose value indicates whether this rule should be applied.
	 * In this expression, you can use <code>$user</code> which refers to <code>Yii::app()->user</code>.
	 * Starting from version 1.0.11, the expression can also be a valid PHP callback,
	 * including class method name (array(ClassName/Object, MethodName)),
	 * or anonymous function (PHP 5.3.0+). The function/method signature should be as follows:
	 * <pre>
	 * function foo($user, $rule) { ... }
	 * </pre>
	 * where $user is the current application user object and $rule is this access rule.
	 * @since 1.0.3
	 */
	public $expression;


	/**
	 * Checks whether the Web user is allowed to perform the specified action.
	 * @param CWebUser the user object
	 * @param CController the controller currently being executed
	 * @param CAction the action to be performed
	 * @param string the request IP address
	 * @param string the request verb (GET, POST, etc.)
	 * @return integer 1 if the user is allowed, -1 if the user is denied, 0 if the rule does not apply to the user
	 */
	public function isUserAllowed($user,$controller,$action,$ip,$verb)
	{
		if($this->isActionMatched($action)
			&& $this->isUserMatched($user)
			&& $this->isRoleMatched($user)
			&& $this->isIpMatched($ip)
			&& $this->isVerbMatched($verb)
			&& $this->isControllerMatched($controller)
			&& $this->isExpressionMatched($user))
			return $this->allow ? 1 : -1;
		else
			return 0;
	}

	/**
	 * @param CAction the action
	 * @return boolean whether the rule applies to the action
	 */
	protected function isActionMatched($action)
	{
		return empty($this->actions) || in_array(strtolower($action->getId()),$this->actions);
	}

	/**
	 * @param CAction the action
	 * @return boolean whether the rule applies to the action
	 */
	protected function isControllerMatched($controller)
	{
		return empty($this->controllers) || in_array(strtolower($controller->getId()),$this->controllers);
	}

	/**
	 * @param IWebUser the user
	 * @return boolean whether the rule applies to the user
	 */
	protected function isUserMatched($user)
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

	/**
	 * @param string the role name
	 * @return boolean whether the rule applies to the role
	 */
	protected function isRoleMatched($user)
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

	/**
	 * @param string the IP address
	 * @return boolean whether the rule applies to the IP address
	 */
	protected function isIpMatched($ip)
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

	/**
	 * @param string the request method
	 * @return boolean whether the rule applies to the request
	 */
	protected function isVerbMatched($verb)
	{
		return empty($this->verbs) || in_array(strtolower($verb),$this->verbs);
	}

	/**
	 * @param IWebUser the user
	 * @return boolean the expression value. True if the expression is not specified.
	 * @since 1.0.3
	 */
	protected function isExpressionMatched($user)
	{
		if($this->expression===null)
			return true;
		else
			return $this->evaluateExpression($this->expression, array('user'=>$user));
	}
}

<?php
/**
 * This file contains the Gii module.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Gii is the web-based Code Generator for the Yii Framework.
 * 
 * To use this module, you may insert the following code in the application configuration:
 * 
 * 'modules'=>array(
 * 		'gii'=>array(
 * 			'class'=>'system.gii.GiiModule',
 * 			'username'=>'dev',
 * 			'password'=>'yiidev',
 * 		),
 * ),
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @version $Id$
 * @package system.gii
 * @since 1.1
 */

class GiiModule extends CModule {

	/**
	 * The UserIdentity instance
	 * @var CUserIdentity
	 */
	public $user;
	
	/**
	 * The username for the gii authentication scheme
	 * @var unknown_type
	 */
	public $username;
	
	/**
	 * The password for the gii authentication scheme
	 * @var unknown_type
	 */
	public $password;
	
	
	/**
	 * (non-PHPdoc)
	 * @see framework/base/CModule#init()
	 */
	public function preinit()
	{
		$a = new CWebUser();
		// Verify the user login
		$this->_components = array('user'=>array(
							'class'=>'CWebUser',
							'stateKeyPrefix'=>md5('Yii.'.get_class($this).'.'.Yii::app()->getId()),
						)
					);
	}

	public function init(){
		
		// import the module-level models and components
		$this->setImport(array(
			'gii.components.*',

		));
	}


	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			
			// Check if the user has been logged in. If not, go to the module Login page
			
			if ($this->user->getIsGuest()){
				// $this->redirect('gii/login');
			}
			
			return true;
		}
		else
			return false;
	}
	
}
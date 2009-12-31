<?php
/**
 * This file contains the Gii module.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
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

class GiiModule extends CWebModule {


	
	/**
	 * The username for the gii authentication scheme
	 * @var string
	 */
	public $username;
	
	/**
	 * The password for the gii authentication scheme
	 * @var string
	 */
	public $password;
	
	/**
	 * The gii module default controller 
	 * @var string
	 */
	public $defaultController = 'gDefault';

	/**
	 * (non-PHPdoc)
	 * @see framework/base/CModule#init()
	 */
	public function preinit()
	{
		$this->setComponents(
			array(
				'user'=>array(
					'class'=>'CWebUser',
					'stateKeyPrefix'=>md5('Yii.'.get_class($this).'.'.Yii::app()->getId()),
					'loginUrl'=>array('/gii/default/login'),
				)
			)
		);
	}
	
	public function getUser(){
		return $this->getComponent('user', true);
	}

	/**
	 * (non-PHPdoc)
	 * @see framework/CModule#init()
	 */
	public function init(){
		// Check for username and password to be checked
	}


	/**
	 * (non-PHPdoc)
	 * @see framework/web/CWebModule#beforeControllerAction($controller, $action)
	 */
	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			// Check if the user has been logged in. If not, go to the module Login page
			if ($this->user->getIsGuest() && 
				(strcmp($controller->id, 'gDefault')!=0 || 
				(strcmp($controller->id, 'gDefault')==0 && strcmp($action->id, 'login')))) {
				Yii::app()->request->redirect(Yii::app()->createUrl('/gii/gDefault/login'));
			}
			// if this is not the login
			$controller->layout = 'gii.views.layouts.main';
			
			return true;
		}
		else
			return false;
	}
	
}
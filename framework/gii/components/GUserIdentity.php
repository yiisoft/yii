<?php
/**
 * This file contains the Gii module User Identity.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Gii is the web-based Code Generator for the Yii Framework.
 * 
 * This file is used as the CUserIdentity verifier for the Gii module
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @version $Id$
 * @package system.gii
 * @since 1.1
 */
class GUserIdentity extends CUserIdentity
{
	/**
	 * Authenticates a user.
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$module = Yii::app()->getModule('gii');
		
		if (strcmp($module->username, $this->username)==0 && strcmp($module->password, $this->password)==0){
			$this->errorCode=self::ERROR_NONE;
        }else{
            $this->errorCode = self::ERROR_UNKNOWN_IDENTITY;
        }
		return !$this->errorCode;
	}
}
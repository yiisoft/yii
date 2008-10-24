<?php
/**
 * This file contains classes implementing security manager feature.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CSecurityManager provides private keys, hashing and encryption functions.
 *
 * CSecurityManager is used by Yii components and applications for security-related purpose.
 * For example, it is used in cookie validation feature to prevent cookie data
 * from being tampered.
 *
 * CSecurityManager is mainly used to protect data from being tampered and viewed.
 * It can generate HMAC and encrypt the data. The private key used to generate HMAC
 * is set by {@link setValidationKey ValidationKey}. The key used to encrypt data is
 * specified by {@link setEncryptionKey EncryptionKey}. If the above keys are not
 * explicitly set, random keys will be generated and used.
 *
 * To protected data with HMAC, call {@link hashData()}; and to check if the data
 * is tampered, call {@link validateData()}, which will return the real data if
 * it is not tampered. The algorithm used to generated HMAC is specified by
 * {@link setValidation Validation}.
 *
 * To encrypt and decrypt data, call {@link encrypt()} and {@link decrypt()}
 * respectively, which uses 3DES encryption algorithm.  Note, the PHP Mcrypt
 * extension must be installed and loaded.
 *
 * CSecurityManager is a core application component that can be accessed via
 * {@link CApplication::getSecurityManager()}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.core
 * @since 1.0
 */
class CSecurityManager extends CApplicationComponent
{
	const STATE_VALIDATION_KEY='Yii.CSecurityManager.validationkey';
	const STATE_ENCRYPTION_KEY='Yii.CSecurityManager.encryptionkey';

	private $_validationKey;
	private $_encryptionKey;
	private $_validation='SHA1';

	/**
	 * @return string a randomly generated key
	 */
	protected function generateRandomKey()
	{
		return rand().rand().rand().rand();
	}

	/**
	 * @return string the private key used to generate HMAC.
	 * If the key is not explicitly set, a random one is generated and returned.
	 */
	public function getValidationKey()
	{
		if($this->_validationKey!==null)
			return $this->_validationKey;
		else
		{
			if(($key=Yii::app()->getGlobalState(self::STATE_VALIDATION_KEY))!==null)
				$this->setValidationKey($key);
			else
			{
				$key=$this->generateRandomKey();
				$this->setValidationKey($key);
				Yii::app()->setGlobalState(self::STATE_VALIDATION_KEY,$key);
			}
			return $this->_validationKey;
		}
	}

	/**
	 * @param string the key used to generate HMAC
	 * @throws CException if the key is empty
	 */
	public function setValidationKey($value)
	{
		if(!empty($value))
			$this->_validationKey=$value;
		else
			throw new CException(Yii::t('yii','CSecurityManager.validationKey cannot be empty.'));
	}

	/**
	 * @return string the private key used to encrypt/decrypt data.
	 * If the key is not explicitly set, a random one is generated and returned.
	 */
	public function getEncryptionKey()
	{
		if($this->_encryptionKey!==null)
			return $this->_encryptionKey;
		else
		{
			if(($key=Yii::app()->getGlobalState(self::STATE_ENCRYPTION_KEY))!==null)
				$this->setEncryptionKey($key);
			else
			{
				$key=$this->generateRandomKey();
				$this->setEncryptionKey($key);
				Yii::app()->setGlobalState(self::STATE_ENCRYPTION_KEY,$key);
			}
			return $this->_encryptionKey;
		}
	}

	/**
	 * @param string the key used to encrypt/decrypt data.
	 * @throws CException if the key is empty
	 */
	public function setEncryptionKey($value)
	{
		if(!empty($value))
			$this->_encryptionKey=$value;
		else
			throw new CException(Yii::t('yii','CSecurityManager.encryptionKey cannot be empty.'));
	}

	/**
	 * @return string hashing algorithm used to generate HMAC. Defaults to 'SHA1'.
	 */
	public function getValidation()
	{
		return $this->_validation;
	}

	/**
	 * @param string hashing algorithm used to generate HMAC. It must be either 'MD5' or 'SHA1'.
	 */
	public function setValidation($value)
	{
		if($value==='MD5' || $value==='SHA1')
			$this->_validation=$value;
		else
			throw new CException(Yii::t('yii','CSecurityManager.validation must be either "MD5" or "SHA1".'));
	}

	/**
	 * Encrypts data with {@link getEncryptionKey EncryptionKey}.
	 * @param string data to be encrypted.
	 * @return string the encrypted data
	 * @throws CException if PHP Mcrypt extension is not loaded
	 */
	public function encrypt($data)
	{
		if(extension_loaded('mcrypt'))
		{
			$module=mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
			$key=substr(md5($this->getEncryptionKey()),0,mcrypt_enc_get_key_size($module));
			srand();
			$iv=mcrypt_create_iv(mcrypt_enc_get_iv_size($module), MCRYPT_RAND);
			mcrypt_generic_init($module,$key,$iv);
			$encrypted=$iv.mcrypt_generic($module,$data);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
			return $encrypted;
		}
		else
			throw new CException(Yii::t('yii','CSecurityManager requires PHP mcrypt extension to be loaded in order to use data encryption feature.'));
	}

	/**
	 * Decrypts data with {@link getEncryptionKey EncryptionKey}.
	 * @param string data to be decrypted.
	 * @return string the decrypted data
	 * @throws CException if PHP Mcrypt extension is not loaded
	 */
	public function decrypt($data)
	{
		if(extension_loaded('mcrypt'))
		{
			$module=mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
			$key=substr(md5($this->getEncryptionKey()),0,mcrypt_enc_get_key_size($module));
			$ivSize=mcrypt_enc_get_iv_size($module);
			$iv=substr($data,0,$ivSize);
			mcrypt_generic_init($module,$key,$iv);
			$decrypted=mdecrypt_generic($module,substr($data,$ivSize));
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
			return rtrim($decrypted,"\0");
		}
		else
			throw new CException(Yii::t('yii','CSecurityManager requires PHP mcrypt extension to be loaded in order to use data encryption feature.'));
	}

	/**
	 * Prefixes data with an HMAC.
	 * @param string data to be hashed.
	 * @return string data prefixed with HMAC
	 */
	public function hashData($data)
	{
		$hmac=$this->computeHMAC($data);
		return $hmac.$data;
	}

	/**
	 * Validates if data is tampered.
	 * @param string data to be validated. The data must be previously
	 * generated using {@link hashData()}.
	 * @return string the real data with HMAC stripped off. False if the data
	 * is tampered.
	 */
	public function validateData($data)
	{
		$len=$this->_validation==='SHA1'?40:32;
		if(strlen($data)>=$len)
		{
			$hmac=substr($data,0,$len);
			$data2=substr($data,$len);
			return $hmac===$this->computeHMAC($data2)?$data2:false;
		}
		else
			return false;
	}

	/**
	 * Computes the HMAC for the data with {@link getValidationKey ValidationKey}.
	 * @param string data to be generated HMAC
	 * @return string the HMAC for the data
	 */
	protected function computeHMAC($data)
	{
		if($this->_validation==='SHA1')
		{
			$pack='H40';
			$func='sha1';
		}
		else
		{
			$pack='H32';
			$func='md5';
		}
		$key=$this->getValidationKey();
		$key=str_pad($func($key), 64, chr(0));
		return $func((str_repeat(chr(0x5C), 64) ^ substr($key, 0, 64)) . pack($pack, $func((str_repeat(chr(0x36), 64) ^ substr($key, 0, 64)) . $data)));
	}
}

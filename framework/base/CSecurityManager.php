<?php
/**
 * This file contains classes implementing security manager feature.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
 * {@link validation}.
 *
 * To encrypt and decrypt data, call {@link encrypt()} and {@link decrypt()}
 * respectively, which uses 3DES encryption algorithm.  Note, the PHP Mcrypt
 * extension must be installed and loaded.
 *
 * CSecurityManager is a core application component that can be accessed via
 * {@link CApplication::getSecurityManager()}.
 *
 * @property string $validationKey The private key used to generate HMAC.
 * If the key is not explicitly set, a random one is generated and returned.
 * @property string $encryptionKey The private key used to encrypt/decrypt data.
 * If the key is not explicitly set, a random one is generated and returned.
 * @property string $validation
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */
class CSecurityManager extends CApplicationComponent
{
	const STATE_VALIDATION_KEY='Yii.CSecurityManager.validationkey';
	const STATE_ENCRYPTION_KEY='Yii.CSecurityManager.encryptionkey';

	/**
	 * @var array known minimum lengths per encryption algorithm
	 */
	protected static $encryptionKeyMinimumLengths=array(
		'blowfish'=>4,
		'arcfour'=>5,
		'rc2'=>5,
	);

	/**
	 * @var boolean if encryption key should be validated
	 * @deprecated
	 */
	public $validateEncryptionKey=true;

	/**
	 * @var string the name of the hashing algorithm to be used by {@link computeHMAC}.
	 * See {@link https://php.net/manual/en/function.hash-algos.php hash-algos} for the list of possible
	 * hash algorithms. Note that if you are using PHP 5.1.1 or below, you can only use 'sha1' or 'md5'.
	 *
	 * Defaults to 'sha1', meaning using SHA1 hash algorithm.
	 * @since 1.1.3
	 */
	public $hashAlgorithm='sha1';
	/**
	 * @var mixed the name of the crypt algorithm to be used by {@link encrypt} and {@link decrypt}.
	 * This will be passed as the first parameter to {@link https://php.net/manual/en/function.mcrypt-module-open.php mcrypt_module_open}.
	 *
	 * This property can also be configured as an array. In this case, the array elements will be passed in order
	 * as parameters to mcrypt_module_open. For example, <code>array('rijndael-128', '', 'ofb', '')</code>.
	 *
	 * Defaults to AES
	 *
	 * Note: MCRYPT_RIJNDAEL_192 and MCRYPT_RIJNDAEL_256 are *not* AES-192 and AES-256. The numbers of the MCRYPT_RIJNDAEL
	 * constants refer to the block size, whereas the numbers of the AES variants refer to the key length. AES is Rijndael
	 * with a block size of 128 bits and a key length of 128 bits, 192 bits or 256 bits. So to use AES in Mcrypt, you need
	 * MCRYPT_RIJNDAEL_128 and a key with 16 bytes (AES-128), 24 bytes (AES-192) or 32 bytes (AES-256). The other two
	 * Rijndael variants in Mcrypt should be avoided, because they're not standardized and have been analyzed much less
	 * than AES.
	 *
	 * @since 1.1.3
	 */
	public $cryptAlgorithm='rijndael-128';

	private $_validationKey;
	private $_encryptionKey;
	private $_mbstring;


	public function init()
	{
		parent::init();
		$this->_mbstring=extension_loaded('mbstring');
	}

	/**
	 * @return string a randomly generated private key.
	 * @deprecated in favor of {@link generateRandomString()} since 1.1.14. Never use this method.
	 */
	protected function generateRandomKey()
	{
		return $this->generateRandomString(32);
	}

	/**
	 * @return string the private key used to generate HMAC.
	 * If the key is not explicitly set, a random one is generated and returned.
	 * @throws CException in case random string cannot be generated.
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
				if(($key=$this->generateRandomString(32,true))===false)
					if(($key=$this->generateRandomString(32,false))===false)
						throw new CException(Yii::t('yii',
							'CSecurityManager::generateRandomString() cannot generate random string in the current environment.'));
				$this->setValidationKey($key);
				Yii::app()->setGlobalState(self::STATE_VALIDATION_KEY,$key);
			}
			return $this->_validationKey;
		}
	}

	/**
	 * @param string $value the key used to generate HMAC
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
	 * @throws CException in case random string cannot be generated.
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
				if(($key=$this->generateRandomString(32,true))===false)
					if(($key=$this->generateRandomString(32,false))===false)
						throw new CException(Yii::t('yii',
							'CSecurityManager::generateRandomString() cannot generate random string in the current environment.'));
				$this->setEncryptionKey($key);
				Yii::app()->setGlobalState(self::STATE_ENCRYPTION_KEY,$key);
			}
			return $this->_encryptionKey;
		}
	}

	/**
	 * @param string $value the key used to encrypt/decrypt data.
	 * @throws CException if the key is empty
	 */
	public function setEncryptionKey($value)
	{
		$this->validateEncryptionKey($value);
		$this->_encryptionKey=$value;
	}

	/**
	 * This method has been deprecated since version 1.1.3.
	 * Please use {@link hashAlgorithm} instead.
	 * @return string -
	 * @deprecated
	 */
	public function getValidation()
	{
		return $this->hashAlgorithm;
	}

	/**
	 * This method has been deprecated since version 1.1.3.
	 * Please use {@link hashAlgorithm} instead.
	 * @param string $value -
	 * @deprecated
	 */
	public function setValidation($value)
	{
		$this->hashAlgorithm=$value;
	}

	/**
	 * Encrypts data.
	 * @param string $data data to be encrypted.
	 * @param string $key the decryption key. This defaults to null, meaning using {@link getEncryptionKey EncryptionKey}.
	 * @return string the encrypted data
	 * @throws CException if PHP Mcrypt extension is not loaded or key is invalid
	 */
	public function encrypt($data,$key=null)
	{
		if($key===null)
			$key=$this->getEncryptionKey();
		$this->validateEncryptionKey($key);
		$module=$this->openCryptModule();
		srand();
		$iv=@mcrypt_create_iv(mcrypt_enc_get_iv_size($module), MCRYPT_RAND);
		@mcrypt_generic_init($module,$key,$iv);
		$encrypted=$iv.@mcrypt_generic($module,$data);
		@mcrypt_generic_deinit($module);
		@mcrypt_module_close($module);
		return $encrypted;
	}

	/**
	 * Decrypts data
	 * @param string $data data to be decrypted.
	 * @param string $key the decryption key. This defaults to null, meaning using {@link getEncryptionKey EncryptionKey}.
	 * @return string the decrypted data
	 * @throws CException if PHP Mcrypt extension is not loaded or key is invalid
	 */
	public function decrypt($data,$key=null)
	{
		if($key===null)
			$key=$this->getEncryptionKey();
		$this->validateEncryptionKey($key);
		$module=$this->openCryptModule();
		$ivSize=@mcrypt_enc_get_iv_size($module);
		$iv=$this->substr($data,0,$ivSize);
		@mcrypt_generic_init($module,$key,$iv);
		$decrypted=@mdecrypt_generic($module,$this->substr($data,$ivSize,$this->strlen($data)));
		@mcrypt_generic_deinit($module);
		@mcrypt_module_close($module);
		return rtrim($decrypted,"\0");
	}

	/**
	 * Opens the mcrypt module with the configuration specified in {@link cryptAlgorithm}.
	 * @throws CException if failed to initialize the mcrypt module or PHP mcrypt extension
	 * @return resource the mycrypt module handle.
	 * @since 1.1.3
	 */
	protected function openCryptModule()
	{
		if(extension_loaded('mcrypt'))
		{
			if(is_array($this->cryptAlgorithm))
				$module=@call_user_func_array('mcrypt_module_open',$this->cryptAlgorithm);
			else
				$module=@mcrypt_module_open($this->cryptAlgorithm,'', MCRYPT_MODE_CBC,'');

			if($module===false)
				throw new CException(Yii::t('yii','Failed to initialize the mcrypt module.'));

			return $module;
		}
		else
			throw new CException(Yii::t('yii','CSecurityManager requires PHP mcrypt extension to be loaded in order to use data encryption feature.'));
	}

	/**
	 * Prefixes data with an HMAC.
	 * @param string $data data to be hashed.
	 * @param string $key the private key to be used for generating HMAC. Defaults to null, meaning using {@link validationKey}.
	 * @return string data prefixed with HMAC
	 */
	public function hashData($data,$key=null)
	{
		return $this->computeHMAC($data,$key).$data;
	}

	/**
	 * Validates if data is tampered.
	 * @param string $data data to be validated. The data must be previously
	 * generated using {@link hashData()}.
	 * @param string $key the private key to be used for generating HMAC. Defaults to null, meaning using {@link validationKey}.
	 * @return string the real data with HMAC stripped off. False if the data
	 * is tampered.
	 */
	public function validateData($data,$key=null)
	{
		if (!is_string($data))
			return false;

		$len=$this->strlen($this->computeHMAC('test'));
		if($this->strlen($data)>=$len)
		{
			$hmac=$this->substr($data,0,$len);
			$data2=$this->substr($data,$len,$this->strlen($data));
			return $this->compareString($hmac,$this->computeHMAC($data2,$key))?$data2:false;
		}
		else
			return false;
	}

	/**
	 * Computes the HMAC for the data with {@link getValidationKey validationKey}. This method has been made public
	 * since 1.1.14.
	 * @param string $data data to be generated HMAC.
	 * @param string|null $key the private key to be used for generating HMAC. Defaults to null, meaning using
	 * {@link validationKey} value.
	 * @param string|null $hashAlgorithm the name of the hashing algorithm to be used.
	 * See {@link https://php.net/manual/en/function.hash-algos.php hash-algos} for the list of possible
	 * hash algorithms. Note that if you are using PHP 5.1.1 or below, you can only use 'sha1' or 'md5'.
	 * Defaults to null, meaning using {@link hashAlgorithm} value.
	 * @return string the HMAC for the data.
	 * @throws CException on unsupported hash algorithm given.
	 */
	public function computeHMAC($data,$key=null,$hashAlgorithm=null)
	{
		if($key===null)
			$key=$this->getValidationKey();
		if($hashAlgorithm===null)
			$hashAlgorithm=$this->hashAlgorithm;

		if(function_exists('hash_hmac'))
			return hash_hmac($hashAlgorithm,$data,$key);

		if(0===strcasecmp($hashAlgorithm,'sha1'))
		{
			$pack='H40';
			$func='sha1';
		}
		elseif(0===strcasecmp($hashAlgorithm,'md5'))
		{
			$pack='H32';
			$func='md5';
		}
		else
		{
			throw new CException(Yii::t('yii','Only SHA1 and MD5 hashing algorithms are supported when using PHP 5.1.1 or below.'));
		}
		if($this->strlen($key)>64)
			$key=pack($pack,$func($key));
		if($this->strlen($key)<64)
			$key=str_pad($key,64,chr(0));
		$key=$this->substr($key,0,64);
		return $func((str_repeat(chr(0x5C), 64) ^ $key) . pack($pack, $func((str_repeat(chr(0x36), 64) ^ $key) . $data)));
	}

	/**
	 * Generate a random ASCII string. Generates only [0-9a-zA-z_~] characters which are all
	 * transparent in raw URL encoding.
	 * @param integer $length length of the generated string in characters.
	 * @param boolean $cryptographicallyStrong set this to require cryptographically strong randomness.
	 * @return string|boolean random string or false in case it cannot be generated.
	 * @since 1.1.14
	 */
	public function generateRandomString($length,$cryptographicallyStrong=true)
	{
		if(($randomBytes=$this->generateRandomBytes($length+2,$cryptographicallyStrong))!==false)
			return strtr($this->substr(base64_encode($randomBytes),0,$length),array('+'=>'_','/'=>'~'));
		return false;
	}

	/**
	 * Generates a string of random bytes.
	 * @param integer $length number of random bytes to be generated.
	 * @param boolean $cryptographicallyStrong whether to fail if a cryptographically strong
	 * result cannot be generated. The method attempts to read from a cryptographically strong
	 * pseudorandom number generator (CS-PRNG), see
	 * {@link https://en.wikipedia.org/wiki/Cryptographically_secure_pseudorandom_number_generator#Requirements Wikipedia}.
	 * However, in some runtime environments, PHP has no access to a CS-PRNG, in which case
	 * the method returns false if $cryptographicallyStrong is true. When $cryptographicallyStrong is false,
	 * the method always returns a pseudorandom result but may fall back to using {@link generatePseudoRandomBlock}.
	 * This method does not guarantee that entropy, from sources external to the CS-PRNG, was mixed into
	 * the CS-PRNG state between each successive call. The caller can therefore expect non-blocking
	 * behavior, unlike, for example, reading from /dev/random on Linux, see
	 * {@link https://eprint.iacr.org/2006/086.pdf Gutterman et al 2006}.
	 * @return boolean|string generated random binary string or false on failure.
	 * @since 1.1.14
	 */
	public function generateRandomBytes($length,$cryptographicallyStrong=true)
	{
		$bytes='';
		if(function_exists('openssl_random_pseudo_bytes'))
		{
			$bytes=openssl_random_pseudo_bytes($length,$strong);
			if($this->strlen($bytes)>=$length && ($strong || !$cryptographicallyStrong))
				return $this->substr($bytes,0,$length);
		}

		if(function_exists('mcrypt_create_iv') &&
			($bytes=@mcrypt_create_iv($length, MCRYPT_DEV_URANDOM))!==false &&
			$this->strlen($bytes)>=$length)
		{
			return $this->substr($bytes,0,$length);
		}

		if(($file=@fopen('/dev/urandom','rb'))!==false &&
			($bytes=@fread($file,$length))!==false &&
			(fclose($file) || true) &&
			$this->strlen($bytes)>=$length)
		{
			return $this->substr($bytes,0,$length);
		}

		$i=0;
		while($this->strlen($bytes)<$length &&
			($byte=$this->generateSessionRandomBlock())!==false &&
			++$i<3)
		{
			$bytes.=$byte;
		}
		if($this->strlen($bytes)>=$length)
			return $this->substr($bytes,0,$length);

		if ($cryptographicallyStrong)
			return false;

		while($this->strlen($bytes)<$length)
			$bytes.=$this->generatePseudoRandomBlock();
		return $this->substr($bytes,0,$length);
	}

	/**
	 * Generate a pseudo random block of data using several sources. On some systems this may be a bit
	 * better than PHP's {@link mt_rand} built-in function, which is not really random.
	 * @return string of 64 pseudo random bytes.
	 * @since 1.1.14
	 */
	public function generatePseudoRandomBlock()
	{
		$bytes='';

		if (function_exists('openssl_random_pseudo_bytes')
			&& ($bytes=openssl_random_pseudo_bytes(512))!==false
			&& $this->strlen($bytes)>=512)
		{
			return $this->substr($bytes,0,512);
		}

		for($i=0;$i<32;++$i)
			$bytes.=pack('S',mt_rand(0,0xffff));

		// On UNIX and UNIX-like operating systems the numerical values in `ps`, `uptime` and `iostat`
		// ought to be fairly unpredictable. Gather the non-zero digits from those.
		foreach(array('ps','uptime','iostat') as $command) {
			@exec($command,$commandResult,$retVal);
			if(is_array($commandResult) && !empty($commandResult) && $retVal==0)
				$bytes.=preg_replace('/[^1-9]/','',implode('',$commandResult));
		}

		// Gather the current time's microsecond part. Note: this is only a source of entropy on
		// the first call! If multiple calls are made, the entropy is only as much as the
		// randomness in the time between calls.
		$bytes.=$this->substr(microtime(),2,6);

		// Concatenate everything gathered, mix it with sha512. hash() is part of PHP core and
		// enabled by default but it can be disabled at compile time but we ignore that possibility here.
		return hash('sha512',$bytes,true);
	}

	/**
	 * Get random bytes from the system entropy source via PHP session manager.
	 * @return boolean|string 20-byte random binary string or false on error.
	 * @since 1.1.14
	 */
	public function generateSessionRandomBlock()
	{
		ini_set('session.entropy_length',20);
		if(ini_get('session.entropy_length')!=20)
			return false;

		// These calls are (supposed to be, according to PHP manual) safe even if
		// there is already an active session for the calling script.
		@session_start();
		@session_regenerate_id();

		$bytes=session_id();
		if(!$bytes)
			return false;

		// $bytes has 20 bytes of entropy but the session manager converts the binary
		// random bytes into something readable. We have to convert that back.
		// SHA-1 should do it without losing entropy.
		return sha1($bytes,true);
	}

	/**
	 * Returns the length of the given string.
	 * If available uses the multibyte string function mb_strlen.
	 * @param string $string the string being measured for length
	 * @return integer the length of the string
	 */
	private function strlen($string)
	{
		return $this->_mbstring ? mb_strlen($string,'8bit') : strlen($string);
	}

	/**
	 * Returns the portion of string specified by the start and length parameters.
	 * If available uses the multibyte string function mb_substr
	 * @param string $string the input string. Must be one character or longer.
	 * @param integer $start the starting position
	 * @param integer $length the desired portion length
	 * @return string the extracted part of string, or FALSE on failure or an empty string.
	 */
	private function substr($string,$start,$length)
	{
		return $this->_mbstring ? mb_substr($string,$start,$length,'8bit') : substr($string,$start,$length);
	}
    
	/**
	 * Checks if a key is valid for {@link cryptAlgorithm}.
	 * @param string $key the key to check
	 * @return boolean the validation result
	 * @throws CException if the supported key lengths of the cipher are unknown
	 */
	protected function validateEncryptionKey($key)
	{
		if(is_string($key))
		{
			$cryptAlgorithm = is_array($this->cryptAlgorithm) ? $this->cryptAlgorithm[0] : $this->cryptAlgorithm;

			$supportedKeyLengths=@mcrypt_module_get_supported_key_sizes($cryptAlgorithm);

			if($supportedKeyLengths)
			{
				if(!in_array($this->strlen($key),$supportedKeyLengths)) {
					throw new CException(Yii::t('yii','Encryption key length can be {keyLengths}.',array('{keyLengths}'=>implode(',',$supportedKeyLengths))));
				}
			}
			elseif(isset(self::$encryptionKeyMinimumLengths[$cryptAlgorithm]))
			{
				$minLength=self::$encryptionKeyMinimumLengths[$cryptAlgorithm];
				$maxLength=@mcrypt_module_get_algo_key_size($cryptAlgorithm);
				if($this->strlen($key)<$minLength || $this->strlen($key)>$maxLength)
					throw new CException(Yii::t('yii','Encryption key length must be between {minLength} and {maxLength}.',array('{minLength}'=>$minLength,'{maxLength}'=>$maxLength)));
			}
			else
				throw new CException(Yii::t('yii','Failed to validate key. Supported key lengths of cipher not known.'));
		}
		else
			throw new CException(Yii::t('yii','Encryption key should be a string.'));
	}
    
	/**
	 * Decrypts legacy ciphertext which was produced by the old, broken implementation of encrypt().
	 * @deprecated use only to convert data encrypted prior to 1.1.16
	 * @param string $data data to be decrypted.
	 * @param string $key the decryption key. This defaults to null, meaning the key should be loaded from persistent storage.
	 * @param string|array $cipher the algorithm to be used
	 * @return string the decrypted data
	 * @throws CException if PHP Mcrypt extension is not loaded
	 * @throws CException if the key is missing
	 */
	public function legacyDecrypt($data,$key=null,$cipher='des')
	{
		if (!$key)
		{
			$key=Yii::app()->getGlobalState(self::STATE_ENCRYPTION_KEY);
			if(!$key)
				throw new CException(Yii::t('yii','No encryption key specified.'));
			$key = md5($key);
		}

		if(extension_loaded('mcrypt'))
		{
			if(is_array($cipher))
				$module=@call_user_func_array('mcrypt_module_open',$cipher);
			else
				$module=@mcrypt_module_open($cipher,'', MCRYPT_MODE_CBC,'');

			if($module===false)
				throw new CException(Yii::t('yii','Failed to initialize the mcrypt module.'));
		}
		else
			throw new CException(Yii::t('yii','CSecurityManager requires PHP mcrypt extension to be loaded in order to use data encryption feature.'));

		$derivedKey=$this->substr($key,0,@mcrypt_enc_get_key_size($module));
		$ivSize=@mcrypt_enc_get_iv_size($module);
		$iv=$this->substr($data,0,$ivSize);
		@mcrypt_generic_init($module,$derivedKey,$iv);
		$decrypted=@mdecrypt_generic($module,$this->substr($data,$ivSize,$this->strlen($data)));
		@mcrypt_generic_deinit($module);
		@mcrypt_module_close($module);
		return rtrim($decrypted,"\0");
	}

	/**
	 * Performs string comparison using timing attack resistant approach.
	 * @see https://codereview.stackexchange.com/questions/13512
	 * @param string $expected string to compare.
	 * @param string $actual user-supplied string.
	 * @return boolean whether strings are equal.
	 */
	public function compareString($expected,$actual)
	{
		$expected.="\0";
		$actual.="\0";
		$expectedLength=$this->strlen($expected);
		$actualLength=$this->strlen($actual);
		$diff=$expectedLength-$actualLength;
		for($i=0;$i<$actualLength;$i++)
			$diff|=(ord($actual[$i])^ord($expected[$i%$expectedLength]));
		return $diff===0;
	}

	/**
	 * Masks a token to make it uncompressible.
	 * Applies a random mask to the token and prepends the mask used to the result making the string always unique.
	 * Used to mitigate BREACH attack by randomizing how token is outputted on each request.
	 * @param string $token An unmasked token.
	 * @return string A masked token.
	 * @since 1.1.18
	 */
	public function maskToken($token)
	{
		// The number of bytes in a mask is always equal to the number of bytes in a token.
		$mask=$this->generateRandomString($this->strlen($token));
		return strtr(base64_encode($mask.($mask^$token)),'+/','-_');
	}

	/**
	 * Unmasks a token previously masked by `maskToken`.
	 * @param string $maskedToken A masked token.
	 * @return string An unmasked token, or an empty string in case of token format is invalid.
	 * @since 1.1.18
	 */
	public function unmaskToken($maskedToken)
	{
		$decoded=base64_decode(strtr($maskedToken,'-_','+/'));
		$length=$this->strlen($decoded)/2;
		// Check if the masked token has an even length.
		if(!is_int($length))
			return '';
		return $this->substr($decoded,$length,$length)^$this->substr($decoded,0,$length);
	}
}

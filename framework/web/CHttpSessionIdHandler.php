<?php
/**
 * CHttpSessionIdHandler class file.
 *
 * @link https://www.yiiframework.com/
 * @copyright 2008-2026 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * CHttpSessionIdHandler adds session ID creation and storage-aware validation on PHP 7.1+.
 * PHP 7.0 uses CHttpSessionHandler so PHP retains its legacy session hash settings.
 *
 * @package system.web
 * @since 1.1.33
 */
class CHttpSessionIdHandler extends CHttpSessionHandlerBase implements SessionHandlerInterface
{
	/**
	 * Creates a new session ID.
	 * @return string the new session ID
	 */
	public function create_sid()
	{
		$length=(int)ini_get('session.sid_length');
		$bitsPerCharacter=(int)ini_get('session.sid_bits_per_character');
		if($length<1)
			$length=32;
		if($bitsPerCharacter<4 || $bitsPerCharacter>6)
			$bitsPerCharacter=4;

		$alphabet=substr('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ,-',0,1 << $bitsPerCharacter);
		$mask=(1 << $bitsPerCharacter)-1;
		$bytes=random_bytes($length);
		$id='';
		for($i=0;$i<$length;++$i)
			$id.=$alphabet[ord($bytes[$i]) & $mask];

		return $id;
	}

	/**
	 * Validates a session ID.
	 * @param string $id the session ID
	 * @return bool whether the session ID is valid
	 */
	public function validateId($id)
	{
		return $this->_session->validateSession($id);
	}
}

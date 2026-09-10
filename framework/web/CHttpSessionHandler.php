<?php
/**
 * CHttpSessionHandler class file.
 *
 * @author EFH Sollewijn Gelpke <efhsg@live.nl>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * CHttpSessionHandler is an adapter that implements SessionHandlerInterface for CHttpSession.
 *
 * Delegates all calls to CHttpSession's openSession/closeSession/etc. methods,
 * allowing subclasses like CDbHttpSession to work without modification.
 *
 * @package system.web
 * @since 1.1.32
 */
class CHttpSessionHandler implements SessionHandlerInterface
{
	/**
	 * @var CHttpSession
	 */
	private $_session;

	/**
	 * Constructor.
     * @param CHttpSession $session
	 */
	public function __construct(CHttpSession $session)
	{
		$this->_session=$session;
	}

	/**
     * Initialize session.
	 * @param string $path
	 * @param string $name
	 * @return bool
	 */
	#[ReturnTypeWillChange]
	public function open($path, $name)
	{
		return $this->_session->openSession($path, $name);
	}

	/**
	 * Close the session.
     * @return bool
	 */
	#[ReturnTypeWillChange]
	public function close()
	{
		return $this->_session->closeSession();
	}

	/**
	 * Read session data.
     * @param string $id
	 * @return string|false
	 */
	#[ReturnTypeWillChange]
	public function read($id)
	{
		return $this->_session->readSession($id);
	}

	/**
	 * Write session data.
     * @param string $id
	 * @param string $data
	 * @return bool
	 */
	#[ReturnTypeWillChange]
	public function write($id, $data)
	{
		return $this->_session->writeSession($id, $data);
	}

	/**
	 * Destroy a session.
     * @param string $id
	 * @return bool
	 */
	#[ReturnTypeWillChange]
	public function destroy($id)
	{
		return $this->_session->destroySession($id);
	}

	/**
	 * Cleanup old sessions.
     * @param int $max_lifetime
	 * @return int|false
	 */
	#[ReturnTypeWillChange]
	public function gc($max_lifetime)
	{
		return $this->_session->gcSession($max_lifetime) ? 0 : false;
	}

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

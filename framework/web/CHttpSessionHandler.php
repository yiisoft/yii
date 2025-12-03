<?php
/**
 * CHttpSessionHandler class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * SessionHandlerInterface adapter for CHttpSession.
 * Delegates all calls to CHttpSession's openSession/closeSession/etc. methods,
 * allowing subclasses like CDbHttpSession to work without modification.
 */
class CHttpSessionHandler implements SessionHandlerInterface
{
    /**
     * @var CHttpSession
     */
    private $_session;

    /**
     * @param CHttpSession $session
     */
    public function __construct(CHttpSession $session)
    {
        $this->_session = $session;
    }

    /**
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
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function close()
    {
        return $this->_session->closeSession();
    }

    /**
     * @param string $id
     * @return string|false
     */
    #[ReturnTypeWillChange]
    public function read($id)
    {
        return $this->_session->readSession($id);
    }

    /**
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
     * @param string $id
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function destroy($id)
    {
        return $this->_session->destroySession($id);
    }

    /**
     * @param int $max_lifetime
     * @return int|false
     */
    #[ReturnTypeWillChange]
    public function gc($max_lifetime)
    {
        return $this->_session->gcSession($max_lifetime) ? 0 : false;
    }
}

<?php
/**
 * CRedisSentinelCache class file
 *
 * @author Otávio Sampaio <otaviofcs@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CRedisSentinelCache is a CRedisCache extension that implements Sentinel High Availability service.
 *
 * CRedisCache also supports {@link http://redis.io/commands/auth the AUTH command} of redis.
 * When the server needs authentication, you can set the {@link password} property to
 * authenticate with the server after connect.
 * 
 * More information at CRedisCache.
 * 
 * See {@link CCache} manual for common cache operations that are supported by CRedisSentinelCache.
 *
 * To use CRedisSentinelCache as the cache application component, configure the application as follows,
 * <pre>
 * array(
 *     'components'=>array(
 *         'cache'=>array(
 *             'class'=>'CRedisSentinelCache',
 *             'sentinels' => array(
 *                array(
 *                  'hostname' => '127.0.0.1',
 *                  'port' => 26379
 *                ),
 *             ),
 *             'sentinelMasterName'=>'mymaster',
 *             'database'=>0,
 *         ),
 *     ),
 * )
 * </pre>
 *
 * About Redis version:
 * http://redis.io/topics/sentinel
 * According to it own documentation:
 * 
 * Redis Sentinel is compatible with Redis 2.4.16 or greater, and Redis 2.6.0 
 * or greater, however it works better if used against Redis instances version 2.8.0 or greater.
 * 
 * @author Otávio Sampaio <otaviofcs@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @package system.caching
 * @since 1.1.15
 */
class CRedisSentinelCache extends CRedisCache
{
  
  public $sentinelTimeout = 5;
  
	/**
	 * @var array of sentinel servers. Set default array to standard Sentinel config
   * 127.0.0.1:26379
	 */
	public $sentinels = array(
      array(
        'hostname' => '127.0.0.1',
        'port' => 26379
      ),
  );
  
  public $sentinelMasterName = 'mymaster';

	/**
	 * Establishes a connection to the redis server.
	 * It does nothing if the connection has already been established.
	 * 
   * @throws CException if connecting fails
	 */
	protected function connect()
	{  
    if(!$this->getCurrentMasterConf()){
      $this->askSentinel();
    }
    try{
    	parent::connect();
    }catch(CException $ce){
      $this->removeCurrentMasterConf();
      throw new CException($ce->getMessage(), $ce->getCode(), $ce);
    }
	}
	
	protected function askSentinel(){
    $errorNumber ='';
		$errorDescription = '';
		foreach($this->sentinels as $sentinel){
      if(!isset($sentinel['hostname']) || !isset($sentinel['port'])){
        throw new Exception('Check your sentinel server config. It should contain host and port.');
      }
			$sentinelSocket=@stream_socket_client(
				$sentinel['hostname'].':'.$sentinel['port'],
				$errorNumber,
				$errorDescription,
				$this->sentinelTimeout
			);
			if($sentinelSocket){
        $this->_socket = $sentinelSocket;
        $retorno = $this->executeCommand('sentinel', array('get-master-addr-by-name', $this->sentinelMasterName));
        if($retorno != null && is_array($retorno) ){
          $this->hostname = $retorno[0];
          $this->port = $retorno[1];
          $this->saveCurrentMasterConf();
          $this->_socket = null;
          return;
        }
			}
		}
    throw new Exception('Could not retrieve master host/port.');
	}
  
  private function getCurrentMasterConf(){
    if($this->sentinelMasterName == null){
      throw new Exception('Set Sentinel Master name.');
    }
    if(!is_file($this->getSentinelMasterConfFile())){
      return false;
    }
    $f = fopen($this->getSentinelMasterConfFile(), 'r');
    $sConfig = fgets($f);
    $arConfig = unserialize($sConfig);
    $this->hostname = $arConfig['hostname'];
    $this->port = $arConfig['port'];
    fclose($f);
    return true;
  }
  
  private function saveCurrentMasterConf(){
    $arConfig = array('hostname' => $this->hostname, 'port' => $this->port);
    $f = fopen($this->getSentinelMasterConfFile(), 'w');
    fputs($f, serialize($arConfig));
    return fclose($f);
  }
    
  /**
   * Remove Redis Config Cache file path
   * @return true if successfully removed 
   */
  private function removeCurrentMasterConf(){
    if(!is_file($this->getSentinelMasterConfFile())){
      return true;
    }
    unlink($this->getSentinelMasterConfFile());
    $this->_socket = null;
    return true;
  }
  
  /**
   * Return Redis Config Cache file path
   * @return string with Redis cache file path
   */
  protected function getSentinelMasterConfFile(){
    return Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'redis-cache-' . $this->sentinelMasterName . '.conf';
  }
    
  /**
   * Handle a Redis Error and remove current master conf if
   * Redis message shows that the current server is in read-only mode
   * @param type $message
   * @throws CException
   */
  protected function handleError($message){
    if(preg_match('/READONLY/i', $message)){
      $this->removeCurrentMasterConf();
    }
    throw new CException( Yii::t('yii',"Redis Error: {message}", array('{message}'=>$message)) );
  }
}

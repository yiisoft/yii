<?php

/**
 * CMemCacheServerConfiguration represents the configuration data for a single memcache server.
 *
 * See {@link http://www.php.net/manual/en/function.Memcache-addServer.php}
 * for detailed explanation of each configuration property.
 *
 * @author  Qiang Xue <qiang.xue@gmail.com>
 * @package system.caching
 * @since   1.0
 */
class CMemCacheServerConfiguration extends CComponent
{
    /**
     * @var string memcache server hostname or IP address
     */
    public $host;
    /**
     * @var int memcache server port
     */
    public $port = 11211;
    /**
     * @var bool whether to use a persistent connection
     */
    public $persistent = true;
    /**
     * @var int probability of using this server among all servers.
     */
    public $weight = 1;
    /**
     * @var int value in seconds which will be used for connecting to the server
     */
    public $timeout = 15;
    /**
     * @var int how often a failed server will be retried (in seconds)
     */
    public $retryInterval = 15;
    /**
     * @var bool if the server should be flagged as online upon a failure
     */
    public $status = true;

    /**
     * Constructor.
     *
     * @param array $config list of memcache server configurations.
     *
     * @throws CException if the configuration is not an array
     */
    public function __construct($config)
    {
        if (is_array($config)) {
            foreach ($config as $key => $value) {
                $this->$key = $value;
            }
            if ($this->host === null) {
                throw new CException(Yii::t('yii', 'CMemCache server configuration must have "host" value.'));
            }
        } else {
            throw new CException(Yii::t('yii', 'CMemCache server configuration must be an array.'));
        }
    }
}
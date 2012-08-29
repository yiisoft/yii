<?php

/**
 * CRedisHttpSession class
 *
 * @author Mikhail Osher <miraagewow@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CRedisHttpSession extends {@link CHttpSession} by using Redis as session data storage.
 *
 * CRedisHttpSession stores session data prefixed by {@link cachePrefix} on servers
 * passed as {@link servers} parameter.
 *
 * Server parameter keys:
 * <ul>
 * <li>host - required</li>
 * <li>port - optional, defaults to 6379</li>
 * <li>prefix - optional, overrides {@link cachePrefix} parameter</li>
 * <li>weight - optional, used to balance multiple servers</li>
 * <li>timeout - optional, server specified timeout, overrides {@link timeout} parameter</li>
 * </ul>
 *
 * Here is just simple configuration:
 * <code>
 * 'components' => array(
 *     'session' => array(
 *         'class' => 'CRedisHttpSession',
 *         'servers' => array(
 *              array('host' => 'localhost'),
 *          ),
 *     }
 * )
 * </code>
 *
 * @author Mikhail Osher <miraagewow@gmail.com>
 * @package system.web
 */
class CRedisHttpSession extends CHttpSession
{
    /**
     * @var string Cache prefix key
     */
    public $prefix = 'YiiSession';

    /**
     * @var integer Connection timeout. Redis' default is 86400
     */
    public $timeout = 60;

    /**
     * @var array Redis servers
     */
    public $servers = array();

    /**
     * Initialize
     * @see https://github.com/nicolasff/phpredis#session-handler-new
     */
    public function init()
    {
        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', $this->getSavePath());
        parent::init();
    }

    /**
     * Get save path
     *
     * @return string
     */
    public function getSavePath()
    {
        if (empty($this->servers) || !is_array($this->servers))
            throw new CException(Yii::t('yii','CRedisHttpSession.servers must be not empty array'));

        $servers = array();

        foreach ($this->servers as $server)
        {
            empty($server['port']) && $server['port'] = 6379;
            empty($server['prefix']) && $server['prefix'] = $this->prefix;
            empty($server['timeout']) && $server['timeout'] = $this->timeout;

            if (isset($server['weight'])) {
                $weight = 'weight=' . $server['weight'];
            } else {
                $weight = '';
            }

            $dsn = 'tcp://' . $server['host'] . ':' . $server['port']
                 . '?prefix=' . $server['prefix'] . '&timeout=' . $server['timeout']
                 . $weight;

            array_push($servers, $dsn);
        }

        return join(', ', $servers);
    }
}

?>
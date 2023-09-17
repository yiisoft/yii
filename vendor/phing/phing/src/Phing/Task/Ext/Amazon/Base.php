<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

namespace Phing\Task\Ext\Amazon;

use Phing\Exception\BuildException;
use Phing\Task;

/**
 * Abstract Service_Amazon class.
 *
 * Implements common methods & properties used by all Amazon services
 *
 * @extends  Task
 * @version  $ID$
 * @package  phing.tasks.ext
 * @author   Andrei Serdeliuc <andrei@serdeliuc.ro>
 * @abstract
 */
abstract class Base extends Task
{
    /**
     * Collection of set options
     *
     * We set these magically so we can also load then from the environment
     *
     * (default value: array())
     *
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @param string $var
     * @param mixed $val
     */
    public function __set($var, $val)
    {
        $this->options[$var] = $val;
    }

    /**
     * Property getter
     *
     * If the property hasn't been previously set (through the task call normally),
     * it will try to load it from the project
     *
     * This way, we can define global properties for the "Amazon" service, like key and secret
     *
     * @param  mixed $var
     * @return mixed
     */
    public function __get($var)
    {
        if (!isset($this->$var)) {
            if (!($val = $this->getProject()->getProperty('amazon.' . strtolower($var)))) {
                return false;
            }

            return $val;
        }

        return $this->options[$var];
    }

    /**
     * @param string $var
     * @return bool
     */
    public function __isset($var)
    {
        return array_key_exists($var, $this->options);
    }

    /**
     * @param string $key
     * @throws BuildException if $key is an empty string
     */
    public function setKey($key)
    {
        if (empty($key) || !is_string($key)) {
            throw new BuildException('Key must be a non empty string');
        }

        $this->key = $key;
    }

    /**
     * @return string
     *
     * @throws BuildException if key is not set
     */
    public function getKey()
    {
        if (!($key = $this->key)) {
            throw new BuildException('Key is not set');
        }

        return $key;
    }

    /**
     * @param string $secret
     * @throws BuildException if $secret is a empty string
     */
    public function setSecret($secret)
    {
        if (empty($secret) || !is_string($secret)) {
            throw new BuildException('Secret must be a non empty string');
        }

        $this->secret = $secret;
    }

    /**
     * @return string
     *
     * @throws BuildException if secret is not set
     */
    public function getSecret()
    {
        if (!($secret = $this->secret)) {
            throw new BuildException('Secret is not set');
        }

        return $this->secret;
    }
}

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

namespace Phing\Task\Ext\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use Phing\Exception\BuildException;
use Phing\Task;
use Phing\Type\Parameter;
use Psr\Http\Message\ResponseInterface;

/**
 * Base class for GuzzleHttp-backed tasks
 *
 * Handles nested <config /> and <header /> tags, contains a method for
 * GuzzleHttp instance creation
 *
 * @package phing.tasks.ext
 * @author  Alexey Borzov <avb@php.net>
 */
abstract class HttpTask extends Task
{
    /**
     * Holds the request URL
     *
     * @var string
     */
    protected $url = null;

    /**
     * Holds additional header data
     *
     * @var Parameter[]
     */
    protected $headers = [];

    /**
     * Holds additional config data for GuzzleHttp
     *
     * @var Parameter[]
     */
    protected $configData = [];

    /**
     * Holds the request method
     *
     * @var string
     */
    protected $method = 'GET';

    /**
     * Holds the authentication user name
     *
     * @var string
     */
    protected $authUser = '';

    /**
     * Holds the authentication password
     *
     * @var string
     */
    protected $authPassword = '';

    /**
     * Holds the authentication scheme
     *
     * @var string
     */
    protected $authScheme = 'basic';

    /**
     * @var HandlerStack
     */
    protected static $handlerStack = null;

    /**
     * @return HandlerStack
     */
    public static function getHandlerStack(): HandlerStack
    {
        if (self::$handlerStack === null) {
            self::$handlerStack = HandlerStack::create();
        }
        return self::$handlerStack;
    }

    /**
     * Load the necessary environment for running this task.
     *
     * @throws BuildException
     */
    public function init()
    {
        if (!class_exists('\GuzzleHttp\Client')) {
            throw new BuildException(
                get_class($this) . ' depends on Guzzle being installed '
                . 'and on include_path.',
                $this->getLocation()
            );
        }
    }

    /**
     * Sets the request URL
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Creates, configures, and sends a request
     *
     * @param array $options
     * @return ResponseInterface
     */
    protected function request($options = [])
    {
        // set the authentication data
        if (!empty($this->authUser)) {
            $options['auth'] = [
                $this->authUser,
                $this->authPassword,
                $this->authScheme
            ];
        }

        if (!empty($this->headers)) {
            $options['headers'] = [];

            foreach ($this->headers as $header) {
                $options['headers'][$header->getName()] = $header->getValue();
            }
        }

        foreach (array_keys($this->getProject()->getProperties()) as $propName) {
            if (0 === strpos($propName, 'phing.http.')) {
                $options[substr($propName, 11)] = (string) $this->getProject()->getProperty($propName);
            }
        }

        foreach ($this->configData as $parameter) {
            $options[$parameter->getName()] = $parameter->getValue();
        }

        $client = new Client(['handler' => self::$handlerStack]);

        return $client->request($this->method, $this->url, $options);
    }

    /**
     * Processes the server's response
     *
     * @param  ResponseInterface $response
     * @return void
     * @throws BuildException
     */
    abstract protected function processResponse(ResponseInterface $response);

    /**
     * Makes a HTTP request and processes its response
     *
     * @throws BuildException
     * @throws Exception
     */
    public function main()
    {
        if (!isset($this->url)) {
            throw new BuildException("Required attribute 'url' is missing");
        }

        $this->processResponse($this->request());
    }

    /**
     * Creates an additional header for this task
     *
     * @return Parameter The created header
     */
    public function createHeader()
    {
        $num = array_push($this->headers, new Parameter());

        return $this->headers[$num - 1];
    }

    /**
     * Creates a config parameter for this task
     *
     * @return Parameter The created config parameter
     */
    public function createConfig()
    {
        $num = array_push($this->configData, new Parameter());

        return $this->configData[$num - 1];
    }

    /**
     * Sets the authentication user name
     *
     * @param string $user
     */
    public function setAuthUser($user)
    {
        $this->authUser = $user;
    }

    /**
     * Sets the authentication password
     *
     * @param string $password
     */
    public function setAuthPassword($password)
    {
        $this->authPassword = $password;
    }

    /**
     * Sets the authentication scheme
     *
     * @param string $scheme
     */
    public function setAuthScheme($scheme)
    {
        $this->authScheme = $scheme;
    }
}

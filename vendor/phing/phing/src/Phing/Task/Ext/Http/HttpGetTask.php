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
use Phing\Exception\BuildException;
use Phing\Project;

/**
 * A HTTP download task.
 *
 * Downloads a file via HTTP GET method and saves it to a specified directory
 *
 * @package phing.tasks.ext
 * @author  Ole Markus With <o.with@sportradar.com>
 */
class HttpGetTask extends HttpTask
{
    /**
     * Holds the filename to store the output in
     *
     * @var string
     */
    protected $filename;

    /**
     * Holds the save location
     *
     * @var string
     */
    protected $dir;

    /**
     * Holds value for "ssl_verify_peer" option
     *
     * @var boolean
     */
    protected $sslVerifyPeer = true;

    /**
     * Holds value for "follow_redirects" option
     *
     * @var null|bool
     */
    protected $followRedirects;

    /**
     * Holds the proxy
     *
     * @var string
     */
    protected $proxy;

    private $quiet = false;

    /**
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function request($options = [])
    {
        if (!isset($this->dir)) {
            throw new BuildException("Required attribute 'dir' is missing", $this->getLocation());
        }

        $options['verify'] = $this->sslVerifyPeer;

        if (isset($this->proxy)) {
            $options['proxy'] = $this->proxy;
        }
        if ($this->followRedirects !== null) {
            $options['allow_redirects'] = $this->followRedirects;
        }

        $response = parent::request($options);

        $this->log("Fetching " . $this->url);

        return $response;
    }

    /**
     * Saves the response body to a specified directory
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return void
     */
    protected function processResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        if ($response->getStatusCode() != 200) {
            throw new BuildException(
                "Request unsuccessful. Response from server: " . $response->getStatusCode()
                . " " . $response->getReasonPhrase(),
                $this->getLocation()
            );
        }

        $content = $response->getBody();
        $disposition = $response->getHeader('content-disposition');

        if ($this->filename) {
            $filename = $this->filename;
        } elseif (
            !empty($disposition)
            && 0 == strpos($disposition[0], 'attachment')
            && preg_match('/filename="([^"]+)"/', $disposition[0], $m)
        ) {
            $filename = basename($m[1]);
        } else {
            $filename = basename(parse_url($this->url, PHP_URL_PATH));
        }

        if (!is_writable($this->dir)) {
            throw new BuildException("Cannot write to directory: " . $this->dir, $this->getLocation());
        }

        $filename = $this->dir . "/" . $filename;
        file_put_contents($filename, $content);

        $this->log("Contents from " . $this->url . " saved to $filename");
    }

    /**
     * Sets the filename to store the output in
     *
     * @param string $filename
     */
    public function setFilename($filename): void
    {
        $this->filename = $filename;
    }

    /**
     * Sets the save location
     *
     * @param string $dir
     */
    public function setDir($dir): void
    {
        $this->dir = $dir;
    }

    /**
     * Sets the ssl_verify_peer option
     *
     * @param bool $value
     */
    public function setSslVerifyPeer($value): void
    {
        $this->sslVerifyPeer = $value;
    }

    /**
     * Sets the follow_redirects option
     *
     * @param bool $value
     */
    public function setFollowRedirects($value): void
    {
        $this->followRedirects = $value;
    }

    /**
     * Sets the proxy
     *
     * @param string $proxy
     */
    public function setProxy($proxy): void
    {
        $this->proxy = $proxy;
    }

    /**
     * If true, set default log level to Project.MSG_ERR.
     *
     * @param boolean $v if "true" then be quiet
     */
    public function setQuiet($v): void
    {
        $this->quiet = $v;
    }

    /**
     * @param string $msg
     * @param int $msgLevel
     * @param Exception|null $t
     */
    public function log($msg, $msgLevel = Project::MSG_INFO, Exception $t = null)
    {
        if (!$this->quiet || $msgLevel <= Project::MSG_ERR) {
            parent::log($msg, $msgLevel, $t);
        }
    }
}

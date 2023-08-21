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

namespace Phing\Task\Ext;

use Phing\Exception\BuildException;
use Phing\Project;
use Phing\Task;

/**
 * HipchatTask
 * Sends a simple hipchat notification.
 *
 * (Yeah, HipChat API has lots of more awesome features than sending a lousy text notification
 *  but I refuse implementing more as long as the chat client lacks the most basic feature of
 *  sorting contacts. If you share my opinion then please upvote this feature request:
 *  https://jira.atlassian.com/browse/HCPUB-363 )
 *
 * <hipchat room="1337" authToken="********" color="red" notify="true" format="html">
 *     Hello &lt;i&gt;World&lt;/i&gt;!
 * </hipchat>
 *
 * @author  Suat Özgür <suat.oezguer@mindgeek.com>
 */
class HipchatTask extends Task
{
    private $domain = 'api.hipchat.com';
    private $room;
    private $authToken;
    private $color = 'yellow';
    private $notify = false;
    private $message;
    private $format = 'text';

    public function main()
    {
        if (null === $this->getRoom()) {
            throw new BuildException('(HipChat) room is not defined');
        }

        if (null === $this->getAuthToken()) {
            throw new BuildException('(HipChat) authToken is not defined');
        }

        $url =
            'https://' .
            $this->getDomain() .
            '/v2/room/' .
            $this->getRoom() .
            '/notification?auth_token=' .
            $this->getAuthToken();

        $data = [
            'color' => $this->getColor(),
            'message' => $this->getMessage(),
            'notify' => $this->isNotify(),
            'message_format' => $this->getFormat(),
        ];

        $result = $this->executeApiCall($url, $data);
        if (true !== $result) {
            $this->log($result, Project::MSG_WARN);
        } else {
            $this->log('HipChat notification sent.');
        }
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param $format
     */
    public function setFormat($format)
    {
        $format = ('text' != $format && 'html' != $format) ? 'text' : $format;
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param string $room
     */
    public function setRoom($room)
    {
        $this->room = $room;
    }

    /**
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * @param string $authToken
     */
    public function setAuthToken($authToken)
    {
        $this->authToken = $authToken;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isNotify()
    {
        return $this->notify;
    }

    /**
     * @param bool $notify
     */
    public function setNotify($notify)
    {
        $this->notify = $notify;
    }

    /**
     * @param $message
     */
    public function addText($message)
    {
        $this->message = trim($message);
    }

    private function executeApiCall($url, $data)
    {
        $postData = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        $response = curl_exec($ch);
        if ('' !== $response) {
            $result = json_decode($response, 1);

            return $result['error']['message'] . ' (' . $result['error']['code'] . ')';
        }

        return true;
    }
}

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
use Phing\Task;
use Phing\Type\Element\FileSetAware;

/**
 * Send an e-mail message.
 *
 * <mail tolist="user@example.org" subject="build complete">The build process is a success...</mail>
 *
 * @author  Michiel Rook <mrook@php.net>
 * @author  Francois Harvey at SecuriWeb (http://www.securiweb.net)
 */
class MailTask extends Task
{
    use FileSetAware;

    protected $tolist;
    protected $subject;
    protected $msg;
    protected $from;

    protected $backend = 'mail';
    protected $backendParams = [];

    public function main()
    {
        if (empty($this->from)) {
            throw new BuildException('Missing "from" attribute');
        }

        $this->log('Sending mail to ' . $this->tolist);

        if (!empty($this->filesets)) {
            $this->sendFilesets();

            return;
        }

        mail($this->tolist, $this->subject, $this->msg, "From: {$this->from}\n");
    }

    /**
     * Setter for message.
     *
     * @param $msg
     */
    public function setMsg($msg)
    {
        $this->setMessage($msg);
    }

    /**
     * Alias setter.
     *
     * @param $msg
     */
    public function setMessage($msg)
    {
        $this->msg = (string) $msg;
    }

    /**
     * Setter for subject.
     *
     * @param $subject
     */
    public function setSubject($subject)
    {
        $this->subject = (string) $subject;
    }

    /**
     * Setter for tolist.
     *
     * @param $tolist
     */
    public function setToList($tolist)
    {
        $this->tolist = $tolist;
    }

    /**
     * Alias for (deprecated) recipient.
     *
     * @param $recipient
     */
    public function setRecipient($recipient)
    {
        $this->tolist = (string) $recipient;
    }

    /**
     * Alias for to.
     *
     * @param $to
     */
    public function setTo($to)
    {
        $this->tolist = (string) $to;
    }

    /**
     * Supports the <mail>Message</mail> syntax.
     *
     * @param $msg
     */
    public function addText($msg)
    {
        $this->msg = (string) $msg;
    }

    /**
     * Sets email address of sender.
     *
     * @param $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * Sets PEAR Mail backend to use.
     *
     * @param $backend
     */
    public function setBackend($backend)
    {
        $this->backend = $backend;
    }

    /**
     * Sets PEAR Mail backend params to use.
     *
     * @param $backendParams
     */
    public function setBackendParams($backendParams)
    {
        $params = explode(',', $backendParams);

        foreach ($params as $param) {
            $values = explode('=', $param);

            if (count($values) < 1) {
                continue;
            }

            if (1 == count($values)) {
                $this->backendParams[] = $values[0];
            } else {
                $key = $values[0];
                $value = $values[1];
                $this->backendParams[$key] = $value;
            }
        }
    }

    protected function sendFilesets()
    {
        @include_once 'Mail.php';
        @include_once 'Mail/mime.php';

        if (!class_exists('Mail_mime')) {
            throw new BuildException('Need the pear/mail and pear/mail_mime packages installed');
        }

        $mime = new \Mail_mime(['text_charset' => 'UTF-8']);
        $hdrs = [
            'From' => $this->from,
            'Subject' => $this->subject,
        ];
        $mime->setTXTBody($this->msg);

        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($this->project);
            $fromDir = $fs->getDir($this->project);
            $srcFiles = $ds->getIncludedFiles();

            foreach ($srcFiles as $file) {
                $mime->addAttachment($fromDir . DIRECTORY_SEPARATOR . $file, 'application/octet-stream');
            }
        }

        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);

        $mail = \Mail::factory($this->backend, $this->backendParams);
        $mail->send($this->tolist, $hdrs, $body);
    }
}

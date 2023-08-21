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

namespace Phing\Listener;

use BadMethodCallException;
use Mail;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\IOException;
use Phing\Io\OutputStream;
use Phing\Phing;
use Phing\Project;
use Phing\Util\Properties;
use Phing\Util\StringHelper;

/**
 * Uses PEAR Mail package to send the build log to one or
 * more recipients.
 *
 * @author  Michiel Rook <mrook@php.net>
 */
class MailLogger extends DefaultLogger
{
    private $mailMessage = '';

    private $from = 'phing@phing.info';

    private $tolist;

    /**
     * Construct new MailLogger.
     */
    public function __construct()
    {
        parent::__construct();

        if (!class_exists('Mail')) {
            throw new BuildException('Need the pear/mail_mime package installed to send logs');
        }

        $tolist = Phing::getDefinedProperty('phing.log.mail.recipients');

        if (!empty($tolist)) {
            $this->tolist = $tolist;
        }
    }

    /**
     * Sends the mail.
     *
     * @see   DefaultLogger#buildFinished
     */
    public function buildFinished(BuildEvent $event)
    {
        parent::buildFinished($event);

        $project = $event->getProject();
        $properties = $project->getProperties();

        $filename = $properties['phing.log.mail.properties.file'];

        // overlay specified properties file (if any), which overrides project
        // settings
        $fileProperties = new Properties();
        $file = new File($filename);

        try {
            $fileProperties->load($file);
        } catch (IOException $ioe) {
            // ignore because properties file is not required
        }

        foreach ($fileProperties as $key => $value) {
            $properties['key'] = $project->replaceProperties($value);
        }

        $success = null === $event->getException();
        $prefix = $success ? 'success' : 'failure';
        $headers = [];

        try {
            $notify = StringHelper::booleanValue($this->getValue($properties, $prefix . '.notify', 'on'));
            if (!$notify) {
                return;
            }

            if (is_string(Phing::getDefinedProperty('phing.log.mail.subject'))) {
                $defaultSubject = Phing::getDefinedProperty('phing.log.mail.subject');
            } else {
                $defaultSubject = ($success) ? 'Build Success' : 'Build Failure';
            }
            $headers['From'] = $this->getValue($properties, 'from', $this->from);
            $headers['Reply-To'] = $this->getValue($properties, 'replyto', '');
            $headers['Cc'] = $this->getValue($properties, $prefix . '.cc', '');
            $headers['Bcc'] = $this->getValue($properties, $prefix . '.bcc', '');
            $headers['Body'] = $this->getValue($properties, $prefix . '.body', '');
            $headers['Subject'] = $this->getValue($properties, $prefix . '.subject', $defaultSubject);
            $tolist = $this->getValue($properties, $prefix . '.to', $this->tolist);
        } catch (BadMethodCallException $e) {
            $project->log($e->getMessage(), Project::MSG_WARN);
        }

        if (empty($tolist)) {
            return;
        }

        $mail = Mail::factory('mail');
        $mail->send($tolist, $headers, $this->mailMessage);
    }

    /**
     * @param string $message
     * @param int    $priority
     *
     * @see   DefaultLogger::printMessage
     */
    final protected function printMessage($message, OutputStream $stream, $priority)
    {
        if (null !== $message) {
            $this->mailMessage .= $message . "\n";
        }
    }

    /**
     * @param string $name
     * @param mixed  $defaultValue
     *
     * @throws BadMethodCallException
     */
    private function getValue(array $properties, $name, $defaultValue)
    {
        $propertyName = 'phing.log.mail.' . $name;
        $value = $properties[$propertyName];
        if (null === $value) {
            $value = $defaultValue;
        }
        if (null === $value) {
            throw new BadMethodCallException('Missing required parameter: ' . $propertyName);
        }

        return $value;
    }
}

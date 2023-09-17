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

namespace Phing\Task\Ext\Svn;

use Phing\Exception\BuildException;

/**
 * Copies a repository from the repository url to another
 *
 * @package phing.tasks.ext.svn
 * @since   2.3.0
 */
class SvnCopyTask extends SvnBaseTask
{
    private $message = "";

    /**
     * Sets the message
     *
     * @param $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Gets the message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->setup('copy');

        $this->log("Copying SVN repository from '" . $this->getRepositoryUrl() . "' to '" . $this->getToDir() . "'");

        $options = [];

        if (strlen($this->getMessage()) > 0) {
            $options['message'] = $this->getMessage();
        }

        $this->run([$this->getToDir()], $options);
    }
}

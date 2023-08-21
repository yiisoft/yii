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

namespace Phing\Task\System;

use Phing\Exception\BuildException;
use Phing\Task;

/**
 * @author Max Romanovsky <max.romanovsky@gmail.com>
 */
class AutoloaderTask extends Task
{
    public const DEFAULT_AUTOLOAD_PATH = 'vendor/autoload.php';

    private $autoloaderPath = self::DEFAULT_AUTOLOAD_PATH;

    /**
     * @return string
     */
    public function getAutoloaderPath()
    {
        return $this->autoloaderPath;
    }

    /**
     * @param string $autoloaderPath
     */
    public function setAutoloaderPath($autoloaderPath)
    {
        $this->autoloaderPath = $autoloaderPath;
    }

    /**
     *  Called by the project to let the task do it's work. This method may be
     *  called more than once, if the task is invoked more than once. For
     *  example, if target1 and target2 both depend on target3, then running
     *  <em>phing target1 target2</em> will run all tasks in target3 twice.
     *
     *  Should throw a BuildException if someting goes wrong with the build
     *
     *  This is here. Must be overloaded by real tasks.
     */
    public function main()
    {
        if (is_dir($this->autoloaderPath) || !is_readable($this->autoloaderPath)) {
            throw new BuildException(
                sprintf(
                    'Provided autoloader file "%s" is not a readable file',
                    $this->autoloaderPath
                )
            );
        }
        $this->log('Loading autoloader from ' . $this->autoloaderPath);

        include_once $this->autoloaderPath;
    }
}

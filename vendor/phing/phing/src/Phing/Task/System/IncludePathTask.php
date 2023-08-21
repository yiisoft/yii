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
use Phing\Phing;
use Phing\Project;
use Phing\Task;
use Phing\Type\Element\ClasspathAware;

/**
 * Adds a normalized path to the PHP include_path.
 *
 * This provides a way to alter the include_path without editing any global php.ini settings
 * or PHP_CLASSPATH environment variable.
 *
 * <code>
 *   <includepath classpath="new/path/here"/>
 * </code>
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class IncludePathTask extends Task
{
    use ClasspathAware;

    /**
     * Classname of task to register.
     *
     * @var string
     */
    private $classname;

    /**
     * Whether to prepend, append or replace the include path.
     *
     * @var string
     */
    private $mode = 'prepend';

    /**
     * @param string $mode
     *
     * @throws BuildException
     */
    public function setMode($mode)
    {
        if (!in_array($mode, ['append', 'prepend', 'replace'])) {
            throw new BuildException('Illegal mode: needs to be either append, prepend or replace');
        }

        $this->mode = $mode;
    }

    /**
     * Main entry point.
     */
    public function main()
    {
        // Apparently casting to (string) no longer invokes __toString() automatically.
        if (is_object($this->classpath)) {
            $classpath = $this->classpath->__toString();
        }

        if (empty($classpath)) {
            throw new BuildException('Provided classpath was empty.');
        }

        $curr_parts = Phing::explodeIncludePath();
        $add_parts = Phing::explodeIncludePath($classpath);
        $new_parts = array_diff($add_parts, $curr_parts);

        if ($new_parts) {
            $this->updateIncludePath($new_parts, $curr_parts);
        }
    }

    /**
     * @param array $new_parts
     * @param array $curr_parts
     */
    private function updateIncludePath($new_parts, $curr_parts)
    {
        $includePath = [];
        $verb = '';

        switch ($this->mode) {
            case 'append':
                $includePath = array_merge($curr_parts, $new_parts);
                $verb = 'Appending';

                break;

            case 'replace':
                $includePath = $new_parts;
                $verb = 'Replacing';

                break;

            case 'prepend':
                $includePath = array_merge($new_parts, $curr_parts);
                $verb = 'Prepending';

                break;
        }

        $this->log(
            $verb . ' new include_path components: ' . implode(PATH_SEPARATOR, $new_parts),
            Project::MSG_VERBOSE
        );

        set_include_path(implode(PATH_SEPARATOR, $includePath));
    }
}

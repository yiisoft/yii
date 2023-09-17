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

namespace Phing\Dispatch;

use Phing\Exception\BuildException;
use Phing\Task;
use Phing\UnknownElement;
use ReflectionClass;
use ReflectionException;

/**
 * Determines and Executes the action method for the task.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class DispatchUtils
{
    /**
     * Determines and Executes the action method for the task.
     *
     * @param object $task the task to execute
     *
     * @throws BuildException on error
     */
    public static function main($task)
    {
        $methodName = 'main';
        $dispatchable = null;

        try {
            if ($task instanceof Dispatchable) {
                $dispatchable = $task;
            } elseif ($task instanceof UnknownElement) {
                $ue = $task;
                $realThing = $ue->getRealThing();
                if (null != $realThing && $realThing instanceof Dispatchable && $realThing instanceof Task) {
                    $dispatchable = $realThing;
                }
            }
            if (null != $dispatchable) {
                $mName = null;

                $name = trim($dispatchable->getActionParameterName());
                if (empty($name)) {
                    throw new BuildException(
                        'Action Parameter Name must not be empty for Dispatchable Task.'
                    );
                }
                $mName = 'get' . ucfirst($name);

                try {
                    $c = new ReflectionClass($dispatchable);
                    $actionM = $c->getMethod($mName);
                    $o = $actionM->invoke($dispatchable);
                    $methodName = trim((string) $o);
                    if (empty($methodName)) {
                        throw new ReflectionException();
                    }
                } catch (ReflectionException $re) {
                    throw new BuildException(
                        "Dispatchable Task attribute '" . $name . "' not set or value is empty."
                    );
                }
                $executeM = $c->getMethod($methodName);
                $executeM->invoke($dispatchable);

                if ($task instanceof UnknownElement) {
                    $task->setRealThing(null);
                }
            } else {
                try {
                    $refl = new ReflectionClass($task);
                    $executeM = $refl->getMethod($methodName);
                } catch (ReflectionException $re) {
                    throw new BuildException('No public ' . $methodName . '() in ' . get_class($task));
                }
                $executeM->invoke($task);
                if ($task instanceof UnknownElement) {
                    $task->setRealThing(null);
                }
            }
        } catch (ReflectionException $e) {
            throw new BuildException($e);
        }
    }
}

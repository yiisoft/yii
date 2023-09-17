<?php

namespace Phing\Task\System\Property;

use Phing\Exception\BuildException;
use Phing\Task;
use Phing\Task\System\PropertyTask;

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
abstract class AbstractPropertySetterTask extends Task
{
    private $property;
    private $override = false;

    public function setOverride($override)
    {
        $this->override = $override;
    }

    public function setProperty($property)
    {
        $this->property = $property;
    }

    protected function validate()
    {
        if (null == $this->property) {
            throw new BuildException('You must specify a property to set.');
        }
    }

    protected function setPropertyValue($value)
    {
        if (null !== $value) {
            if ($this->override) {
                if (null == $this->getProject()->getUserProperty($this->property)) {
                    $this->getProject()->setProperty($this->property, $value);
                } else {
                    $this->getProject()->setUserProperty($this->property, $value);
                }
            } else {
                /**
                 * @var PropertyTask
                 */
                $p = $this->project->createTask('property');
                $p->setName($this->property);
                $p->setValue($value);
                $p->main();
            }
        }
    }
}

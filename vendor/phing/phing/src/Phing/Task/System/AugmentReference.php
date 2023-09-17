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

use LogicException;
use Phing\Exception\BuildException;
use Phing\Project;
use Phing\Task;
use Phing\TypeAdapter;
use Phing\UnknownElement;

/**
 * Phing task to dynamically augment a previously declared reference.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class AugmentReference extends Task implements TypeAdapter
{
    private $id;

    public function main()
    {
        $this->restoreWrapperId();
    }

    public function setProxy($o)
    {
        throw new LogicException(__METHOD__ . ' unsupported.');
    }

    public function getProxy()
    {
        if (null === $this->getProject()) {
            throw new LogicException($this->getTaskName() . 'Project owner unset');
        }
        $this->hijackId();
        $ref = $this->getProject()->getReference($this->id);
        if ($this->getProject()->hasReference($this->id) && !$ref instanceof UnknownElement) {
            $result = $this->getProject()->getReference($this->id);
            $this->log('project reference ' . $this->id . '=' . get_class($result), Project::MSG_DEBUG);

            return $result;
        }

        throw new BuildException('Unknown reference "' . $this->id . '"');
    }

    /**
     * Needed if two different targets reuse the same instance.
     */
    private function restoreWrapperId(): void
    {
        if (null !== $this->id) {
            $this->log('restoring augment wrapper ' . $this->id, Project::MSG_DEBUG);
            $wrapper = $this->getWrapper();
            $wrapper->setAttribute('id', $this->id);
            $wrapper->setElementTag($this->getTaskName());
            $this->id = null;
        }
    }

    private function hijackId(): void
    {
        if (null === $this->id) {
            $wrapper = $this->getWrapper();
            $this->id = $wrapper->getId();
            if (null === $this->id) {
                throw new BuildException($this->getTaskName() . " attribute 'id' unset");
            }
            $wrapper->setAttribute('id', null);
            $wrapper->removeAttribute('id');
            $wrapper->setElementTag('augmented reference "' . $this->id . '"');
        }
    }
}

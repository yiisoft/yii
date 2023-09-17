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
use Phing\Parser\ProjectConfigurator;
use Phing\Parser\XmlContext;
use Phing\Target;
use Phing\Task;

/**
 * Simple task which bind some targets to some defined extension point.
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class BindTargets extends Task
{
    /** @var string */
    private $extensionPoint;

    /** @var Target[] */
    private $targets = [];

    private $onMissingExtensionPoint = 'fail';

    public function setExtensionPoint(string $extensionPoint)
    {
        $this->extensionPoint = $extensionPoint;
    }

    public function setOnMissingExtensionPoint(string $onMissingExtensionPoint)
    {
        if (!in_array($onMissingExtensionPoint, ['fail', 'warn', 'ignore'], true)) {
            throw new BuildException('Invalid onMissingExtensionPoint: ' . $onMissingExtensionPoint);
        }
        $this->onMissingExtensionPoint = $onMissingExtensionPoint;
    }

    public function setTargets(string $target)
    {
        $this->targets = array_values(array_filter(array_map('trim', explode(',', $target))));
    }

    public function main()
    {
        if (null === $this->extensionPoint) {
            throw new BuildException('extensionPoint required', $this->getLocation());
        }

        if (null === $this->getOwningTarget() || '' !== $this->getOwningTarget()->getName()) {
            throw new BuildException('bindtargets only allowed as a top-level task');
        }

        /** @var XmlContext $ctx */
        $ctx = $this->getProject()->getReference(ProjectConfigurator::PARSING_CONTEXT_REFERENCE);

        foreach ($this->targets as $target) {
            $ctx->addExtensionPoint([$this->extensionPoint, $target, $this->onMissingExtensionPoint, null]);
        }
    }
}

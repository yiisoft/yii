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

namespace Phing\Task\System\Condition;

use Phing\Exception\BuildException;
use Phing\Util\SizeHelper;
use Throwable;

/**
 * Condition returns true if selected partition has the requested space, false otherwise.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class HasFreeSpaceCondition implements Condition
{
    /**
     * @var string
     */
    private $partition;

    /**
     * @var string
     */
    private $needed;

    /**
     * {@inheritdoc}
     *
     * @throws BuildException
     */
    public function evaluate(): bool
    {
        $this->validate();

        try {
            $free = disk_free_space($this->partition);
        } catch (Throwable $throwable) {
            // Only when "display errors" is enabled.
            throw new BuildException($throwable->getMessage());
        }

        if (false === $free) {
            throw new BuildException('Error while retrieving free space.');
        }

        return $free >= SizeHelper::fromHumanToBytes($this->needed);
    }

    /**
     * Set the partition/device to check.
     */
    public function setPartition(string $partition): void
    {
        $this->partition = $partition;
    }

    /**
     * Set the amount of free space required.
     */
    public function setNeeded(string $needed): void
    {
        $this->needed = $needed;
    }

    /**
     * @throws BuildException
     */
    private function validate(): void
    {
        if (null == $this->partition) {
            throw new BuildException('Please set the partition attribute.');
        }
        if (null == $this->needed) {
            throw new BuildException('Please set the needed attribute.');
        }
    }
}

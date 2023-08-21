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

namespace Phing\Task\System\Property;

use Phing\Exception\BuildException;

/**
 * PropertyCopy.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class PropertyCopy extends AbstractPropertySetterTask
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var bool
     */
    private $silent;

    // Default Constructor
    public function __construct()
    {
        parent::__construct();
        $this->from = null;
        $this->silent = false;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @param bool $silent
     */
    public function setSilent($silent)
    {
        $this->silent = $silent;
    }

    public function main()
    {
        $this->validate();

        $value = $this->getProject()->getProperty($this->from);

        if (null === $value && !$this->silent) {
            throw new BuildException("Property '{$this->from}' is not defined.");
        }

        if (null !== $value) {
            $this->setPropertyValue($value);
        }
    }

    protected function validate()
    {
        parent::validate();
        if (null === $this->from) {
            throw new BuildException("Missing the 'from' attribute.");
        }
    }
}

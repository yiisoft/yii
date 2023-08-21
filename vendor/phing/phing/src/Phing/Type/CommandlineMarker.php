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

namespace Phing\Type;

/**
 * Class to keep track of the position of an Argument.
 *
 * <p>This class is there to support the srcfile and targetfile
 * elements of &lt;execon&gt; and &lt;transform&gt; - don't know
 * whether there might be additional use cases.</p> --SB
 */
class CommandlineMarker
{
    /**
     * @var int
     */
    private $position;
    private $realPos = -1;
    private $outer;
    private $prefix;
    private $suffix;

    /**
     * @param int $position
     */
    public function __construct(Commandline $outer, $position)
    {
        $this->outer = $outer;
        $this->position = $position;
    }

    /**
     * Return the number of arguments that preceded this marker.
     *
     * <p>The name of the executable - if set - is counted as the
     * very first argument.</p>
     */
    public function getPosition()
    {
        if (-1 === $this->realPos) {
            $this->realPos = (null === $this->outer->executable ? 0 : 1);
            for ($i = 0; $i < $this->position; ++$i) {
                $arg = $this->outer->arguments[$i];
                $this->realPos += count($arg->getParts());
            }
        }

        return $this->realPos;
    }

    /**
     * Set the prefix to be placed in front of the inserted argument.
     *
     * @param string|null $prefix fixed prefix string
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix ?? '';
    }

    /**
     * Get the prefix to be placed in front of the inserted argument.
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the suffix to be placed at the end of the inserted argument.
     *
     * @param string|null $suffix fixed suffix string
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix ?? '';
    }

    /**
     * Get the suffix to be placed at the end of the inserted argument.
     */
    public function getSuffix()
    {
        return $this->suffix;
    }
}

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

use Phing\Exception\BuildException;
use Phing\Io\File;

/**
 * "Inner" class used for nested xml command line definitions.
 */
class CommandlineArgument
{
    public $escape = false;
    private $parts = [];
    private $outer;

    public function __construct(Commandline $outer)
    {
        $this->outer = $outer;
    }

    /**
     * @param bool $escape
     */
    public function setEscape($escape)
    {
        $this->escape = $escape;
    }

    /**
     * Sets a single commandline argument.
     *
     * @param string $value a single commandline argument
     */
    public function setValue(string $value)
    {
        $this->parts = [$value];
    }

    /**
     * Line to split into several commandline arguments.
     *
     * @param string $line line to split into several commandline arguments
     *
     * @throws BuildException
     */
    public function setLine($line)
    {
        if (null === $line) {
            return;
        }
        $this->parts = $this->outer::translateCommandline($line);
    }

    /**
     * Sets a single commandline argument and treats it like a
     * PATH - ensures the right separator for the local platform
     * is used.
     *
     * @param Path $value a single commandline argument
     */
    public function setPath(Path $value): void
    {
        $this->parts = [(string) $value];
    }

    /**
     * Sets a single commandline argument to the absolute filename
     * of the given file.
     *
     * @internal param a $value single commandline argument
     */
    public function setFile(File $value): void
    {
        $this->parts = [$value->getAbsolutePath()];
    }

    /**
     * Returns the parts this Argument consists of.
     *
     * @return array string[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }
}

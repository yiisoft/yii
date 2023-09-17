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

namespace Phing\Type\Element;

use Phing\Exception\BuildException;
use Phing\Type\Path;
use Phing\Type\Reference;

trait ClasspathAware
{
    /**
     * @var Path
     */
    protected $classpath;

    /**
     * Refid to already defined classpath.
     */
    protected $classpathId;

    /**
     * Returns the classpath.
     */
    public function getClasspath(): ?Path
    {
        return $this->classpath;
    }

    /**
     * @throws BuildException
     */
    public function setClasspath(Path $classpath): void
    {
        if (null === $this->classpath) {
            $this->classpath = $classpath;
        } else {
            $this->classpath->append($classpath);
        }
    }

    /**
     * @throws BuildException
     */
    public function createClasspath(): Path
    {
        if (null === $this->classpath) {
            $this->classpath = new Path();
        }

        return $this->classpath->createPath();
    }

    /**
     * Reference to a classpath to use when loading the files.
     *
     * @throws BuildException
     */
    public function setClasspathRef(Reference $r): void
    {
        $this->classpathId = $r->getRefId();
        $this->createClasspath()->setRefid($r);
    }
}

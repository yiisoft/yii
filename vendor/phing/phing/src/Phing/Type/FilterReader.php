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
use Phing\Io\FilterReader as IoFilterReader;

/**
 * A PhingFilterReader is a wrapper class that encloses the className
 * and configuration of a Configurable FilterReader.
 *
 * @author  Yannick Lecaillez <yl@seasonfive.com>
 *
 * @see     IoFilterReader
 */
class FilterReader extends DataType
{
    private $className;
    private $parameters = [];

    /** @var Path */
    private $classPath;

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Set the classpath to load the FilterReader through (attribute).
     *
     * @throws BuildException
     */
    public function setClasspath(Path $classpath)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if (null === $this->classPath) {
            $this->classPath = $classpath;
        } else {
            $this->classPath->append($classpath);
        }
    }

    /*
     * Set the classpath to load the FilterReader through (nested element).
     *
     * @return Path
     * @throws BuildException
     */
    public function createClasspath()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        if (null === $this->classPath) {
            $this->classPath = new Path($this->project);
        }

        return $this->classPath->createPath();
    }

    public function getClasspath()
    {
        return $this->classPath;
    }

    /**
     * @throws BuildException
     */
    public function setClasspathRef(Reference $r)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $o = $this->createClasspath();
        $o->setRefid($r);
    }

    public function addParam(Parameter $param)
    {
        $this->parameters[] = $param;
    }

    public function createParam()
    {
        $num = array_push($this->parameters, new Parameter());

        return $this->parameters[$num - 1];
    }

    /**
     * @return array
     */
    public function getParams()
    {
        // We return a COPY
        $ret = [];
        for ($i = 0, $size = count($this->parameters); $i < $size; ++$i) {
            $ret[] = clone $this->parameters[$i];
        }

        return $ret;
    }

    /*
     * Makes this instance in effect a reference to another FilterReader
     * instance.
     *
     * <p>You must not set another attribute or nest elements inside
     * this element if you make it a reference.</p>
     *
     * @param Reference $r the reference to which this instance is associated
     * @throws BuildException if this instance already has been configured.
    */

    /**
     * @throws BuildException
     */
    public function setRefid(Reference $r)
    {
        if ((0 !== count($this->parameters)) || (null !== $this->className)) {
            throw $this->tooManyAttributes();
        }
        $o = $r->getReferencedObject($this->getProject());
        if ($o instanceof FilterReader) {
            $this->setClassName($o->getClassName());
            $this->setClasspath($o->getClasspath());
            foreach ($o->getParams() as $p) {
                $this->addParam($p);
            }
        } else {
            $msg = $r->getRefId() . ' is not a ' . self::class;

            throw new BuildException($msg);
        }

        parent::setRefid($r);
    }
}

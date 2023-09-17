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
use Phing\Mapper\ChainedMapper;
use Phing\Mapper\CompositeMapper;
use Phing\Mapper\ContainerMapper;
use Phing\Mapper\CutDirsMapper;
use Phing\Mapper\FileNameMapper;
use Phing\Mapper\FirstMatchMapper;
use Phing\Mapper\FlattenMapper;
use Phing\Mapper\GlobMapper;
use Phing\Mapper\IdentityMapper;
use Phing\Mapper\MergeMapper;
use Phing\Mapper\RegexpMapper;
use Phing\Phing;
use Phing\Project;
use Phing\Util\StringHelper;

/**
 * Filename Mapper maps source file name(s) to target file name(s).
 *
 * Built-in mappers can be accessed by specifying they "type" attribute:
 * <code>
 * <mapper type="glob" from="*.php" to="*.php.bak"/>
 * </code>
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class Mapper extends DataType
{
    protected $type;
    protected $classname;
    protected $from;
    protected $to;

    /**
     * @var Path
     */
    protected $classpath;
    protected $classpathId;

    /**
     * @var ContainerMapper
     */
    private $container;

    public function __construct(Project $project)
    {
        parent::__construct();
        $this->project = $project;
    }

    /**
     * Set the classpath to be used when searching for component being defined.
     *
     * @param Path $classpath an Path object containing the classpath
     *
     * @throws BuildException
     */
    public function setClasspath(Path $classpath)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if (null === $this->classpath) {
            $this->classpath = $classpath;
        } else {
            $this->classpath->append($classpath);
        }
    }

    /**
     * Create the classpath to be used when searching for component being defined.
     */
    public function createClasspath()
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if (null === $this->classpath) {
            $this->classpath = new Path($this->project);
        }

        return $this->classpath->createPath();
    }

    /**
     * Reference to a classpath to use when loading the files.
     *
     * @throws BuildException
     */
    public function setClasspathRef(Reference $r)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->classpathId = $r->getRefId();
        $this->createClasspath()->setRefid($r);
    }

    /**
     * Set the type of FileNameMapper to use.
     *
     * @param string $type
     *
     * @throws BuildException
     */
    public function setType($type)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->type = $type;
    }

    /**
     * Add a nested <code>FileNameMapper</code>.
     *
     * @param Mapper $fileNameMapper the <code>FileNameMapper</code> to add
     *
     * @throws BuildException
     */
    public function add(Mapper $fileNameMapper)
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        if (null == $this->container) {
            if (null == $this->type && null == $this->classname) {
                $this->container = new CompositeMapper();
            } else {
                $m = $this->getImplementation();
                if ($m instanceof ContainerMapper) {
                    $this->container = $m;
                } else {
                    throw new BuildException("{$m} mapper implementation does not support nested mappers!");
                }
            }
        }
        $this->container->add($fileNameMapper);
        $this->checked = false;
    }

    /**
     * Add a Mapper.
     *
     * @param Mapper $mapper the mapper to add
     */
    public function addMapper(Mapper $mapper)
    {
        $this->add($mapper);
    }

    /**
     * Set the class name of the FileNameMapper to use.
     *
     * @param string $classname
     *
     * @throws BuildException
     */
    public function setClassname($classname)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->classname = $classname;
    }

    /**
     * Set the argument to FileNameMapper.setFrom.
     *
     * @param string $from
     *
     * @throws BuildException
     */
    public function setFrom($from)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->from = $from;
    }

    /**
     * Set the argument to FileNameMapper.setTo.
     *
     * @param string $to
     *
     * @throws BuildException
     */
    public function setTo($to)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->to = $to;
    }

    /**
     * Make this Mapper instance a reference to another Mapper.
     *
     * You must not set any other attribute if you make it a reference.
     *
     * @throws BuildException
     */
    public function setRefid(Reference $r)
    {
        if (null !== $this->type || null !== $this->from || null !== $this->to) {
            throw DataType::tooManyAttributes();
        }
        parent::setRefid($r);
    }

    /**
     * Factory, returns inmplementation of file name mapper as new instance.
     */
    public function getImplementation()
    {
        if ($this->isReference()) {
            $o = $this->getRef();
            if ($o instanceof FileNameMapper) {
                return $o;
            }
            if ($o instanceof Mapper) {
                return $o->getImplementation();
            }

            $od = null == $o ? 'null' : get_class($o);

            throw new BuildException($od . " at reference '" . $this->getRefId() . "' is not a valid mapper reference.");
        }

        if (null === $this->type && null === $this->classname && null == $this->container) {
            throw new BuildException('either type or classname attribute must be set for <mapper>');
        }

        if (null != $this->container) {
            return $this->container;
        }

        if (null !== $this->type) {
            switch ($this->type) {
                case 'chained':
                    $this->classname = ChainedMapper::class;

                    break;

                case 'composite':
                    $this->classname = CompositeMapper::class;

                    break;

                case 'cutdirs':
                    $this->classname = CutDirsMapper::class;

                    break;

                case 'identity':
                    $this->classname = IdentityMapper::class;

                    break;

                case 'firstmatch':
                    $this->classname = FirstMatchMapper::class;

                    break;

                case 'flatten':
                    $this->classname = FlattenMapper::class;

                    break;

                case 'glob':
                    $this->classname = GlobMapper::class;

                    break;

                case 'regexp':
                case 'regex':
                    $this->classname = RegexpMapper::class;

                    break;

                case 'merge':
                    $this->classname = MergeMapper::class;

                    break;

                default:
                    throw new BuildException("Mapper type {$this->type} not known");
            }
        }

        // get the implementing class
        $cls = Phing::import($this->classname, $this->classpath);

        $m = new $cls();
        $m->setFrom($this->from);
        $m->setTo($this->to);

        return $m;
    }

    /**
     * Performs the check for circular references and returns the referenced Mapper.
     */
    private function getRef()
    {
        $dataTypeName = StringHelper::substring(__CLASS__, strrpos(__CLASS__, '\\') + 1);

        return $this->getCheckedRef(__CLASS__, $dataTypeName);
    }
}

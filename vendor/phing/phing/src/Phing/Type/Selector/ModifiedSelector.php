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

namespace Phing\Type\Selector;

use Phing\Exception\BuildException;
use Phing\IntrospectionHelper;
use Phing\Io\File;
use Phing\Listener\BuildEvent;
use Phing\Listener\BuildListener;
use Phing\Phing;
use Phing\Project;
use Phing\Type\Parameter;
use Phing\Type\Selector\Modified\Algorithm;
use Phing\Type\Selector\Modified\Cache;
use Phing\Type\Selector\Modified\Comparator;
use Phing\Type\Selector\Modified\EqualComparator;
use Phing\Type\Selector\Modified\HashfileAlgorithm;
use Phing\Type\Selector\Modified\LastModifiedAlgorithm;
use Phing\Type\Selector\Modified\PropertiesCache;
use Phing\Util\StringHelper;
use Throwable;

/**
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class ModifiedSelector extends BaseExtendSelector implements BuildListener
{
    private const CACHE_PREFIX = 'cache.';
    private const ALGORITHM_PREFIX = 'algorithm.';
    private const COMPARATOR_PREFIX = 'comparator.';

    /** Cache name for later instantiation. */
    private $cacheName;

    /** User specified classname for Cache. */
    private $cacheClass;

    /** Algorithm name for later instantiation. */
    private $algoName;

    /** User specified classname for Algorithm. */
    private $algorithmClass;

    /** Comparator name for later instantiation. */
    private $compName;

    /** User specified classname for Comparator. */
    private $comparatorClass;

    private $update = true;
    private $selectDirectories = true;
    /** @var Cache */
    private $cache;
    /** @var Algorithm */
    private $algorithm;
    /** @var Comparator */
    private $comparator;
    private $delayUpdate = true;
    private $modified = 0;
    private $isConfigured = false;
    /** @var File */
    private $cachefile;
    /** @var Parameter[] */
    private $configParameter = [];
    /** @var Parameter[] */
    private $specialParameter = [];

    public function __toString(): string
    {
        $this->configure();

        return sprintf(
            '{modifiedselector update=%s seldirs=%s cache=%s algorithm=%s comparator=%s}',
            true === $this->update ? 'true' : 'false',
            true === $this->selectDirectories ? 'true' : 'false',
            $this->cache,
            $this->algorithm,
            $this->comparator
        );
    }

    /**
     * Get the cache type to use.
     *
     * @return Cache the enumerated cache type
     */
    public function getCache(): Cache
    {
        return $this->cache;
    }

    /**
     * Set the cache type to use.
     *
     * @param string $name an enumerated cache type
     */
    public function setCache(string $name): void
    {
        $this->cacheName = $name;
    }

    public function verifySettings()
    {
        $this->configure();
        if (null === $this->cache) {
            $this->setError('Cache must be set.');
        } elseif (null === $this->algorithm) {
            $this->setError('Algorithm must be set.');
        } elseif (!$this->cache->isValid()) {
            $this->setError('Cache must be proper configured.');
        } elseif (!$this->algorithm->isValid()) {
            $this->setError('Algorithm must be proper configured.');
        }
    }

    public function configure(): void
    {
        if ($this->isConfigured) {
            return;
        }
        $this->isConfigured = true;

        $p = $this->getProject();
        $filename = 'cache.properties';
        if (null !== $p) {
            // normal use inside Phing
            $this->cachefile = new File($p->getBasedir(), $filename);

            // set self as a BuildListener to delay cachefile saves
            $this->getProject()->addBuildListener($this);
        } else {
            // no reference to project - e.g. during normal JUnit tests
            $this->cachefile = new File($filename);
            $this->setDelayUpdate(false);
        }
        $defaultCache = new PropertiesCache($this->cachefile);
        $defaultAlgorithm = new HashfileAlgorithm();
        $defaultComparator = new EqualComparator();

        //
        // -----  Set the main attributes, pattern '*'  -----
        //
        foreach ($this->configParameter as $parameter) {
            if (strpos($parameter->getName(), '.') > 0) {
                // this is a *.* parameter for later use
                $this->specialParameter[] = $parameter;
            } else {
                $this->useParameter($parameter);
            }
        }
        $this->configParameter = [];

        // specify the algorithm classname
        if (null !== $this->algoName) {
            // use Algorithm defined via name
            if ('hashfile' === $this->algoName) {
                $this->algorithm = new HashfileAlgorithm();
            } elseif ('lastmodified' === $this->algoName) {
                $this->algorithm = new LastModifiedAlgorithm();
            }
        } elseif (null !== $this->algorithmClass) {
            // use Algorithm specified by classname
            $clz = Phing::import($this->algorithmClass);
            $this->algorithm = new $clz();
            if (!$this->algorithm instanceof Algorithm) {
                throw new BuildException($this->algorithmClass . ' is not an Algorithm.');
            }
        } else {
            // nothing specified - use default
            $this->algorithm = $defaultAlgorithm;
        }

        // specify the cache classname
        if (null !== $this->cacheName) {
            // use Cache defined via name
            if ('propertyfile' === $this->cacheName) {
                $this->cache = new PropertiesCache();
            }
        } elseif (null !== $this->cacheClass) {
            // use Cache specified by classname
            $clz = Phing::import($this->cacheClass);
            $this->cache = new $clz();
            if (!$this->cache instanceof Cache) {
                throw new BuildException($this->cacheClass . ' is not a Cache.');
            }
        } else {
            // nothing specified - use default
            $this->cache = $defaultCache;
        }

        // specify the comparator classname
        if (null !== $this->compName) {
            // use Algorithm defined via name
            if ('equal' === $this->compName) {
                $this->comparator = new EqualComparator();
            }
        } elseif (null !== $this->comparatorClass) {
            // use Comparator specified by classname
            $localComparator = Phing::import($this->comparatorClass);
            if (!$localComparator instanceof Comparator) {
                throw new BuildException($this->comparatorClass . ' is not a Comparator.');
            }
            $this->comparator = $localComparator;
        } else {
            // nothing specified - use default
            $this->comparator = $defaultComparator;
        }
        foreach ($this->specialParameter as $special) {
            $this->useParameter($special);
        }
        $this->specialParameter = [];
    }

    /**
     * Support for nested <code>&lt;param name="" value=""/&gt;</code> tags.
     * Parameter named <i>cache</i>, <i>algorithm</i>,
     * <i>comparator</i> or <i>update</i> are mapped to
     * the respective set-Method.
     * Parameter which names starts with <i>cache.</i> or
     * <i>algorithm.</i> or <i>comparator.</i> are tried
     * to set on the appropriate object via its set-methods.
     * Other parameters are invalid and an BuildException will
     * be thrown.
     *
     * @param Parameter $parameter Key and value as parameter object
     */
    public function useParameter(Parameter $parameter): void
    {
        $key = $parameter->getName();
        $value = $parameter->getValue();
        if ('cache' === $key) {
            $this->setCache($value);
        } elseif ('algorithm' === $key) {
            $this->setAlgorithm($value);
        } elseif ('comparator' === $key) {
            $this->setComparator($value);
        } elseif ('update' === $key) {
            $this->setUpdate(StringHelper::booleanValue($value));
        } elseif ('delayupdate' === $key) {
            $this->setDelayUpdate(StringHelper::booleanValue($value));
        } elseif ('seldirs' === $key) {
            $this->setSeldirs(StringHelper::booleanValue($value));
        } elseif (StringHelper::startsWith(self::CACHE_PREFIX, $key)) {
            $name = StringHelper::substring($key, strlen(self::CACHE_PREFIX));
            $this->tryToSetAParameter($this->cache, $name, $value);
        } elseif (StringHelper::startsWith(self::ALGORITHM_PREFIX, $key)) {
            $name = StringHelper::substring($key, strlen(self::ALGORITHM_PREFIX));
            $this->tryToSetAParameter($this->algorithm, $name, $value);
        } elseif (StringHelper::startsWith(self::COMPARATOR_PREFIX, $key)) {
            $name = StringHelper::substring($key, strlen(self::COMPARATOR_PREFIX));
            $this->tryToSetAParameter($this->comparator, $name, $value);
        } else {
            $this->setError('Invalid parameter ' . $key);
        }
    }

    /**
     * Support for <i>update</i> attribute.
     *
     * @param bool $update new value
     */
    public function setUpdate(bool $update): void
    {
        $this->update = $update;
    }

    /**
     * Support for <i>seldirs</i> attribute.
     *
     * @param bool $seldirs new value
     */
    public function setSeldirs(bool $seldirs): void
    {
        $this->selectDirectories = $seldirs;
    }

    /**
     * Support for nested &lt;param&gt; tags.
     *
     * @param Parameter $parameter the parameter object
     */
    public function addParam(Parameter $parameter)
    {
        $this->configParameter[] = $parameter;
    }

    /**
     * Defined in org.apache.tools.ant.types.Parameterizable.
     * Overwrite implementation in superclass because only special
     * parameters are valid.
     *
     * @see #addParam(String,Object)
     *
     * @param array $parameters the parameters to set
     */
    public function setParameters(array $parameters): void
    {
        parent::setParameters($parameters);
        foreach ($parameters as $param) {
            $this->configParameter[] = $param;
        }
    }

    public function buildStarted(BuildEvent $event)
    {
        // do nothing
    }

    public function buildFinished(BuildEvent $event)
    {
        if ($this->getDelayUpdate()) {
            $this->saveCache();
        }
    }

    /**
     * Getter for the delay update.
     *
     * @return bool true if we should delay for performance
     */
    public function getDelayUpdate(): bool
    {
        return $this->delayUpdate;
    }

    public function setDelayUpdate(bool $false): void
    {
        $this->delayUpdate = $false;
    }

    /**
     * Getter for the modified count.
     *
     * @return int $modified count
     */
    public function getModified(): int
    {
        return $this->modified;
    }

    /**
     * Setter for the modified count.
     *
     * @param int $modified count
     */
    public function setModified(int $modified): void
    {
        $this->modified = $modified;
    }

    /**
     * Setter for algorithmClass.
     */
    public function setAlgorithmClass(string $classname): void
    {
        $this->algorithmClass = $classname;
    }

    /**
     * Setter for comparatorClass.
     */
    public function setComparatorClass(string $classname): void
    {
        $this->comparatorClass = $classname;
    }

    /**
     * Setter for cacheClass.
     */
    public function setCacheClass(string $classname): void
    {
        $this->cacheClass = $classname;
    }

    public function targetStarted(BuildEvent $event)
    {
        // do nothing
    }

    public function targetFinished(BuildEvent $event)
    {
        if ($this->getDelayUpdate()) {
            $this->saveCache();
        }
    }

    public function taskStarted(BuildEvent $event)
    {
        // do nothing
    }

    public function taskFinished(BuildEvent $event)
    {
        if ($this->getDelayUpdate()) {
            $this->saveCache();
        }
    }

    public function messageLogged(BuildEvent $event)
    {
        // do nothing
    }

    /**
     * @param string $filename
     *
     * @return null|bool
     */
    public function isSelected(File $basedir, $filename, File $file)
    {
        $this->validate();

        try {
            $f = new File($basedir, $filename);

            // You can not compute a value for a directory
            if ($f->isDirectory()) {
                return $this->selectDirectories;
            }
        } catch (Throwable $t) {
            throw new BuildException($t);
        }

        // Get the values and do the comparison
        $cachedValue = (string) $this->cache->get($f->getAbsolutePath());
        $newValue = $this->algorithm->getValue($f);

        $rv = 0 !== $this->comparator->compare($cachedValue, $newValue);

        // Maybe update the cache
        if ($this->update && $rv) {
            $this->cache->put($f->getAbsolutePath(), $newValue);
            $this->setModified($this->getModified() + 1);
            if (!$this->getDelayUpdate()) {
                $this->saveCache();
            }
        }

        return $rv;
    }

    public function setAlgorithm(string $an): void
    {
        $this->algoName = $an;
    }

    public function setComparator($value): void
    {
        $this->compName = $value;
    }

    /**
     * Try to set a value on an object using reflection.
     * Helper method for easier access to IntrospectionHelper.setAttribute().
     *
     * @param object $obj   the object on which the attribute should be set
     * @param string $name  the attributename
     * @param string $value the new value
     */
    protected function tryToSetAParameter(object $obj, string $name, string $value): void
    {
        $prj = $this->getProject() ?? new Project();
        $iHelper = IntrospectionHelper::getHelper(get_class($obj));

        try {
            $iHelper->setAttribute($prj, $obj, $name, $value);
        } catch (BuildException $e) {
            // no-op
        }
    }

    /**
     * save the cache file.
     */
    protected function saveCache(): void
    {
        if ($this->getModified() > 0) {
            $this->cache->save();
            $this->setModified(0);
        }
    }
}

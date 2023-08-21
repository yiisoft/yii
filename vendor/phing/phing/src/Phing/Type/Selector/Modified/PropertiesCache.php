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

namespace Phing\Type\Selector\Modified;

use ArrayIterator;
use Exception;
use Iterator;
use Phing\Io\File;
use Phing\Util\Properties;

/**
 * Class PropertiesfileCache.
 */
class PropertiesCache implements Cache
{
    /** Where to store the properties? */
    private $cachefile;

    /**
     * Object for storing the key-value-pairs.
     *
     * @var Properties
     */
    private $cache;

    /** Is the cache already loaded? Prevents from multiple load operations. */
    private $cacheLoaded = false;

    /** Must the cache be saved? Prevents from multiple save operations. */
    private $cacheDirty = true;

    /**
     * Constructor.
     *
     * @param File $cachefile set the cachefile
     */
    public function __construct(File $cachefile = null)
    {
        $this->cache = new Properties();
        $this->cachefile = $cachefile;
    }

    /**
     * @return string information about this cache
     */
    public function __toString(): string
    {
        if (!$this->cacheLoaded) {
            $this->load();
        }

        return sprintf(
            '<%s:cachefile=%s;noOfEntries=%d>',
            __CLASS__,
            $this->cachefile,
            count($this->cache->keys())
        );
    }

    /**
     * Getter.
     *
     * @return null|File the cachefile
     */
    public function getCachefile(): ?File
    {
        return $this->cachefile;
    }

    /**
     * Setter.
     *
     * @param File $file new value
     */
    public function setCachefile(File $file): void
    {
        $this->cachefile = $file;
    }

    /**
     * This cache is valid if the cachefile is set.
     *
     * @return true if all is ok false otherwise
     */
    public function isValid(): bool
    {
        return null !== $this->cachefile;
    }

    /**
     * Saves modification of the cache.
     * Cache is only saved if there is one ore more entries.
     * Because entries can not be deleted by this API, this Cache
     * implementation checks the existence of entries before creating the file
     * for performance optimisation.
     */
    public function save(): void
    {
        if (!$this->cacheDirty) {
            return;
        }
        if (null !== $this->cachefile && count($this->cache->propertyNames()) > 0) {
            $this->cache->store($this->cachefile);
        }
        $this->cacheDirty = false;
    }

    /** Deletes the cache and its underlying file. */
    public function delete(): void
    {
        $this->cache = new Properties();
        $this->cachefile->delete();
        $this->cacheLoaded = true;
        $this->cacheDirty = false;
    }

    /**
     * Returns a value for a given key from the cache.
     *
     * @param string $key the key
     *
     * @return mixed the stored value
     */
    public function get($key)
    {
        if (!$this->cacheLoaded) {
            $this->load();
        }

        return $this->cache->getProperty((string) $key);
    }

    /**
     * Load the cache from underlying properties file.
     */
    public function load(): void
    {
        if (null !== $this->cachefile && $this->cachefile->isFile() && $this->cachefile->canRead()) {
            try {
                $this->cache->load($this->cachefile);
            } catch (Exception $e) {
                echo $e->getTraceAsString();
            }
        }
        // after loading the cache is up to date with the file
        $this->cacheLoaded = true;
        $this->cacheDirty = false;
    }

    /**
     * Saves a key-value-pair in the cache.
     *
     * @param string $key   the key
     * @param string $value the value
     */
    public function put($key, $value): void
    {
        $this->cache->put((string) $key, (string) $value);
        $this->cacheDirty = true;
    }

    /**
     * Returns an iterator over the keys in the cache.
     *
     * @return Iterator an iterator over the keys
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->cache->propertyNames());
    }
}

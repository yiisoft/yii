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

use Iterator;
use IteratorAggregate;

/**
 * Interface Cache.
 */
interface Cache extends IteratorAggregate
{
    /**
     * Checks its prerequisites.
     *
     * @return bool <i>true</i> if all is ok, otherwise <i>false</i>
     */
    public function isValid(): bool;

    /** Deletes the cache. If file based the file has to be deleted also. */
    public function delete(): void;

    /** Loads the cache, must handle not existing cache. */
    public function load(): void;

    /** Saves modification of the cache. */
    public function save(): void;

    /**
     * Returns a value for a given key from the cache.
     *
     * @param string $key the key
     *
     * @return string the stored value
     */
    public function get($key);

    /**
     * Saves a key-value-pair in the cache.
     *
     * @param string $key   the key
     * @param string $value the value
     */
    public function put($key, $value): void;

    /**
     * Returns an iterator over the keys in the cache.
     *
     * @return Iterator an iterator over the keys
     */
    public function getIterator(): Iterator;
}

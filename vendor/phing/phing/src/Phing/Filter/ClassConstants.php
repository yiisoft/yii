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

namespace Phing\Filter;

use Phing\Io\IOException;
use Phing\Io\Reader;
use ReflectionClass;

/**
 * Assembles the constants declared in a PHP class in
 * <code>key1=value1(PHP_EOL)key2=value2</code>
 * format.
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class ClassConstants extends BaseFilterReader implements ChainableReader
{
    private $test = '';

    /**
     * Returns the filtered stream.
     *
     * @param int $len
     *
     * @throws IOException if the underlying stream throws an IOException
     *                     during reading
     *
     * @return mixed the filtered stream, or -1 if the end of the resulting stream has been reached
     */
    public function read($len = null)
    {
        $buffer = $this->in->read();

        if (-1 === $buffer) {
            return -1;
        }

        $classes = get_declared_classes();
        eval($buffer);
        $newClasses = array_diff(get_declared_classes(), $classes);

        $sb = '';
        foreach ($newClasses as $name) {
            $clazz = new ReflectionClass($name);
            foreach ($clazz->getConstants() as $key => $value) {
                $sb .= $key . '=' . $value . PHP_EOL;
            }
        }

        return $sb;
    }

    /**
     * Creates a new ExpandProperties filter using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader A Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @return ExpandProperties A new filter based on this configuration, but filtering
     *                          the specified reader
     */
    public function chain(Reader $reader): Reader
    {
        return new self($reader);
    }
}

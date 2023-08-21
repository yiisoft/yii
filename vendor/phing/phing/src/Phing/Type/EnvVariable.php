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
 * Representation of a single env value.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class EnvVariable
{
    /**
     * env key and value pair; everything gets expanded to a string
     * during assignment.
     */
    private $key;
    private $value;

    /**
     * set the key.
     *
     * @param string $key string
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * set the value.
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * key accessor.
     *
     * @return string key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * value accessor.
     *
     * @return string value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * stringify path and assign to the value.
     * The value will contain all path elements separated by the appropriate
     * separator.
     */
    public function setPath(Path $path)
    {
        $this->value = (string) $path;
    }

    /**
     * get the absolute path of a file and assign it to the value.
     *
     * @param File $file file to use as the value
     */
    public function setFile(File $file)
    {
        $this->value = $file->getAbsolutePath();
    }

    /**
     * get the assignment string
     * This is not ready for insertion into a property file without following
     * the escaping rules of the properties class.
     *
     * @throws BuildException if key or value are unassigned
     *
     * @return string of the form key=value
     */
    public function getContent()
    {
        $this->validate();

        return trim($this->key) . '=' . trim($this->value);
    }

    /**
     * checks whether all required attributes have been specified.
     *
     * @throws BuildException if key or value are unassigned
     */
    public function validate()
    {
        if (null === $this->key || null === $this->value) {
            throw new BuildException('key and value must be specified for environment variables.');
        }
    }
}

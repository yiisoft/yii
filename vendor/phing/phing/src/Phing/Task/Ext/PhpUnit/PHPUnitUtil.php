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

namespace Phing\Task\Ext\PhpUnit;

use Exception;
use Phing\Phing;
use ReflectionClass;
use ReflectionException;

/**
 * Various utility functions
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.phpunit
 * @since   2.1.0
 */
class PHPUnitUtil
{
    protected static $definedClasses = [];

    /**
     * Returns the package of a class as defined in the docblock of the class using {@package}
     *
     * @param string the name of the class
     * @return string the name of the package
     * @throws ReflectionException
     */
    public static function getPackageName($classname)
    {
        $reflect = new ReflectionClass($classname);

        if (method_exists($reflect, 'getNamespaceName')) {
            $namespace = $reflect->getNamespaceName();

            if ($namespace != '') {
                return $namespace;
            }
        }

        if (preg_match('/@package[\s]+([\.\w]+)/', $reflect->getDocComment(), $matches)) {
            return $matches[1];
        }

        return "default";
    }

    /**
     * Returns the subpackage of a class as defined in the docblock of the class
     * using {@subpackage}
     *
     * @param string $classname the name of the class
     *
     * @return string|null the name of the subpackage
     * @throws ReflectionException
     * @author Benjamin Schultz <bschultz@proqrent.de>
     */
    public static function getSubpackageName($classname)
    {
        $reflect = new ReflectionClass($classname);

        if (preg_match('/@subpackage[\s]+([\.\w]+)/', $reflect->getDocComment(), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param $filename
     * @param null $classpath
     * @throws Exception
     * @internal param the $string filename
     * @internal param optional $Path classpath
     * @return array list of classes defined in the file
     */
    public static function getDefinedClasses($filename, $classpath = null)
    {
        $filename = realpath($filename);

        if (!file_exists($filename)) {
            throw new Exception("File '" . $filename . "' does not exist");
        }

        if (isset(self::$definedClasses[$filename])) {
            return self::$definedClasses[$filename];
        }

        Phing::importFile($filename, $classpath);

        $declaredClasses = get_declared_classes();

        foreach ($declaredClasses as $classname) {
            $reflect = new ReflectionClass($classname);

            self::$definedClasses[$reflect->getFilename()][] = $classname;

            if (is_array(self::$definedClasses[$reflect->getFilename()])) {
                self::$definedClasses[$reflect->getFilename()] = array_unique(
                    self::$definedClasses[$reflect->getFilename()]
                );
            }
        }

        return self::$definedClasses[$filename] ?? [];
    }
}

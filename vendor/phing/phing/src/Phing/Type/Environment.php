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

use ArrayObject;
use Phing\Exception\BuildException;

/**
 * Wrapper for environment variables.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class Environment
{
    /**
     * a vector of type EnvVariable.
     *
     * @var EnvVariable[]
     */
    protected $variables;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->variables = new ArrayObject();
    }

    /**
     * add a variable.
     * Validity checking is <i>not</i> performed at this point. Duplicates
     * are not caught either.
     *
     * @param EnvVariable $var new variable
     */
    public function addVariable(EnvVariable $var)
    {
        $this->variables->append($var);
    }

    /**
     * get the variable list as an array.
     *
     * @throws BuildException if any variable is misconfigured
     *
     * @return array of key=value assignment strings
     */
    public function getVariables()
    {
        if (0 === $this->variables->count()) {
            return null;
        }

        return array_map(
            function (EnvVariable $env) {
                return $env->getContent();
            },
            $this->variables->getArrayCopy()
        );
    }

    /**
     * Get the raw vector of variables. This is not a clone.
     *
     * @return ArrayObject a potentially empty (but never null) vector of elements of type
     *                     Variable
     */
    public function getVariablesObject(): ArrayObject
    {
        return $this->variables;
    }
}

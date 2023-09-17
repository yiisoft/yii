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

namespace Phing\Task\System;

use Phing\Exception\BuildException;
use Phing\Phing;
use Phing\Project;
use Phing\Task;
use Phing\Task\System\Element\LogLevelAware;
use Phing\Type\Parameter;
use Phing\Util\StringHelper;

/**
 * Executes PHP function or evaluates expression and sets return value to a property.
 *
 *    WARNING:
 *        This task can, of course, be abused with devastating effects.  E.g. do not
 *        modify internal Phing classes unless you know what you are doing.
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 *
 * @todo Add support for evaluating expressions
 */
class PhpEvalTask extends Task
{
    use LogLevelAware;

    protected $expression; // Expression to evaluate
    protected $function; // Function to execute
    protected $class; // Class containing function to execute
    protected $returnProperty; // name of property to set to return value
    protected $params = []; // parameters for function calls

    public function init()
    {
        $this->logLevel = Project::MSG_INFO;
    }

    /**
     * Main entry point.
     */
    public function main()
    {
        if (null === $this->function && null === $this->expression) {
            throw new BuildException(
                'You must specify a function to execute or PHP expression to evalute.',
                $this->getLocation()
            );
        }

        if (null !== $this->function && null !== $this->expression) {
            throw new BuildException('You can specify function or expression, but not both.', $this->getLocation());
        }

        if (null !== $this->expression && !empty($this->params)) {
            throw new BuildException(
                'You cannot use nested <param> tags when evaluationg a PHP expression.',
                $this->getLocation()
            );
        }

        if (null !== $this->function) {
            $this->callFunction();
        } elseif (null !== $this->expression) {
            $this->evalExpression();
        }
    }

    /**
     * Set function to execute.
     *
     * @param string $function
     */
    public function setFunction($function)
    {
        $this->function = $function;
    }

    /**
     * Set [static] class which contains function to execute.
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Sets property name to set with return value of function or expression.
     *
     * @param string $returnProperty
     */
    public function setReturnProperty($returnProperty)
    {
        $this->returnProperty = $returnProperty;
    }

    /**
     * Set PHP expression to evaluate.
     *
     * @param string $expression
     */
    public function addText($expression)
    {
        $this->expression = $expression;
    }

    /**
     * Set PHP expression to evaluate.
     *
     * @param string $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * Add a nested <param> tag.
     */
    public function addParam(Parameter $p)
    {
        $this->params[] = $p;
    }

    /**
     * Simplifies a Parameter object of arbitrary complexity into string or
     * array, retaining only the value of the parameter.
     */
    protected function simplifyParameter(Parameter $param)
    {
        if (empty($children = $param->getParams())) {
            return $param->getValue();
        }

        $simplified = [];
        foreach ($children as $child) {
            $simplified[] = $this->simplifyParameter($child);
        }

        return $simplified;
    }

    /**
     * Calls function and stores results in property.
     */
    protected function callFunction()
    {
        if (null !== $this->class) {
            // import the classname & unqualify it, if necessary
            $this->class = Phing::import($this->class);

            $user_func = [$this->class, $this->function];
            $h_func = $this->class . '::' . $this->function; // human-readable (for log)
        } else {
            $user_func = $this->function;
            $h_func = $user_func; // human-readable (for log)
        }

        // put parameters into simple array
        $params = [];
        foreach ($this->params as $p) {
            $params[] = $this->simplifyParameter($p);
        }

        $this->log('Calling PHP function: ' . $h_func . '()', $this->logLevel);
        foreach ($params as $p) {
            $this->log('  param: ' . print_r($p, true), Project::MSG_VERBOSE);
        }

        $return = call_user_func_array($user_func, $params);

        if (null !== $this->returnProperty) {
            $this->project->setProperty($this->returnProperty, $return);
        }
    }

    /**
     * Evaluates expression and sets property to resulting value.
     */
    protected function evalExpression()
    {
        $this->log('Evaluating PHP expression: ' . $this->expression, $this->logLevel);
        if (!StringHelper::endsWith(';', trim($this->expression))) {
            $this->expression .= ';';
        }

        if (null !== $this->returnProperty) {
            $retval = null;
            eval('$retval = ' . $this->expression);
            $this->project->setProperty($this->returnProperty, $retval);
        } else {
            eval($this->expression);
        }
    }
}

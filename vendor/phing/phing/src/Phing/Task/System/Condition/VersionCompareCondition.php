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

namespace Phing\Task\System\Condition;

use Phing\Exception\BuildException;

/**
 * Condition that compare versions.
 *
 * @author  Tomáš Fejfar (tomas.fejfar@gmail.com)
 */
class VersionCompareCondition implements Condition
{
    /**
     * Actual version.
     *
     * @var string
     */
    private $version;

    /**
     * Version to be compared to.
     *
     * @var string
     */
    private $desiredVersion;

    /**
     * Operator to use (default "greater or equal").
     *
     * @var string operator for possible values @see http://php.net/version%20compare
     */
    private $operator = '>=';

    private $debug = false;

    public function setVersion(string $version)
    {
        $this->version = $version;
    }

    public function setDesiredVersion(string $desiredVersion)
    {
        $this->desiredVersion = $desiredVersion;
    }

    /**
     * @throws BuildException
     */
    public function setOperator(string $operator)
    {
        $allowed = ['<', 'lt', '<=', 'le', '>', 'gt', '>=', 'ge', '==', '=', 'eq', '!=', '<>', 'ne'];
        if (!in_array($operator, $allowed)) { // allowed operators for php's version_comapare()
            throw new BuildException(
                sprintf(
                    'Operator "%s" is not supported. Supported operators: %s',
                    $operator,
                    implode(', ', $allowed)
                )
            );
        }
        $this->operator = $operator;
    }

    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * @throws BuildException
     */
    public function evaluate()
    {
        if (null === $this->version || null === $this->desiredVersion) {
            throw new BuildException('Missing one version parameter for version compare');
        }
        $isValid = version_compare($this->version, $this->desiredVersion, $this->operator);
        if ($this->debug) {
            echo sprintf(
                'Assertion that %s %s %s failed' . PHP_EOL,
                $this->version,
                $this->operator,
                $this->desiredVersion
            );
        }

        return $isValid;
    }
}

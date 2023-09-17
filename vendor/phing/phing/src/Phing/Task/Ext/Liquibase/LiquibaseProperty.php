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

namespace Phing\Task\Ext\Liquibase;

use Phing\Exception\BuildException;
use Phing\Project;
use Phing\Type\DataType;
use Phing\Util\StringHelper;

/**
 * @author Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
 * @since 2.4.10
 * @package phing.tasks.ext.liquibase
 */
class LiquibaseProperty extends DataType
{
    private $name;
    private $value;

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param Project $p
     * @return string
     * @throws BuildException
     */
    public function getCommandline(Project $p)
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getCommandline($p);
        }

        return sprintf("-D%s=%s", $this->name, escapeshellarg($this->value));
    }

    /**
     * @param Project $p
     * @return mixed
     * @throws BuildException
     */
    public function getRef(Project $p)
    {
        $dataTypeName = StringHelper::substring(__CLASS__, strrpos(__CLASS__, '\\') + 1);
        return $this->getCheckedRef(__CLASS__, $dataTypeName);
    }
}

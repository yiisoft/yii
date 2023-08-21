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

/**
 * Task to tag the current database state. In case you tag the database multiple
 * times without applying a new changelog before, the tags will overwrite each
 * other!
 *
 * @author  Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
 * @since   2.4.10
 * @package phing.tasks.ext.liquibase
 */
class LiquibaseTagTask extends AbstractLiquibaseTask
{
    protected $tag;

    /**
     * Sets the name of tag which is used to mark the database state for
     * possible future rollback.
     *
     * @param string the name to tag the database with
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @see AbstractTask::checkParams()
     */
    protected function checkParams()
    {
        parent::checkParams();

        if (null === $this->tag) {
            throw new BuildException('Please specify the tag!');
        }
    }

    /**
     * @see Task::main()
     */
    public function main()
    {
        $this->checkParams();
        $this->execute('tag', escapeshellarg($this->tag));
    }
}

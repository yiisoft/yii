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

namespace Phing\Task\System\Pdo;

use Phing\Io\File;
use Phing\Io\FileReader;
use Phing\Io\IOException;
use Phing\Io\StringReader;
use Phing\Project;

/**
 * "Inner" class that contains the definition of a new transaction element.
 * Transactions allow several files or blocks of statements
 * to be executed using the same JDBC connection and commit
 * operation in between.
 */
class PDOSQLExecTransaction
{
    private $tSrcFile;
    private $tSqlCommand = '';
    private $parent;

    public function __construct(PDOSQLExecTask $parent)
    {
        // Parent is required so that we can log things ...
        $this->parent = $parent;
    }

    public function setSrc(File $src)
    {
        $this->tSrcFile = $src;
    }

    /**
     * @param string $sql
     */
    public function addText($sql)
    {
        $this->tSqlCommand .= $sql;
    }

    /**
     * @throws IOException, PDOException
     */
    public function runTransaction()
    {
        if (!empty($this->tSqlCommand)) {
            $this->parent->log('Executing commands', Project::MSG_INFO);
            $this->parent->runStatements(new StringReader($this->tSqlCommand));
        }

        if (null !== $this->tSrcFile) {
            $this->parent->log(
                'Executing file: ' . $this->tSrcFile->getAbsolutePath(),
                Project::MSG_INFO
            );
            $reader = new FileReader($this->tSrcFile);
            $this->parent->runStatements($reader);
            $reader->close();
        }
    }
}

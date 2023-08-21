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

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\IOException;
use Phing\Io\Reader;
use Phing\Project;
use Phing\Task\System\Condition\Condition;
use Phing\Type\Element\FileListAware;
use Phing\Type\Element\FileSetAware;

/**
 * Executes a series of SQL statements on a database using PDO.
 *
 * <p>Statements can
 * either be read in from a text file using the <i>src</i> attribute or from
 * between the enclosing SQL tags.</p>
 *
 * <p>Multiple statements can be provided, separated by semicolons (or the
 * defined <i>delimiter</i>). Individual lines within the statements can be
 * commented using either --, // or REM at the start of the line.</p>
 *
 * <p>The <i>autocommit</i> attribute specifies whether auto-commit should be
 * turned on or off whilst executing the statements. If auto-commit is turned
 * on each statement will be executed and committed. If it is turned off the
 * statements will all be executed as one transaction.</p>
 *
 * <p>The <i>onerror</i> attribute specifies how to proceed when an error occurs
 * during the execution of one of the statements.
 * The possible values are: <b>continue</b> execution, only show the error;
 * <b>stop</b> execution and commit transaction;
 * and <b>abort</b> execution and transaction and fail task.</p>
 *
 * This task can also be used as a Condition.
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Jeff Martin <jeff@custommonkey.org> (Ant)
 * @author  Michael McCallum <gholam@xtra.co.nz> (Ant)
 * @author  Tim Stephenson <tim.stephenson@sybase.com> (Ant)
 */
class PDOSQLExecTask extends PDOTask implements Condition
{
    use FileListAware;
    use FileSetAware;

    public const DELIM_ROW = 'row';
    public const DELIM_NORMAL = 'normal';
    public const DELIM_NONE = 'none';
    /**
     * Count of how many statements were executed successfully.
     *
     * @var int
     */
    private $goodSql = 0;

    /**
     * Count of total number of SQL statements.
     *
     * @var int
     */
    private $totalSql = 0;

    /**
     * Database connection.
     *
     * @var PDO
     */
    private $conn;

    /**
     * Formatter elements.
     *
     * @var PDOSQLExecFormatterElement[]
     */
    private $formatters = [];

    /**
     * SQL statement.
     *
     * @var PDOStatement
     */
    private $statement;

    /**
     * SQL input file.
     *
     * @var File
     */
    private $srcFile;

    /**
     * SQL input command.
     *
     * @var string
     */
    private $sqlCommand = '';

    /**
     * SQL transactions to perform.
     */
    private $transactions = [];

    /**
     * SQL Statement delimiter (for parsing files).
     *
     * @var string
     */
    private $delimiter = ';';

    /**
     * The delimiter type indicating whether the delimiter will
     * only be recognized on a line by itself.
     */
    private $delimiterType = self::DELIM_NONE;

    /**
     * Action to perform if an error is found.
     */
    private $onError = 'abort';

    /**
     * Encoding to use when reading SQL statements from a file.
     */
    private $encoding;

    /**
     * Fetch mode for PDO select queries.
     *
     * @var int
     */
    private $fetchMode;

    /**
     * The name of the property to set in the event of an error
     */
    private $errorProperty;

    /**
     * The name of the property that receives the number of rows
     * returned
     */
    private $statementCountProperty;

    /**
     * @var bool
     */
    private $keepformat = false;

    /**
     * @var bool
     */
    private $expandProperties = true;

    /**
     * Set the name of the SQL file to be run.
     * Required unless statements are enclosed in the build file.
     */
    public function setSrc(File $srcFile): void
    {
        $this->srcFile = $srcFile;
    }

    /**
     * Set an inline SQL command to execute.
     * NB: Properties are not expanded in this text.
     *
     * @param string $sql
     */
    public function addText($sql): void
    {
        $this->sqlCommand .= $sql;
    }

    /**
     * Creates a new PDOSQLExecFormatterElement for <formatter> element.
     *
     * @return PDOSQLExecFormatterElement
     */
    public function createFormatter(): PDOSQLExecFormatterElement
    {
        $fe = new PDOSQLExecFormatterElement($this);
        $this->formatters[] = $fe;

        return $fe;
    }

    /**
     * Add a SQL transaction to execute.
     */
    public function createTransaction()
    {
        $t = new PDOSQLExecTransaction($this);
        $this->transactions[] = $t;

        return $t;
    }

    /**
     * Set the statement delimiter.
     *
     * <p>For example, set this to "go" and delimitertype to "ROW" for
     * Sybase ASE or MS SQL Server.</p>
     */
    public function setDelimiter(string $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    /**
     * Get the statement delimiter.
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * Set the Delimiter type for this sql task. The delimiter type takes two
     * values - normal and row. Normal means that any occurrence of the delimiter
     * terminate the SQL command whereas with row, only a line containing just
     * the delimiter is recognized as the end of the command.
     */
    public function setDelimiterType(string $delimiterType): void
    {
        $this->delimiterType = $delimiterType;
    }

    /**
     * Action to perform when statement fails: continue, stop, or abort
     * optional; default &quot;abort&quot;.
     *
     * @param string $action continue|stop|abort
     */
    public function setOnerror($action): void
    {
        $this->onError = $action;
    }

    /**
     * Sets the fetch mode to use for the PDO resultset.
     *
     * @param mixed $mode the PDO fetchmode int or constant name
     *
     * @throws BuildException
     */
    public function setFetchmode($mode): void
    {
        if (is_numeric($mode)) {
            $this->fetchMode = (int) $mode;
        } else {
            if (defined($mode)) {
                $this->fetchMode = constant($mode);
            } else {
                throw new BuildException('Invalid PDO fetch mode specified: ' . $mode, $this->getLocation());
            }
        }
    }

    public function getGoodSQL()
    {
        return $this->goodSql;
    }

    /**
     * Property to set to "true" if a statement throws an error.
     *
     * @param string $errorProperty the name of the property to set in the
     * event of an error.
     */
    public function setErrorProperty(string $errorProperty): void
    {
        $this->errorProperty = $errorProperty;
    }

    /**
     * Sets a given property to the number of statements processed.
     * @param string $statementCountProperty String
     */
    public function setStatementCountProperty(string $statementCountProperty): void
    {
        $this->statementCountProperty = $statementCountProperty;
    }

    public function setKeepformat(bool $keepformat): void
    {
        $this->keepformat = $keepformat;
    }

    public function setExpandProperties(bool $expandProps): void
    {
        $this->expandProperties = $expandProps;
    }

    /**
     * Load the sql file and then execute it.
     *
     * {@inheritdoc}
     *
     * @throws BuildException
     */
    public function main()
    {
        // Set a default fetchmode if none was specified
        // (We're doing that here to prevent errors loading the class is PDO is not available.)
        if (null === $this->fetchMode) {
            $this->fetchMode = PDO::FETCH_ASSOC;
        }

        // Initialize the formatters here.  This ensures that any parameters passed to the formatter
        // element get passed along to the actual formatter object
        foreach ($this->formatters as $fe) {
            $fe->prepare($this->getLocation());
        }

        $savedTransaction = [];
        for ($i = 0, $size = count($this->transactions); $i < $size; ++$i) {
            $savedTransaction[] = clone $this->transactions[$i];
        }

        $savedSqlCommand = $this->sqlCommand;

        $this->sqlCommand = trim($this->sqlCommand);

        try {
            if (
                null === $this->srcFile
                && '' === $this->sqlCommand
                && empty($this->filesets)
                && empty($this->filelists)
                && 0 === count($this->transactions)
            ) {
                throw new BuildException(
                    'Source file or fileset/filelist, '
                    . 'transactions or sql statement '
                    . 'must be set!',
                    $this->getLocation()
                );
            }

            if (null !== $this->srcFile && !$this->srcFile->exists()) {
                throw new BuildException('Source file does not exist!', $this->getLocation());
            }

            // deal with the filesets
            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($this->project);
                $srcDir = $fs->getDir($this->project);
                $srcFiles = $ds->getIncludedFiles();
                // Make a transaction for each file
                foreach ($srcFiles as $srcFile) {
                    $t = $this->createTransaction();
                    $t->setSrc(new File($srcDir, $srcFile));
                }
            }

            // process filelists
            foreach ($this->filelists as $fl) {
                $srcDir = $fl->getDir($this->project);
                $srcFiles = $fl->getFiles($this->project);
                // Make a transaction for each file
                foreach ($srcFiles as $srcFile) {
                    $t = $this->createTransaction();
                    $t->setSrc(new File($srcDir, $srcFile));
                }
            }

            // Make a transaction group for the outer command
            $t = $this->createTransaction();
            if ($this->srcFile) {
                $t->setSrc($this->srcFile);
            }
            $t->addText($this->sqlCommand);

            $this->conn = $this->getConnection();
            if ($this->conn === null) {
                return;
            }

            try {
                $this->statement = null;

                // Initialize the formatters.
                $this->initFormatters();

                try {
                    // Process all transactions
                    for ($i = 0, $size = count($this->transactions); $i < $size; ++$i) {
                        if (!$this->isAutocommit() || $this->conn->inTransaction()) {
                            $this->log('Beginning transaction', Project::MSG_VERBOSE);
                            $this->conn->beginTransaction();
                        }
                        $this->transactions[$i]->runTransaction();
                        if (!$this->isAutocommit() || $this->conn->inTransaction()) {
                            $this->log('Committing transaction', Project::MSG_VERBOSE);
                            $this->conn->commit();
                        }
                    }
                } catch (Exception $e) {
                    $this->closeConnection();

                    throw new BuildException($e);
                }
            } catch (IOException | PDOException $e) {
                $this->closeQuietly();
                $this->setErrorProp();
                if ('abort' === $this->onError) {
                    throw new BuildException($e->getMessage(), $this->getLocation());
                }
            }

            // Close the formatters.
            $this->closeFormatters();

            $this->log(
                $this->goodSql . ' of ' . $this->totalSql .
                ' SQL statements executed successfully'
            );
            $this->setStatementCountProp($this->goodSql);
        } catch (Exception $e) {
            throw new BuildException($e);
        } finally {
            $this->transactions = $savedTransaction;
            $this->sqlCommand = $savedSqlCommand;
            $this->closeConnection();
        }
    }

    /**
     * read in lines and execute them.
     *
     * @throws BuildException
     */
    public function runStatements(Reader $reader): void
    {
        if (self::DELIM_NONE === $this->delimiterType) {
            $splitter = new DummyPDOQuerySplitter($this, $reader);
        } elseif (self::DELIM_NORMAL === $this->delimiterType && 0 === strpos((string) $this->getUrl(), 'pgsql:')) {
            $splitter = new PgsqlPDOQuerySplitter($this, $reader);
        } else {
            $splitter = new DefaultPDOQuerySplitter($this, $reader, $this->delimiterType);
        }

        $splitter->setExpandProperties($this->expandProperties);
        $splitter->setKeepformat($this->keepformat);

        try {
            while (null !== ($query = $splitter->nextQuery())) {
                $this->log('SQL: ' . $query, Project::MSG_VERBOSE);
                $this->execSQL($query);
            }
        } catch (PDOException $e) {
            throw new BuildException($e);
        }
    }

    /**
     * PDOSQLExecTask as condition.
     *
     * Returns false when the database connection fails, and true otherwise.
     * This method only uses three properties: url (required), userId and
     * password.
     *
     * The database connection is not stored in a variable, this allow to
     * immediately close the connections since there's no reference to it.
     *
     * @author Jawira Portugal <dev@tugal.be>
     */
    public function evaluate(): bool
    {
        if (empty($this->getUrl())) {
            throw new BuildException('url is required');
        }

        $this->log('Trying to reach ' . $this->getUrl(), Project::MSG_DEBUG);

        try {
            new PDO($this->getUrl(), $this->getUserId(), $this->getPassword());
        } catch (PDOException $ex) {
            $this->log($ex->getMessage(), Project::MSG_VERBOSE);

            return false;
        }

        $this->log('Successful connection to ' . $this->getUrl(), Project::MSG_DEBUG);

        return true;
    }

    /**
     * Whether the passed-in SQL statement is a SELECT statement.
     * This does a pretty simple match, checking to see if statement starts with
     * 'select' (but not 'select into').
     *
     * @param string $sql
     *
     * @return bool whether specified SQL looks like a SELECT query
     */
    protected function isSelectSql($sql): bool
    {
        $sql = trim($sql);

        return 0 === stripos($sql, 'select') && 0 !== stripos($sql, 'select into ');
    }

    /**
     * Exec the sql statement.
     *
     * @param string $sql
     *
     * @throws BuildException
     */
    protected function execSQL($sql): void
    {
        // Check and ignore empty statements
        if (empty(trim($sql))) {
            return;
        }

        try {
            ++$this->totalSql;

            $this->statement = $this->conn->query($sql);

            // only call processResults() for statements that return actual data (such as 'select')
            if ($this->statement->columnCount() > 0) {
                $this->processResults();
            }

            $this->statement->closeCursor();
            $this->statement = null;

            ++$this->goodSql;
        } catch (PDOException $e) {
            $this->log('Failed to execute: ' . $sql, Project::MSG_ERR);
            $this->setErrorProp();
            if ('abort' !== $this->onError) {
                $this->log((string) $e, Project::MSG_ERR);
            }
            if ('continue' !== $this->onError) {
                throw new BuildException('Failed to execute SQL', $e);
            }
            $this->log($e->getMessage(), Project::MSG_ERR);
        }
    }

    /**
     * Returns configured PDOResultFormatter objects
     * (which were created from PDOSQLExecFormatterElement objects).
     *
     * @return PDOResultFormatter[]
     */
    protected function getConfiguredFormatters(): array
    {
        $formatters = [];
        foreach ($this->formatters as $fe) {
            $formatter = $fe->getFormatter();
            if ($formatter instanceof PlainPDOResultFormatter) {
                $formatter->setStatementCounter($this->goodSql);
            }
            $formatters[] = $formatter;
        }

        return $formatters;
    }

    /**
     * Initialize the formatters.
     */
    protected function initFormatters(): void
    {
        $formatters = $this->getConfiguredFormatters();
        foreach ($formatters as $formatter) {
            $formatter->initialize();
        }
    }

    /**
     * Run cleanup and close formatters.
     */
    protected function closeFormatters(): void
    {
        $formatters = $this->getConfiguredFormatters();
        foreach ($formatters as $formatter) {
            $formatter->close();
        }
    }

    /**
     * Passes results from query to any formatters.
     *
     * @throws PDOException
     */
    protected function processResults(): void
    {
        $this->log('Processing new result set.', Project::MSG_VERBOSE);

        $formatters = $this->getConfiguredFormatters();

        try {
            while ($row = $this->statement->fetch($this->fetchMode)) {
                foreach ($formatters as $formatter) {
                    $formatter->processRow($row);
                }
            }
        } catch (Exception $x) {
            $this->log('Error processing results: ' . $x->getMessage(), Project::MSG_ERR);
            foreach ($formatters as $formatter) {
                $formatter->close();
            }

            throw new BuildException($x);
        }
    }

    /**
     * Closes current connection.
     */
    protected function closeConnection(): void
    {
        if ($this->conn) {
            unset($this->conn);
            $this->conn = null;
        }
    }

    final protected function setErrorProp(): void
    {
        $this->setProperty($this->errorProperty, 'true');
    }

    final protected function setStatementCountProp(int $statementCount): void
    {
        $this->setProperty($this->statementCountProperty, (string) $statementCount);
    }

    /**
     * @param string|null $name
     * @param string $value
     */
    private function setProperty(?string $name, string $value): void
    {
        if ($name !== null) {
            $this->getProject()->setNewProperty($name, $value);
        }
    }

    /**
     * Closes an unused connection after an error and doesn't rethrow
     * a possible PDOException
     */
    private function closeQuietly(): void
    {
        if (null !== $this->conn && 'abort' === $this->onError && !$this->isAutocommit()) {
            try {
                $this->conn->rollback();
            } catch (PDOException $ex) {
            }
        }
        $this->closeConnection();
    }
}

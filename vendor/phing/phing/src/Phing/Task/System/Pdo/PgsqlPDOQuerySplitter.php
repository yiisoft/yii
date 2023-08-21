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

/**
 * Splits PostgreSQL's dialect of SQL into separate queries.
 *
 * Unlike DefaultPDOQuerySplitter this uses a lexer instead of regular
 * expressions. This allows handling complex constructs like C-style comments
 * (including nested ones) and dollar-quoted strings.
 *
 * @author  Alexey Borzov <avb@php.net>
 *
 * @see    http://www.phing.info/trac/ticket/499
 * @see    http://www.postgresql.org/docs/current/interactive/sql-syntax-lexical.html#SQL-SYNTAX-DOLLAR-QUOTING
 */
class PgsqlPDOQuerySplitter extends PDOQuerySplitter
{
    /*#@+
     * Lexer states
     */
    public const STATE_NORMAL = 0;
    public const STATE_SINGLE_QUOTED = 1;
    public const STATE_DOUBLE_QUOTED = 2;
    public const STATE_DOLLAR_QUOTED = 3;
    public const STATE_COMMENT_LINEEND = 4;
    public const STATE_COMMENT_MULTILINE = 5;
    public const STATE_BACKSLASH = 6;
    // #@-

    /**
     * Nesting depth of current multiline comment.
     *
     * @var int
     */
    protected $commentDepth = 0;

    /**
     * Current dollar-quoting "tag".
     *
     * @var string
     */
    protected $quotingTag = '';

    /**
     * Current lexer state, one of STATE_* constants.
     *
     * @var int
     */
    protected $state = self::STATE_NORMAL;

    /**
     * Whether a backslash was just encountered in quoted string.
     *
     * @var bool
     */
    protected $escape = false;

    /**
     * Current source line being examined.
     *
     * @var string
     */
    protected $line = '';

    /**
     * Position in current source line.
     *
     * @var int
     */
    protected $inputIndex;

    /**
     * Gets next symbol from the input, false if at end.
     *
     * @return bool|string
     */
    public function getc()
    {
        if (!strlen($this->line) || $this->inputIndex >= strlen($this->line)) {
            if (null === ($line = $this->sqlReader->readLine())) {
                return false;
            }
            $project = $this->parent->getOwningTarget()->getProject();
            $this->line = $project->replaceProperties($line) . "\n";
            $this->inputIndex = 0;
        }

        return $this->line[$this->inputIndex++];
    }

    /**
     * Bactracks one symbol on the input.
     *
     * NB: we don't need ungetc() at the start of the line, so this case is
     * not handled.
     */
    public function ungetc(): void
    {
        --$this->inputIndex;
    }

    /**
     * @return null|string
     */
    public function nextQuery(): ?string
    {
        $sql = '';
        $delimiter = $this->parent->getDelimiter();
        $openParens = 0;

        while (false !== ($ch = $this->getc())) {
            switch ($this->state) {
                case self::STATE_NORMAL:
                    switch ($ch) {
                        case '-':
                            if ('-' === $this->getc()) {
                                $this->state = self::STATE_COMMENT_LINEEND;
                            } else {
                                $this->ungetc();
                            }

                            break;

                        case '"':
                            $this->state = self::STATE_DOUBLE_QUOTED;

                            break;

                        case "'":
                            $this->state = self::STATE_SINGLE_QUOTED;

                            break;

                        case '/':
                            if ('*' === $this->getc()) {
                                $this->state = self::STATE_COMMENT_MULTILINE;
                                $this->commentDepth = 1;
                            } else {
                                $this->ungetc();
                            }

                            break;

                        case '$':
                            if (false !== ($tag = $this->checkDollarQuote())) {
                                $this->state = self::STATE_DOLLAR_QUOTED;
                                $this->quotingTag = $tag;
                                $sql .= '$' . $tag . '$';

                                continue 3;
                            }

                            break;

                        case '(':
                            $openParens++;

                            break;

                        case ')':
                            $openParens--;

                            break;
                        // technically we can use e.g. psql's \g command as delimiter
                        case $delimiter[0]:
                            // special case to allow "create rule" statements
                            // http://www.postgresql.org/docs/current/interactive/sql-createrule.html
                            if (';' === $delimiter && 0 < $openParens) {
                                break;
                            }
                            $hasQuery = true;
                            for ($i = 1, $delimiterLength = strlen($delimiter); $i < $delimiterLength; ++$i) {
                                if ($delimiter[$i] != $this->getc()) {
                                    $hasQuery = false;
                                }
                            }
                            if ($hasQuery) {
                                return $sql;
                            }

                            for ($j = 1; $j < $i; ++$j) {
                                $this->ungetc();
                            }
                    }

                    break;

                case self::STATE_COMMENT_LINEEND:
                    if ("\n" === $ch) {
                        $this->state = self::STATE_NORMAL;
                    }

                    break;

                case self::STATE_COMMENT_MULTILINE:
                    switch ($ch) {
                        case '/':
                            if ('*' !== $this->getc()) {
                                $this->ungetc();
                            } else {
                                ++$this->commentDepth;
                            }

                            break;

                        case '*':
                            if ('/' !== $this->getc()) {
                                $this->ungetc();
                            } else {
                                --$this->commentDepth;
                                if (0 == $this->commentDepth) {
                                    $this->state = self::STATE_NORMAL;

                                    continue 3;
                                }
                            }
                    }

                // no break
                case self::STATE_SINGLE_QUOTED:
                case self::STATE_DOUBLE_QUOTED:
                    if ($this->escape) {
                        $this->escape = false;

                        break;
                    }
                    $quote = self::STATE_SINGLE_QUOTED == $this->state ? "'" : '"';

                    switch ($ch) {
                        case '\\':
                            $this->escape = true;

                            break;

                        case $quote:
                            if ($quote == $this->getc()) {
                                $sql .= $quote;
                            } else {
                                $this->ungetc();
                                $this->state = self::STATE_NORMAL;
                            }
                    }

                // no break
                case self::STATE_DOLLAR_QUOTED:
                    if ('$' === $ch && false !== ($tag = $this->checkDollarQuote())) {
                        if ($tag == $this->quotingTag) {
                            $this->state = self::STATE_NORMAL;
                        }
                        $sql .= '$' . $tag . '$';

                        continue 2;
                    }
            }

            if (self::STATE_COMMENT_LINEEND != $this->state && self::STATE_COMMENT_MULTILINE != $this->state) {
                $sql .= $ch;
            }
        }
        if ('' !== $sql) {
            return $sql;
        }

        return null;
    }

    /**
     * Checks whether symbols after $ are a valid dollar-quoting tag.
     *
     * @return bool|string Dollar-quoting "tag" if it is present, false otherwise
     */
    protected function checkDollarQuote()
    {
        $ch = $this->getc();
        if ('$' === $ch) {
            // empty tag
            return '';
        }

        if (!ctype_alpha($ch) && '_' !== $ch) {
            // not a delimiter
            $this->ungetc();

            return false;
        }

        $tag = $ch;
        while (false !== ($ch = $this->getc())) {
            if ('$' === $ch) {
                return $tag;
            }

            if (ctype_alnum($ch) || '_' === $ch) {
                $tag .= $ch;
            } else {
                for ($i = 0, $tagLength = strlen($tag); $i < $tagLength; ++$i) {
                    $this->ungetc();
                }

                return false;
            }
        }

        return $tag;
    }
}

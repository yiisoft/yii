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

use Countable;
use Phing\Exception\BuildException;
use Phing\Io\FileUtils;
use Phing\Task\System\Condition\OsCondition;

/**
 * Commandline objects help handling command lines specifying processes to
 * execute.
 *
 * The class can be used to define a command line as nested elements or as a
 * helper to define a command line by an application.
 * <p>
 * <code>
 * &lt;someelement&gt;<br>
 * &nbsp;&nbsp;&lt;acommandline executable="/executable/to/run"&gt;<br>
 * &nbsp;&nbsp;&nbsp;&nbsp;&lt;argument value="argument 1" /&gt;<br>
 * &nbsp;&nbsp;&nbsp;&nbsp;&lt;argument line="argument_1 argument_2 argument_3" /&gt;<br>
 * &nbsp;&nbsp;&nbsp;&nbsp;&lt;argument value="argument 4" /&gt;<br>
 * &nbsp;&nbsp;&lt;/acommandline&gt;<br>
 * &lt;/someelement&gt;<br>
 * </code>
 * The element <code>someelement</code> must provide a method
 * <code>createAcommandline</code> which returns an instance of this class.
 *
 * @author  thomas.haas@softwired-inc.com
 * @author  <a href="mailto:stefan.bodewig@epost.de">Stefan Bodewig</a>
 */
class Commandline implements Countable
{
    public const DISCLAIMER = "The ' characters around the executable and arguments are not part of the command.";
    /**
     * @var CommandlineArgument[]
     */
    public $arguments = []; // public so "inner" class can access

    /**
     * Full path (if not on %PATH% env var) to executable program.
     *
     * @var string
     */
    public $executable; // public so "inner" class can access
    private $escape = false;

    /**
     * @param string $to_process
     *
     * @throws BuildException
     */
    public function __construct($to_process = null)
    {
        if (null !== $to_process) {
            $tmp = static::translateCommandline($to_process);
            if ($tmp) {
                $this->setExecutable(array_shift($tmp)); // removes first el
                foreach ($tmp as $arg) { // iterate through remaining elements
                    $this->createArgument()->setValue($arg);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            $cmd = $this->toString($this->getCommandline());
        } catch (BuildException $be) {
            $cmd = '';
        }

        return $cmd;
    }

    /**
     * @throws BuildException
     */
    public function __clone()
    {
        $c = new self();
        $c->addArguments($this->getArguments());
    }

    /**
     * Creates an argument object and adds it to our list of args.
     *
     * <p>Each commandline object has at most one instance of the
     * argument class.</p>
     *
     * @param bool $insertAtStart if true, the argument is inserted at the
     *                            beginning of the list of args, otherwise
     *                            it is appended
     *
     * @return CommandlineArgument
     */
    public function createArgument($insertAtStart = false)
    {
        $argument = new CommandlineArgument($this);
        if ($insertAtStart) {
            array_unshift($this->arguments, $argument);
        } else {
            $this->arguments[] = $argument;
        }

        return $argument;
    }

    /**
     * Sets the executable to run.
     *
     * @param string $executable
     * @param bool   $translateFileSeparator
     */
    public function setExecutable($executable, $translateFileSeparator = true): void
    {
        if (null === $executable || '' === $executable) {
            return;
        }
        $this->executable = $translateFileSeparator
            ? str_replace(['/', '\\'], FileUtils::getSeparator(), $executable)
            : $executable;
    }

    /**
     * @return string
     */
    public function getExecutable()
    {
        return $this->executable;
    }

    public function addArguments(array $arguments)
    {
        foreach ($arguments as $arg) {
            $this->createArgument()->setValue($arg);
        }
    }

    /**
     * Returns the executable and all defined arguments.
     */
    public function getCommandline(): array
    {
        $args = $this->getArguments();
        if (null !== $this->executable && '' !== $this->executable) {
            array_unshift($args, $this->executable);
        }

        return $args;
    }

    /**
     * Returns all arguments defined by <code>addLine</code>,
     * <code>addValue</code> or the argument object.
     */
    public function getArguments(): array
    {
        $result = [];
        foreach ($this->arguments as $arg) {
            $parts = $arg->getParts();
            if (null !== $parts) {
                foreach ($parts as $part) {
                    $result[] = $part;
                }
            }
        }

        return $result;
    }

    public function setEscape($flag)
    {
        $this->escape = $flag;
    }

    /**
     * Put quotes around the given String if necessary.
     *
     * <p>If the argument doesn't include spaces or quotes, return it
     * as is. If it contains double quotes, use single quotes - else
     * surround the argument by double quotes.</p>
     *
     * @param string $argument
     * @param bool   $escape
     *
     * @throws BuildException if the argument contains both, single
     *                        and double quotes
     *
     * @return string
     */
    public static function quoteArgument($argument, $escape = false)
    {
        if ($escape) {
            return escapeshellarg($argument);
        }

        if (false !== strpos($argument, '"')) {
            if (false !== strpos($argument, "'")) {
                throw new BuildException("Can't handle single and double quotes in same argument");
            }

            return '\'' . $argument . '\'';
        }

        if (
            false !== strpos($argument, "'")
            || false !== strpos($argument, ' ')
            // WIN9x uses a bat file for executing commands
            || (OsCondition::isFamily('win32')
                && false !== strpos($argument, ';'))
        ) {
            return '"' . $argument . '"';
        }

        return $argument;
    }

    /**
     * Crack a command line.
     *
     * @param string $toProcess the command line to process
     *
     * @throws BuildException
     *
     * @return string[] the command line broken into strings.
     *                  An empty or null toProcess parameter results in a zero sized array.
     */
    public static function translateCommandline(string $toProcess = null): array
    {
        if (null === $toProcess || '' === $toProcess) {
            return [];
        }

        // parse with a simple finite state machine

        $normal = 0;
        $inQuote = 1;
        $inDoubleQuote = 2;

        $state = $normal;
        $args = [];
        $current = '';
        $lastTokenHasBeenQuoted = false;

        $tokens = preg_split('/(["\' ])/', $toProcess, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        while (($nextTok = array_shift($tokens)) !== null) {
            switch ($state) {
                case $inQuote:
                    if ("'" === $nextTok) {
                        $lastTokenHasBeenQuoted = true;
                        $state = $normal;
                    } else {
                        $current .= $nextTok;
                    }

                    break;

                case $inDoubleQuote:
                    if ('"' === $nextTok) {
                        $lastTokenHasBeenQuoted = true;
                        $state = $normal;
                    } else {
                        $current .= $nextTok;
                    }

                    break;

                default:
                    if ("'" === $nextTok) {
                        $state = $inQuote;
                    } elseif ('"' === $nextTok) {
                        $state = $inDoubleQuote;
                    } elseif (' ' === $nextTok) {
                        if ($lastTokenHasBeenQuoted || '' !== $current) {
                            $args[] = $current;
                            $current = '';
                        }
                    } else {
                        $current .= $nextTok;
                    }
                    $lastTokenHasBeenQuoted = false;

                    break;
            }
        }

        if ($lastTokenHasBeenQuoted || '' !== $current) {
            $args[] = $current;
        }

        if ($state === $inQuote || $state === $inDoubleQuote) {
            throw new BuildException('unbalanced quotes in ' . $toProcess);
        }

        return $args;
    }

    /**
     * @return int number of components in current commandline
     */
    public function count(): int
    {
        return count($this->getCommandline());
    }

    /**
     * Return a marker.
     *
     * <p>This marker can be used to locate a position on the
     * commandline - to insert something for example - when all
     * parameters have been set.</p>
     *
     * @return CommandlineMarker
     */
    public function createMarker()
    {
        return new CommandlineMarker($this, count($this->arguments));
    }

    /**
     * Returns a String that describes the command and arguments
     * suitable for verbose output before a call to
     * <code>Runtime.exec(String[])</code>.
     *
     * <p>This method assumes that the first entry in the array is the
     * executable to run.</p>
     *
     * @param array|Commandline $args CommandlineArgument[] to use
     *
     * @return string
     */
    public function describeCommand($args = null)
    {
        if (null === $args) {
            $args = $this->getCommandline();
        } elseif ($args instanceof self) {
            $args = $args->getCommandline();
        }

        if (!$args) {
            return '';
        }

        $buf = "Executing '";
        $buf .= $args[0];
        $buf .= "'";
        if (count($args) > 0) {
            $buf .= ' with ';
            $buf .= $this->describeArguments($args, 1);
        } else {
            $buf .= self::DISCLAIMER;
        }

        return $buf;
    }

    /**
     * Returns a String that describes the arguments suitable for
     * verbose output before a call to
     * <code>Runtime.exec(String[])</code>.
     *
     * @param array $args   arguments to use (default is to use current class args)
     * @param int   $offset ignore entries before this index
     *
     * @return string
     */
    public function describeArguments(array $args = null, $offset = 0)
    {
        if (null === $args) {
            $args = $this->getArguments();
        }

        if (null === $args || count($args) <= $offset) {
            return '';
        }

        $buf = 'argument';
        if (count($args) > $offset) {
            $buf .= 's';
        }
        $buf .= ':' . PHP_EOL;
        for ($i = $offset, $alen = count($args); $i < $alen; ++$i) {
            $buf .= "'" . $args[$i] . "'" . PHP_EOL;
        }
        $buf .= self::DISCLAIMER;

        return $buf;
    }

    /**
     * Quotes the parts of the given array in way that makes them
     * usable as command line arguments.
     *
     * @param array $lines
     *
     * @throws BuildException
     */
    private function toString($lines = null): string
    {
        // empty path return empty string
        if (null === $lines || 0 === count($lines)) {
            return '';
        }

        return implode(
            ' ',
            array_map(
                function ($arg) {
                    return self::quoteArgument($arg, $this->escape);
                },
                $lines
            )
        );
    }
}

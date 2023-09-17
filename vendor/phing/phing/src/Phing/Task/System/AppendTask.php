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

use Exception;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileReader;
use Phing\Io\FileUtils;
use Phing\Io\FileWriter;
use Phing\Io\IOException;
use Phing\Io\LogWriter;
use Phing\Io\Reader;
use Phing\Io\StringReader;
use Phing\Io\Writer;
use Phing\Project;
use Phing\Task;
use Phing\Task\System\Append\TextElement;
use Phing\Type\Element\FileListAware;
use Phing\Type\Element\FileSetAware;
use Phing\Type\Element\FilterChainAware;
use Phing\Type\FileSet;
use Phing\Type\Path;
use Phing\Util\Register;

/**
 *  Appends text, contents of a file or set of files defined by a filelist to a destination file.
 *
 * <code>
 * <append text="And another thing\n" destfile="badthings.log"/>
 * </code>
 * OR
 * <code>
 * <append file="header.html" destfile="fullpage.html"/>
 * <append file="body.html" destfile="fullpage.html"/>
 * <append file="footer.html" destfile="fullpage.html"/>
 * </code>
 * OR
 * <code>
 * <append destfile="${process.outputfile}">
 *    <filterchain>
 *        <xsltfilter style="${process.stylesheet}">
 *            <param name="mode" expression="${process.xslt.mode}"/>
 *            <param name="file_name" expression="%{task.append.current_file.basename}"/> <!-- Example of using a RegisterSlot variable -->
 *        </xsltfilter>
 *    </filterchain>
 *     <filelist dir="book/" listfile="book/PhingGuide.book"/>
 * </append>
 * </code>
 */
class AppendTask extends Task
{
    use FileListAware;
    use FileSetAware;
    use FilterChainAware;

    /**
     * Append stuff to this file.
     */
    private $to;

    /**
     * Explicit file to append.
     */
    private $file;

    /**
     * Text to append. (cannot be used in conjunction w/ files or filesets).
     */
    private $text;

    private $filtering = true;

    /**
     * @var TextElement
     */
    private $header;

    /**
     * @var TextElement
     */
    private $footer;

    private $append = true;

    private $fixLastLine = false;

    private $overwrite = true;

    private $eolString;

    private $skipSanitize = false;

    public function setFiltering(bool $filtering): void
    {
        $this->filtering = $filtering;
    }

    /**
     * @param bool $overwrite
     */
    public function setOverwrite($overwrite): void
    {
        $this->overwrite = $overwrite;
    }

    /**
     * The more conventional naming for method to set destination file.
     */
    public function setDestFile(File $f): void
    {
        $this->to = $f;
    }

    /**
     * Sets the behavior when the destination exists. If set to
     * <code>true</code> the task will append the stream data an
     * {@link Appendable} resource; otherwise existing content will be
     * overwritten. Defaults to <code>false</code>.
     *
     * @param bool $append if true append output
     */
    public function setAppend(bool $append): void
    {
        $this->append = $append;
    }

    /**
     * Specify the end of line to find and to add if
     * not present at end of each input file. This attribute
     * is used in conjunction with fixlastline.
     *
     * @param string $crlf the type of new line to add -
     *                     cr, mac, lf, unix, crlf, or dos
     */
    public function setEol($crlf): void
    {
        $s = $crlf;
        if ('cr' === $s || 'mac' === $s) {
            $this->eolString = "\r";
        } elseif ('lf' === $s || 'unix' === $s) {
            $this->eolString = "\n";
        } elseif ('crlf' === $s || 'dos' === $s) {
            $this->eolString = "\r\n";
        } else {
            $this->eolString = $this->getProject()->getProperty('line.separator');
        }
    }

    /**
     * Sets specific file to append.
     */
    public function setFile(File $f): void
    {
        $this->file = $f;
    }

    public function createPath(): Path
    {
        $path = new Path($this->getProject());
        $this->filesets[] = $path;

        return $path;
    }

    /**
     * Sets text to append.  (cannot be used in conjunction w/ files or filesets).
     */
    public function setText(string $txt): void
    {
        $this->text = $txt;
    }

    /**
     * Sets text to append. Supports CDATA.
     */
    public function addText(string $txt): void
    {
        $this->text .= $txt;
    }

    public function addHeader(TextElement $headerToAdd): void
    {
        $this->header = $headerToAdd;
    }

    public function addFooter(TextElement $footerToAdd): void
    {
        $this->footer = $footerToAdd;
    }

    /**
     * Append line.separator to files that do not end
     * with a line.separator, default false.
     *
     * @param bool $fixLastLine if true make sure each input file has
     *                          new line on the concatenated stream
     */
    public function setFixLastLine(bool $fixLastLine): void
    {
        $this->fixLastLine = $fixLastLine;
    }

    public function setSkipSanitize(bool $skipSanitize): void
    {
        $this->skipSanitize = $skipSanitize;
    }

    /**
     * Append the file(s).
     *
     * {@inheritdoc}
     */
    public function main()
    {
        $this->validate();

        try {
            if (null !== $this->to) {
                // create a file writer to append to "to" file.
                $writer = new FileWriter($this->to, $this->append);
            } else {
                $writer = new LogWriter($this);
            }

            if (null !== $this->text) {
                // simply append the text
                if ($this->to instanceof File) {
                    $this->log('Appending string to ' . $this->to->getPath());
                }

                $text = $this->text;
                if ($this->filtering) {
                    $fr = $this->getFilteredReader(new StringReader($text));
                    $text = $fr->read();
                }

                $text = $this->appendHeader($text);
                $text = $this->appendFooter($text);
                $writer->write($text);
            } else {
                // append explicitly-specified file
                if (null !== $this->file) {
                    try {
                        $this->appendFile($writer, $this->file);
                    } catch (Exception $ioe) {
                        $this->log(
                            'Unable to append contents of file ' . $this->file->getAbsolutePath() . ': ' . $ioe->getMessage(),
                            Project::MSG_WARN
                        );
                    }
                }

                // append any files in filesets
                foreach ($this->filesets as $fs) {
                    try {
                        if ($fs instanceof Path) {
                            $files = $fs->listPaths();
                            $this->appendFiles($writer, $files);
                        } elseif ($fs instanceof FileSet) {
                            $files = $fs->getDirectoryScanner($this->project)->getIncludedFiles();
                            $this->appendFiles($writer, $files, $fs->getDir($this->project));
                        }
                    } catch (BuildException $be) {
                        if (false === strpos($be->getMessage(), 'is the same as the output file')) {
                            $this->log($be->getMessage(), Project::MSG_WARN);
                        } else {
                            throw new BuildException($be->getMessage());
                        }
                    } catch (IOException $ioe) {
                        throw new BuildException($ioe);
                    }
                }

                foreach ($this->filelists as $list) {
                    $dir = $list->getDir($this->project);
                    $files = $list->getFiles($this->project);
                    foreach ($files as $file) {
                        $this->appendFile($writer, new File($dir, $file));
                    }
                }
            }
        } catch (Exception $e) {
            throw new BuildException($e);
        }

        $writer->close();
    }

    private function appendHeader($string): string
    {
        $result = $string;
        if (null !== $this->header) {
            $header = $this->header->getValue();
            if ($this->header->filtering) {
                $fr = $this->getFilteredReader(new StringReader($header));
                $header = $fr->read();
            }

            $result = $header . $string;
        }

        return $result;
    }

    private function appendFooter($string): string
    {
        $result = $string;
        if (null !== $this->footer) {
            $footer = $this->footer->getValue();
            if ($this->footer->filtering) {
                $fr = $this->getFilteredReader(new StringReader($footer));
                $footer = $fr->read();
            }

            $result = $string . $footer;
        }

        return $result;
    }

    private function validate(): void
    {
        if (!$this->skipSanitize) {
            $this->sanitizeText();
        }

        if (null === $this->file && null === $this->text && 0 === count($this->filesets) && 0 === count($this->filelists)) {
            throw new BuildException('You must specify a file, use a filelist/fileset, or specify a text value.');
        }

        if (null !== $this->text && (null !== $this->file || count($this->filesets) > 0)) {
            throw new BuildException('Cannot use text attribute in conjunction with file or fileset');
        }

        if (!$this->eolString) {
            $this->eolString = $this->getProject()->getProperty('line.separator');
        }
    }

    private function sanitizeText(): void
    {
        if (null !== $this->text && '' === trim($this->text)) {
            $this->text = null;
        }
    }

    private function getFilteredReader(Reader $r)
    {
        return FileUtils::getChainedReader($r, $this->filterChains, $this->getProject());
    }

    /**
     * Append an array of files in a directory.
     *
     * @param Writer $writer the FileWriter that is appending to target file
     * @param array  $files  array of files to delete; can be of zero length
     * @param File   $dir    directory to work from
     */
    private function appendFiles(Writer $writer, $files, File $dir = null): void
    {
        if (!empty($files)) {
            $this->log(
                'Attempting to append ' . count(
                    $files
                ) . ' files' . (null !== $dir ? ', using basedir ' . $dir->getPath() : '')
            );
            $basenameSlot = Register::getSlot('task.append.current_file');
            $pathSlot = Register::getSlot('task.append.current_file.path');
            foreach ($files as $file) {
                try {
                    if (!$this->checkFilename($file, $dir)) {
                        continue;
                    }

                    if (null !== $dir) {
                        $file = is_string($file) ? new File($dir->getPath(), $file) : $file;
                    } else {
                        $file = is_string($file) ? new File($file) : $file;
                    }
                    $basenameSlot->setValue($file);
                    $pathSlot->setValue($file->getPath());
                    $this->appendFile($writer, $file);
                } catch (IOException $ioe) {
                    $this->log(
                        'Unable to append contents of file ' . $file . ': ' . $ioe->getMessage(),
                        Project::MSG_WARN
                    );
                } catch (\InvalidArgumentException $npe) {
                    $this->log(
                        'Unable to append contents of file ' . $file . ': ' . $npe->getMessage(),
                        Project::MSG_WARN
                    );
                }
            }
        }
    }

    private function checkFilename($filename, $dir = null): bool
    {
        if (null !== $dir) {
            $f = new File($dir, $filename);
        } else {
            $f = new File($filename);
        }

        if (!$f->exists()) {
            $this->log('File ' . (string) $f . ' does not exist.', Project::MSG_ERR);

            return false;
        }
        if (null !== $this->to && $f->equals($this->to)) {
            throw new BuildException(
                'Input file "'
                . $f . '" '
                . 'is the same as the output file.'
            );
        }

        if (
            null !== $this->to
            && !$this->overwrite
            && $this->to->exists()
            && $f->lastModified() > $this->to->lastModified()
        ) {
            $this->log((string) $this->to . ' is up-to-date.', Project::MSG_VERBOSE);

            return false;
        }

        return true;
    }

    /**
     * @throws IOException
     */
    private function appendFile(Writer $writer, File $f): void
    {
        $in = $this->getFilteredReader(new FileReader($f));

        $text = '';
        while (-1 !== ($buffer = $in->read())) { // -1 indicates EOF
            $text .= $buffer;
        }
        if ($this->fixLastLine && ("\n" !== $text[strlen($text) - 1] || "\r" !== $text[strlen($text) - 1])) {
            $text .= $this->eolString;
        }

        $text = $this->appendHeader($text);
        $text = $this->appendFooter($text);
        $writer->write($text);
        if ($f instanceof File && $this->to instanceof File) {
            $this->log('Appending contents of ' . $f->getPath() . ' to ' . $this->to->getPath());
        }
    }
}

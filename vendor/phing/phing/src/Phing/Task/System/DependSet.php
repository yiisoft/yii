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

use DateTime;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Project;
use Phing\Type\FileList;
use Phing\Type\FileSet;

/**
 * Examines and removes out of date target files.  If any of the target files
 * are out of date with respect to any of the source files, all target
 * files are removed.  This is useful where dependencies cannot be
 * computed (for example, dynamically interpreted parameters or files
 * that need to stay in synch but are not directly linked) or where
 * the phing task in question could compute them but does not.
 *
 * nested arguments:
 * <ul>
 * <li>srcfileset     (fileset describing the source files to examine)
 * <li>srcfilelist    (filelist describing the source files to examine)
 * <li>targetfileset  (fileset describing the target files to examine)
 * <li>targetfilelist (filelist describing the target files to examine)
 * </ul>
 * At least one instance of either a fileset or filelist for both source and
 * target are required.
 * <p>
 * This task will examine each of the source files against each of the target
 * files. If any target files are out of date with respect to any of the source
 * files, all targets are removed. If any files named in a (src or target)
 * filelist do not exist, all targets are removed.
 * Hint: If missing files should be ignored, specify them as include patterns
 * in filesets, rather than using filelists.
 * </p><p>
 * This task attempts to optimize speed of dependency checking.  It will stop
 * after the first out of date file is found and remove all targets, rather
 * than exhaustively checking every source vs target combination unnecessarily.
 * </p>
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class DependSet extends MatchingTask
{
    /**
     * @var FileSet[]
     */
    private $sourceFileSets = [];

    /**
     * @var FileList[]
     */
    private $sourceFileLists = [];

    /**
     * @var FileSet[]
     */
    private $targetFileSets = [];

    /**
     * @var FileList[]
     */
    private $targetFileLists = [];

    /**
     * Add a set of source files.
     *
     * @param FileSet $fs the FileSet to add
     */
    public function addSrcfileset(FileSet $fs)
    {
        $this->sourceFileSets[] = $fs;
    }

    /**
     * Add a list of source files.
     *
     * @param FileList $fl the FileList to add
     */
    public function addSrcfilelist(FileList $fl)
    {
        $this->sourceFileLists[] = $fl;
    }

    /**
     * Add a set of target files.
     *
     * @param FileSet $fs the FileSet to add
     */
    public function addTargetfileset(FileSet $fs)
    {
        $this->targetFileSets[] = $fs;
    }

    /**
     * Add a list of target files.
     *
     * @param FileList $fl the FileList to add
     */
    public function addTargetfilelist(FileList $fl)
    {
        $this->targetFileLists[] = $fl;
    }

    /**
     * Executes the task.
     *
     * @throws BuildException if errors occur
     */
    public function main()
    {
        if ((0 === count($this->sourceFileSets)) && (0 === count($this->sourceFileLists))) {
            throw new BuildException(
                'At least one <srcfileset> or <srcfilelist>'
                . ' element must be set'
            );
        }
        if ((0 === count($this->targetFileSets)) && (0 === count($this->targetFileLists))) {
            throw new BuildException(
                'At least one <targetfileset> or'
                . ' <targetfilelist> element must be set'
            );
        }
        $now = (new DateTime())->getTimestamp();
        /*
          We have to munge the time to allow for the filesystem time
          granularity.
        */
        // $now += FILE_UTILS . getFileTimestampGranularity();

        // Grab all the target files specified via filesets:
        $allTargets = [];
        $oldestTargetTime = 0;
        $oldestTarget = null;
        foreach ($this->targetFileSets as $targetFS) {
            if (!$targetFS->getDir($this->getProject())->exists()) {
                // this is the same as if it was empty, no target files found
                continue;
            }
            $targetDS = $targetFS->getDirectoryScanner($this->getProject());
            $targetFiles = $targetDS->getIncludedFiles();

            foreach ($targetFiles as $targetFile) {
                $dest = new File($targetFS->getDir($this->getProject()), $targetFile);
                $allTargets[] = $dest;

                if ($dest->lastModified() > $now) {
                    $this->log(
                        'Warning: ' . $targetFile . ' modified in the future.',
                        Project::MSG_WARN
                    );
                }
                if (
                    null === $oldestTarget
                    || $dest->lastModified() < $oldestTargetTime
                ) {
                    $oldestTargetTime = $dest->lastModified();
                    $oldestTarget = $dest;
                }
            }
        }
        // Grab all the target files specified via filelists:
        $upToDate = true;
        foreach ($this->targetFileLists as $targetFL) {
            $targetFiles = $targetFL->getFiles($this->getProject());

            foreach ($targetFiles as $targetFile) {
                $dest = new File($targetFL->getDir($this->getProject()), $targetFile);
                if (!$dest->exists()) {
                    $this->log($targetFile . ' does not exist.', Project::MSG_VERBOSE);
                    $upToDate = false;

                    continue;
                }

                $allTargets[] = $dest;
                if ($dest->lastModified() > $now) {
                    $this->log(
                        'Warning: ' . $targetFile . ' modified in the future.',
                        Project::MSG_WARN
                    );
                }
                if (
                    null === $oldestTarget
                    || $dest->lastModified() < $oldestTargetTime
                ) {
                    $oldestTargetTime = $dest->lastModified();
                    $oldestTarget = $dest;
                }
            }
        }
        if (null !== $oldestTarget) {
            $this->log($oldestTarget . ' is oldest target file', Project::MSG_VERBOSE);
        } else {
            // no target files, then we cannot remove any target files and
            // skip the following tests right away
            $upToDate = false;
        }
        // Check targets vs source files specified via filelists:
        if ($upToDate) {
            foreach ($this->sourceFileLists as $sourceFL) {
                $sourceFiles = $sourceFL->getFiles($this->getProject());

                foreach ($sourceFiles as $sourceFile) {
                    $src = new File($sourceFL->getDir($this->getProject()), $sourceFile);

                    if ($src->lastModified() > $now) {
                        $this->log(
                            'Warning: ' . $sourceFile
                            . ' modified in the future.',
                            Project::MSG_WARN
                        );
                    }
                    if (!$src->exists()) {
                        $this->log(
                            $sourceFile . ' does not exist.',
                            Project::MSG_VERBOSE
                        );
                        $upToDate = false;

                        break 2;
                    }
                    if ($src->lastModified() > $oldestTargetTime) {
                        $upToDate = false;
                        $this->log(
                            $oldestTarget . ' is out of date with respect to '
                            . $sourceFile,
                            Project::MSG_VERBOSE
                        );

                        break 2;
                    }
                }
            }
        }
        // Check targets vs source files specified via filesets:
        if ($upToDate) {
            foreach ($this->sourceFileSets as $sourceFS) {
                $sourceDS = $sourceFS->getDirectoryScanner($this->getProject());
                $sourceFiles = $sourceDS->getIncludedFiles();

                foreach ($sourceFiles as $sourceFile) {
                    $src = new File($sourceFS->getDir($this->getProject()), $sourceFile);

                    if ($src->lastModified() > $now) {
                        $this->log(
                            'Warning: ' . $sourceFile
                            . ' modified in the future.',
                            Project::MSG_WARN
                        );
                    }
                    if ($src->lastModified() > $oldestTargetTime) {
                        $upToDate = false;
                        $this->log(
                            $oldestTarget . ' is out of date with respect to '
                            . $sourceFile,
                            Project::MSG_VERBOSE
                        );

                        break 2;
                    }
                }
            }
        }
        if (!$upToDate) {
            $this->log('Deleting all target files. ', Project::MSG_VERBOSE);
            foreach ($allTargets as $fileToRemove) {
                $this->log(
                    'Deleting file ' . $fileToRemove->getAbsolutePath(),
                    Project::MSG_VERBOSE
                );
                $fileToRemove->delete();
            }
        }
    }
}

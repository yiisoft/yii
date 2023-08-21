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

namespace Phing\Task\Ext\Git;

use Phing\Exception\BuildException;
use Phing\Project;

/**
 * Wrapper around git-clone
 *
 * @author  Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext.git
 * @see     VersionControl_Git
 * @since   2.4.3
 */
class GitCloneTask extends GitBaseTask
{
    /**
     * Whether --depth key should be set for git-clone
     *
     * @var int
     */
    private $depth = 0;

    /**
     * Whether --bare key should be set for git-clone
     *
     * @var bool
     */
    private $isBare = false;

    /**
     * Whether --single-branch key should be set for git-clone
     *
     * @var bool
     */
    private $singleBranch = false;

    /**
     * Branch to check out
     *
     * @var string
     */
    private $branch = "";

    /**
     * Path to target directory
     *
     * @var string
     */
    private $targetPath;

    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        if (null === $this->getTargetPath()) {
            throw new BuildException('"targetPath" is required parameter');
        }

        $files = @scandir($this->getTargetPath());
        if (is_array($files) && (count($files) > 2)) {
            throw new BuildException(
                sprintf(
                    '"%s" target directory is not empty',
                    $this->getTargetPath()
                )
            );
        }

        try {
            $this->doClone($this->getGitClient(false, getcwd()));
        } catch (\Exception $e) {
            throw new BuildException('The remote end hung up unexpectedly', $e);
        }

        $msg = 'git-clone: cloning '
            . ($this->isBare() ? '(bare) ' : '')
            . ($this->hasDepth() ? ' (depth="' . $this->getDepth() . '") ' : '')
            . '"' . $this->getRepository() . '" repository'
            . ' to "' . $this->getTargetPath() . '" directory';
        $this->log($msg, Project::MSG_INFO);
    }

    /**
     * Create a new clone
     *
     * @param \VersionControl_Git $client
     *
     * @throws \VersionControl_Git_Exception
     */
    protected function doClone(\VersionControl_Git $client)
    {
        $command = $client->getCommand('clone')
            ->setOption('q')
            ->setOption('bare', $this->isBare())
            ->setOption('single-branch', $this->isSingleBranch())
            ->setOption('depth', $this->hasDepth() ? $this->getDepth() : false)
            ->setOption('branch', $this->hasBranch() ? $this->getBranch() : false)
            ->addArgument($this->getRepository())
            ->addArgument($this->getTargetPath());

        if (is_dir($this->getTargetPath()) && version_compare('1.6.1.4', $client->getGitVersion(), '>=')) {
            $isEmptyDir = true;
            $entries = scandir($this->getTargetPath());
            foreach ($entries as $entry) {
                if ('.' !== $entry && '..' !== $entry) {
                    $isEmptyDir = false;

                    break;
                }
            }

            if ($isEmptyDir) {
                @rmdir($this->getTargetPath());
            }
        }

        $command->execute();
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }

    /**
     * @return bool
     */
    public function hasDepth()
    {
        return $this->depth > 0;
    }

    /**
     * Get path to target direcotry repo
     *
     * @return string
     */
    public function getTargetPath()
    {
        return $this->targetPath;
    }

    /**
     * Set path to source repo
     *
     * @param  string $targetPath Path to repository used as source
     * @return void
     */
    public function setTargetPath($targetPath)
    {
        $this->targetPath = $targetPath;
    }

    /**
     * Alias @see getBare()
     *
     * @return bool
     */
    public function isBare()
    {
        return $this->getBare();
    }

    /**
     * @return bool
     */
    public function getBare()
    {
        return $this->isBare;
    }

    /**
     * @param bool $bare
     */
    public function setBare(bool $bare)
    {
        $this->isBare = $bare;
    }

    /**
     * @return boolean
     */
    public function isSingleBranch()
    {
        return $this->singleBranch;
    }

    /**
     * @param boolean $singleBranch
     */
    public function setSingleBranch($singleBranch)
    {
        $this->singleBranch = $singleBranch;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @return bool
     */
    public function hasBranch()
    {
        return !empty($this->branch);
    }

    /**
     * @param string $branch
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
    }
}

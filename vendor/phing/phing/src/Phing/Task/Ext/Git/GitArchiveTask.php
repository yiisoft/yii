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
use Phing\Io\File;
use Phing\Project;

/**
 * Repository archive task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.git
 * @see     VersionControl_Git
 */
class GitArchiveTask extends GitBaseTask
{
    /**
     * @var string|false $format
     */
    private $format = false;

    /**
     * @var File $output
     */
    private $output;

    /**
     * @var string|false $prefix
     */
    private $prefix = false;

    /**
     * @var string $treeish
     */
    private $treeish;

    /**
     * @var string|false $remoteRepo
     */
    private $remoteRepo = false;

    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository() && false === $this->getRemoteRepo()) {
            throw new BuildException('"repository" is required parameter');
        }

        if (null === $this->getTreeish()) {
            throw new BuildException('"treeish" is required parameter');
        }

        $cmd = $this->getGitClient(false, $this->getRepository() ?? './')
            ->getCommand('archive')
            ->setOption('prefix', $this->prefix)
            ->setOption('output', $this->output !== null ? $this->output->getPath() : false)
            ->setOption('format', $this->format)
            ->setOption('remote', $this->remoteRepo)
            ->addArgument($this->treeish);

        $this->log('Git command : ' . $cmd->createCommandString(), Project::MSG_DEBUG);

        $cmd->execute();

        $msg = 'git-archive: archivating "' . $this->getRepository() . '" repository (' . $this->getTreeish() . ')';
        $this->log($msg, Project::MSG_INFO);
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return File
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param File $output
     */
    public function setOutput(File $output)
    {
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getTreeish()
    {
        return $this->treeish;
    }

    /**
     * @param string $treeish
     */
    public function setTreeish($treeish)
    {
        $this->treeish = $treeish;
    }

    /**
     * @return string
     */
    public function getRemoteRepo()
    {
        return $this->remoteRepo;
    }

    /**
     * @param string $repo
     */
    public function setRemoteRepo($repo)
    {
        $this->remoteRepo = $repo;
    }
}

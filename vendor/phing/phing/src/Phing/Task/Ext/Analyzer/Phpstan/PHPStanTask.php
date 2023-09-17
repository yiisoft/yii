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

declare(strict_types=1);

namespace Phing\Task\Ext\Analyzer\Phpstan;

use Phing\Task;
use Phing\Task\Ext\Analyzer\Phpstan\CommandBuilder\PHPStanCommandBuilderFactory;
use Phing\Task\System\ExecTask;
use Phing\Type\Commandline;
use Phing\Type\Element\FileSetAware;

class PHPStanTask extends Task
{
    use FileSetAware;

    /**
     * @var string
     */
    private $executable = 'phpstan';

    /**
     * @var string
     */
    private $command = 'analyse';

    /**
     * @var bool
     */
    private $help;

    /**
     * @var bool
     */
    private $quiet;

    /**
     * @var bool
     */
    private $version;

    /**
     * @var bool
     */
    private $ansi;

    /**
     * @var bool
     */
    private $noAnsi;

    /**
     * @var bool
     */
    private $noInteraction;

    /**
     * @var bool
     */
    private $verbose;

    /**
     * @var string
     */
    private $configuration;

    /**
     * @var string
     */
    private $level;

    /**
     * @var bool
     */
    private $noProgress;

    /**
     * @var bool
     */
    private $checkreturn;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var string
     */
    private $autoloadFile;

    /**
     * @var string
     */
    private $errorFormat;

    /**
     * @var string
     */
    private $memoryLimit;

    /**
     * @var string
     */
    private $format;

    /**
     * @var bool
     */
    private $raw;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string Analyse command paths
     */
    private $paths;

    /**
     * @var string Help command command name
     */
    private $commandName;

    /**
     * @var Commandline
     */
    private $cmd;

    public function __construct()
    {
        $this->cmd = new Commandline();
        parent::__construct();
    }

    public function getCommandline(): Commandline
    {
        return $this->cmd;
    }

    public function getExecutable(): string
    {
        return $this->executable;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function isHelp(): ?bool
    {
        return $this->help;
    }

    public function isQuiet(): ?bool
    {
        return $this->quiet;
    }

    public function isVersion(): ?bool
    {
        return $this->version;
    }

    public function isAnsi(): ?bool
    {
        return $this->ansi;
    }

    public function isNoAnsi(): ?bool
    {
        return $this->noAnsi;
    }

    public function isNoInteraction(): ?bool
    {
        return $this->noInteraction;
    }

    public function isVerbose(): ?bool
    {
        return $this->verbose;
    }

    public function getConfiguration(): ?string
    {
        return $this->configuration;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function isNoProgress(): ?bool
    {
        return $this->noProgress;
    }

    public function isCheckreturn(): ?bool
    {
        return $this->checkreturn;
    }

    public function isDebug(): ?bool
    {
        return $this->debug;
    }

    public function getAutoloadFile(): ?string
    {
        return $this->autoloadFile;
    }

    public function getErrorFormat(): ?string
    {
        return $this->errorFormat;
    }

    public function getMemoryLimit(): ?string
    {
        return $this->memoryLimit;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function isRaw(): ?bool
    {
        return $this->raw;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getPaths(): ?string
    {
        return $this->paths;
    }

    public function getCommandName(): ?string
    {
        return $this->commandName;
    }

    public function setExecutable(string $executable): void
    {
        $this->executable = $executable;
    }

    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    public function setHelp(bool $help): void
    {
        $this->help = $help;
    }

    public function setQuiet(bool $quiet): void
    {
        $this->quiet = $quiet;
    }

    public function setVersion(bool $version): void
    {
        $this->version = $version;
    }

    public function setAnsi(bool $ansi): void
    {
        $this->ansi = $ansi;
    }

    public function setNoAnsi(bool $noAnsi): void
    {
        $this->noAnsi = $noAnsi;
    }

    public function setNoInteraction(bool $noInteraction): void
    {
        $this->noInteraction = $noInteraction;
    }

    public function setVerbose(bool $verbose): void
    {
        $this->verbose = $verbose;
    }

    public function setConfiguration(string $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    public function setNoProgress(bool $noProgress): void
    {
        $this->noProgress = $noProgress;
    }

    public function setCheckreturn(bool $checkreturn)
    {
        $this->checkreturn = $checkreturn;
    }

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    public function setAutoloadFile(string $autoloadFile): void
    {
        $this->autoloadFile = $autoloadFile;
    }

    public function setErrorFormat(string $errorFormat): void
    {
        $this->errorFormat = $errorFormat;
    }

    public function setMemoryLimit(string $memoryLimit): void
    {
        $this->memoryLimit = $memoryLimit;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function setRaw(bool $raw): void
    {
        $this->raw = $raw;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function setPaths(string $paths): void
    {
        $this->paths = $paths;
    }

    public function setCommandName(string $commandName): void
    {
        $this->commandName = $commandName;
    }

    public function main()
    {
        $commandBuilder = (new PHPStanCommandBuilderFactory())->createBuilder($this);
        $commandBuilder->build($this);

        $toExecute = $this->cmd;

        $exe = new ExecTask();
        $exe->setExecutable($toExecute->getExecutable());
        $exe->createArg()->setLine(implode(' ', $toExecute->getArguments()));
        $exe->setCheckreturn($this->checkreturn);
        $exe->setLocation($this->getLocation());
        $exe->setProject($this->getProject());
        $exe->setLevel('info');
        $exe->main();
    }
}

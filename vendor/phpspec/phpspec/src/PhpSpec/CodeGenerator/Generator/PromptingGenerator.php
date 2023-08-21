<?php

/*
 * This file is part of PhpSpec, A php toolset to drive emergent
 * design by specification.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpSpec\CodeGenerator\Generator;

use PhpSpec\Console\ConsoleIO;
use PhpSpec\CodeGenerator\TemplateRenderer;
use PhpSpec\Process\Context\ExecutionContext;
use PhpSpec\Util\Filesystem;
use PhpSpec\Locator\Resource;

/**
 * Base class with common behaviour for generating class and spec class
 */
abstract class PromptingGenerator implements Generator
{
    /**
     * @var ConsoleIO
     */
    private $io;

    /**
     * @var TemplateRenderer
     */
    private $templates;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ExecutionContext
     */
    private $executionContext;

    
    public function __construct(ConsoleIO $io, TemplateRenderer $templates, Filesystem $filesystem, ExecutionContext $executionContext)
    {
        $this->io         = $io;
        $this->templates  = $templates;
        $this->filesystem = $filesystem;
        $this->executionContext = $executionContext;
    }

    
    public function generate(Resource $resource, array $data = array()): void
    {
        $filepath = $this->getFilePath($resource);

        if ($this->fileAlreadyExists($filepath)) {
            if ($this->userAborts($filepath)) {
                return;
            }

            $this->io->writeln();
        }

        $this->createDirectoryIfItDoesExist($filepath);
        $this->generateFileAndRenderTemplate($resource, $filepath);
        $this->executionContext->addGeneratedType($resource->getSrcClassname());
    }

    protected function getTemplateRenderer(): TemplateRenderer
    {
        return $this->templates;
    }

    abstract protected function getFilePath(Resource $resource): string;

    abstract protected function renderTemplate(Resource $resource, string $filepath): string;

    
    abstract protected function getGeneratedMessage(Resource $resource, string $filepath): string;

    private function fileAlreadyExists(string $filepath): bool
    {
        return $this->filesystem->pathExists($filepath);
    }

    private function userAborts(string $filepath): bool
    {
        $message = sprintf('File "%s" already exists. Overwrite?', basename($filepath));

        return !$this->io->askConfirmation($message, false);
    }

    private function createDirectoryIfItDoesExist(string $filepath)
    {
        $path = dirname($filepath);
        if (!$this->filesystem->isDirectory($path)) {
            $this->filesystem->makeDirectory($path);
        }
    }

    private function generateFileAndRenderTemplate(Resource $resource, string $filepath)
    {
        $content = $this->renderTemplate($resource, $filepath);

        $this->filesystem->putFileContents($filepath, $content);
        $this->io->writeln($this->getGeneratedMessage($resource, $filepath));
    }
}

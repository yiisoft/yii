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
use PhpSpec\CodeGenerator\Writer\CodeWriter;
use PhpSpec\Util\Filesystem;
use PhpSpec\Locator\Resource;

/**
 * Generates class methods from a resource
 */
final class MethodGenerator implements Generator
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
     * @var CodeWriter
     */
    private $codeWriter;

    
    public function __construct(ConsoleIO $io, TemplateRenderer $templates, Filesystem $filesystem, CodeWriter $codeWriter)
    {
        $this->io         = $io;
        $this->templates  = $templates;
        $this->filesystem = $filesystem;
        $this->codeWriter = $codeWriter;
    }

    public function supports(Resource $resource, string $generation, array $data): bool
    {
        return 'method' === $generation;
    }

    
    public function generate(Resource $resource, array $data = array()): void
    {
        $filepath  = $resource->getSrcFilename();
        $name      = $data['name'];
        $arguments = $data['arguments'];

        $argString = \count($arguments)
            ? '$argument'.implode(', $argument', range(1, \count($arguments)))
            : ''
        ;

        $values = array('%name%' => $name, '%arguments%' => $argString);
        if (!$content = $this->templates->render('method', $values)) {
            $content = $this->templates->renderString(
                $this->getTemplate(),
                $values
            );
        }

        $code = $this->filesystem->getFileContents($filepath);
        $this->filesystem->putFileContents($filepath, $this->getUpdatedCode($name, $content, $code));

        $this->io->writeln(sprintf(
            "<info>Method <value>%s::%s()</value> has been created.</info>\n",
            $resource->getSrcClassname(),
            $name
        ), 2);
    }

    public function getPriority(): int
    {
        return 0;
    }

    protected function getTemplate(): string
    {
        return file_get_contents(__DIR__.'/templates/method.template');
    }

    private function getUpdatedCode(string $methodName, string $snippetToInsert, string $code): string
    {
        if ('__construct' === $methodName) {
            return $this->codeWriter->insertMethodFirstInClass($code, $snippetToInsert);
        }
        return $this->codeWriter->insertMethodLastInClass($code, $snippetToInsert);
    }
}

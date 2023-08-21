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
use PhpSpec\Util\Filesystem;
use PhpSpec\Locator\Resource;

/**
 * Generates interface method signatures from a resource
 */
final class MethodSignatureGenerator implements Generator
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

    
    public function __construct(ConsoleIO $io, TemplateRenderer $templates, Filesystem $filesystem)
    {
        $this->io         = $io;
        $this->templates  = $templates;
        $this->filesystem = $filesystem;
    }

    public function supports(Resource $resource, string $generation, array $data): bool
    {
        return 'method-signature' === $generation;
    }

    public function generate(Resource $resource, array $data = array()): void
    {
        $filepath  = $resource->getSrcFilename();
        $name      = $data['name'];
        $arguments = $data['arguments'];

        $argString = $this->buildArgumentString($arguments);

        $values = array('%name%' => $name, '%arguments%' => $argString);
        if (!$content = $this->templates->render('interface-method-signature', $values)) {
            $content = $this->templates->renderString(
                $this->getTemplate(), $values
            );
        }

        $this->insertMethodSignature($filepath, $content);

        $this->io->writeln(sprintf(
            "<info>Method signature <value>%s::%s()</value> has been created.</info>\n",
            $resource->getSrcClassname(), $name
        ), 2);
    }

    public function getPriority(): int
    {
        return 0;
    }

    protected function getTemplate(): string
    {
        return file_get_contents(__DIR__.'/templates/interface_method_signature.template');
    }

    private function insertMethodSignature(string $filepath, string $content)
    {
        $code = $this->filesystem->getFileContents($filepath);
        $code = preg_replace('/}[ \n]*$/', rtrim($content) . "\n}\n", trim($code));
        $this->filesystem->putFileContents($filepath, $code);
    }

    private function buildArgumentString(array $arguments): string
    {
        $argString = \count($arguments)
            ? '$argument' . implode(', $argument', range(1, \count($arguments)))
            : '';
        return $argString;
    }
}

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

use PhpSpec\CodeGenerator\TemplateRenderer;
use PhpSpec\Console\ConsoleIO;
use PhpSpec\Locator\Resource;
use PhpSpec\CodeGenerator\Writer\CodeWriter;
use PhpSpec\Util\Filesystem;

final class PrivateConstructorGenerator implements Generator
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
        return 'private-constructor' === $generation;
    }

    
    public function generate(Resource $resource, array $data): void
    {
        $filepath  = $resource->getSrcFilename();

        if (!$content = $this->templates->render('private-constructor', array())) {
            $content = $this->templates->renderString(
                $this->getTemplate(),
                array()
            );
        }

        $code = $this->filesystem->getFileContents($filepath);
        $code = $this->codeWriter->insertMethodFirstInClass($code, $content);
        $this->filesystem->putFileContents($filepath, $code);

        $this->io->writeln("<info>Private constructor has been created.</info>\n", 2);
    }

    public function getPriority(): int
    {
        return 0;
    }

    protected function getTemplate(): string
    {
        return file_get_contents(__DIR__.'/templates/private-constructor.template');
    }
}

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
use PhpSpec\Util\Filesystem;

final class ReturnConstantGenerator implements Generator
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
        $this->io = $io;
        $this->templates = $templates;
        $this->filesystem = $filesystem;
    }

    public function supports(Resource $resource, string $generation, array $data): bool
    {
        return 'returnConstant' == $generation;
    }

    
    public function generate(Resource $resource, array $data): void
    {
        $method = $data['method'];
        $expected = $data['expected'];

        $code = $this->filesystem->getFileContents($resource->getSrcFilename());

        $values = array('%constant%' => var_export($expected, true));
        if (!$content = $this->templates->render('method', $values)) {
            $content = $this->templates->renderString(
                $this->getTemplate(),
                $values
            );
        }

        $pattern = '/'.'(function\s+'.preg_quote($method, '/').'\s*\([^\)]*\))\s+{[^}]*?}/';
        $replacement = '$1'.$content;

        $modifiedCode = preg_replace($pattern, $replacement, $code);

        $this->filesystem->putFileContents($resource->getSrcFilename(), $modifiedCode);

        $this->io->writeln(sprintf(
            "<info>Method <value>%s::%s()</value> has been modified.</info>\n",
            $resource->getSrcClassname(),
            $method
        ), 2);
    }

    public function getPriority(): int
    {
        return 0;
    }

    protected function getTemplate(): string
    {
        return file_get_contents(__DIR__.'/templates/returnconstant.template');
    }
}

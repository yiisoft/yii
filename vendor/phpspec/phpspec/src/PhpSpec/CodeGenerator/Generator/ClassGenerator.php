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

use PhpSpec\Locator\Resource;

/**
 * The Class Generator is responsible for generating the classes from a resource
 * in the appropriate folder using the template provided
 */
final class ClassGenerator extends PromptingGenerator
{
    
    public function supports(Resource $resource, string $generation, array $data): bool
    {
        return 'class' === $generation;
    }

    public function getPriority(): int
    {
        return 0;
    }

    protected function renderTemplate(Resource $resource, string $filepath): string
    {
        $values = array(
            '%filepath%'        => $filepath,
            '%name%'            => $resource->getName(),
            '%namespace%'       => $resource->getSrcNamespace(),
            '%namespace_block%' => '' !== $resource->getSrcNamespace()
                                ?  sprintf("\n\nnamespace %s;", $resource->getSrcNamespace())
                                : '',
        );

        if (!$content = $this->getTemplateRenderer()->render('class', $values)) {
            $content = $this->getTemplateRenderer()->renderString(
                $this->getTemplate(),
                $values
            );
        }

        return $content;
    }

    protected function getTemplate(): string
    {
        return file_get_contents(__DIR__.'/templates/class.template');
    }

    protected function getFilePath(Resource $resource): string
    {
        return $resource->getSrcFilename();
    }

    protected function getGeneratedMessage(Resource $resource, string $filepath): string
    {
        return sprintf(
            "<info>Class <value>%s</value> created in <value>%s</value>.</info>\n",
            $resource->getSrcClassname(),
            $filepath
        );
    }
}

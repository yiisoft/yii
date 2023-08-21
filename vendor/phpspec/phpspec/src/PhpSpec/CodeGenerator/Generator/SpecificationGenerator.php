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
use PhpSpec\ObjectBehavior;

/**
 * Generates spec classes from resources and puts them into the appropriate
 * folder using the appropriate template.
 */
final class SpecificationGenerator extends PromptingGenerator
{
    public function supports(Resource $resource, string $generation, array $data): bool
    {
        return 'specification' === $generation;
    }

    public function getPriority(): int
    {
        return 0;
    }

    
    protected function renderTemplate(Resource $resource, string $filepath): string
    {
        $values = array(
            '%filepath%'      => $filepath,
            '%name%'          => $resource->getSpecName(),
            '%namespace%'     => $resource->getSpecNamespace(),
            '%imports%'       => $this->getImports($resource),
            '%subject%'       => $resource->getSrcClassname(),
            '%subject_class%' => $resource->getName(),
        );

        if (!$content = $this->getTemplateRenderer()->render('specification', $values)) {
            $content = $this->getTemplateRenderer()->renderString($this->getTemplate(), $values);
        }

        return $content;
    }

    protected function getTemplate(): string
    {
        return file_get_contents(__DIR__.'/templates/specification.template');
    }

    protected function getFilePath(Resource $resource): string
    {
        return $resource->getSpecFilename();
    }

    protected function getGeneratedMessage(Resource $resource, string $filepath): string
    {
        return sprintf(
            "<info>Specification for <value>%s</value> created in <value>%s</value>.</info>\n",
            $resource->getSrcClassname(),
            $filepath
        );
    }

    protected function getImports(Resource $resource): string
    {
        $imports = [$resource->getSrcClassname(), ObjectBehavior::class];
        asort($imports);

        foreach ($imports as &$import) {
            $import = sprintf('use %s;', $import);
        }

        return implode("\n", $imports);
    }
}

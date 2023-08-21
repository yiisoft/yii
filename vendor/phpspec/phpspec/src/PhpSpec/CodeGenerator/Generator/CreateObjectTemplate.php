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

class CreateObjectTemplate
{
    private $templates;
    private $methodName;
    private $arguments;
    private $className;

    public function __construct(TemplateRenderer $templates, $methodName, $arguments, $className)
    {
        $this->templates  = $templates;
        $this->methodName = $methodName;
        $this->arguments  = $arguments;
        $this->className  = $className;
    }

    public function getContent(): string
    {
        $values = $this->getValues();

        if (!$content = $this->templates->render('named_constructor_create_object', $values)) {
            $content = $this->templates->renderString(
                $this->getTemplate(),
                $values
            );
        }

        return $content;
    }

    private function getTemplate(): string
    {
        return file_get_contents(__DIR__.'/templates/named_constructor_create_object.template');
    }

    /**
     * @return string[]
     */
    private function getValues(): array
    {
        $argString = \count($this->arguments)
            ? '$argument'.implode(', $argument', range(1, \count($this->arguments)))
            : ''
        ;

        return array(
            '%methodName%'           => $this->methodName,
            '%arguments%'            => $argString,
            '%returnVar%'            => '$'.lcfirst($this->className),
            '%className%'            => $this->className,
            '%constructorArguments%' => ''
        );
    }
}

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
use ReflectionMethod;

class ExistingConstructorTemplate
{
    private $templates;
    private $class;
    private $className;
    private $arguments;
    private $methodName;

    public function __construct(TemplateRenderer $templates, string $methodName, array $arguments, string $className, string $class)
    {
        $this->templates  = $templates;
        $this->class      = $class;
        $this->className  = $className;
        $this->arguments  = $arguments;
        $this->methodName = $methodName;
    }

    public function getContent(): string
    {
        if (!$this->numberOfConstructorArgumentsMatchMethod()) {
            return $this->getExceptionContent();
        }

        return $this->getCreateObjectContent();
    }

    private function numberOfConstructorArgumentsMatchMethod(): bool
    {
        $constructorArguments = 0;

        $constructor = new ReflectionMethod($this->class, '__construct');
        $params = $constructor->getParameters();

        foreach ($params as $param) {
            if (!$param->isOptional()) {
                $constructorArguments++;
            }
        }

        return $constructorArguments == \count($this->arguments);
    }

    private function getExceptionContent(): string
    {
        $values = $this->getValues();

        if (!$content = $this->templates->render('named_constructor_exception', $values)) {
            $content = $this->templates->renderString(
                $this->getExceptionTemplate(),
                $values
            );
        }

        return $content;
    }

    private function getCreateObjectContent(): string
    {
        $values = $this->getValues(true);

        if (!$content = $this->templates->render('named_constructor_create_object', $values)) {
            $content = $this->templates->renderString(
                $this->getCreateObjectTemplate(),
                $values
            );
        }

        return $content;
    }

    /**
     * @return string[]
     */
    private function getValues(bool $constructorArguments = false): array
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
            '%constructorArguments%' => $constructorArguments ? $argString : ''
        );
    }

    
    private function getCreateObjectTemplate(): string
    {
        return file_get_contents(__DIR__.'/templates/named_constructor_create_object.template');
    }

    
    private function getExceptionTemplate(): string
    {
        return file_get_contents(__DIR__.'/templates/named_constructor_exception.template');
    }
}

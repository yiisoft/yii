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

namespace PhpSpec\Loader\Node;

use PhpSpec\Loader\Suite;
use PhpSpec\Locator\Resource;
use ReflectionClass;

class SpecificationNode implements \Countable
{
    /**
     * @var string
     */
    private $title;
    /**
     * @var \ReflectionClass
     */
    private $class;
    /**
     * @var Resource
     */
    private $resource;
    /**
     * @var Suite
     */
    private $suite;
    /**
     * @var ExampleNode[]
     */
    private $examples = array();

    
    public function __construct(string $title, ReflectionClass $class, Resource $resource)
    {
        $this->title    = $title;
        $this->class    = $class;
        $this->resource = $resource;
    }

    
    public function getTitle(): string
    {
        return $this->title;
    }

    
    public function getClassReflection(): ReflectionClass
    {
        return $this->class;
    }

    
    public function getResource(): Resource
    {
        return $this->resource;
    }

    
    public function addExample(ExampleNode $example): void
    {
        $this->examples[] = $example;
        $example->setSpecification($this);
    }

    /**
     * @return ExampleNode[]
     */
    public function getExamples(): array
    {
        return $this->examples;
    }

    
    public function setSuite(Suite $suite)
    {
        $this->suite = $suite;
    }

    /**
     * @return null|Suite
     */
    public function getSuite()
    {
        return $this->suite;
    }

    
    public function count(): int
    {
        return \count($this->examples);
    }
}

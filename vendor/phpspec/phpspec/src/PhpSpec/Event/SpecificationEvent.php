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

namespace PhpSpec\Event;

use PhpSpec\Loader\Suite;
use PhpSpec\Loader\Node\SpecificationNode;

/**
 * Class SpecificationEvent holds information about the specification event
 */
class SpecificationEvent extends BaseEvent implements PhpSpecEvent
{
    /**
     * @var SpecificationNode
     */
    private $specification;

    /**
     * @var float
     */
    private $time;

    /**
     * @var int
     */
    private $result;

    
    public function __construct(SpecificationNode $specification, float $time = 0.0, int $result = 0)
    {
        $this->specification = $specification;
        $this->time          = $time;
        $this->result        = $result;
    }

    
    public function getSpecification(): SpecificationNode
    {
        return $this->specification;
    }

    
    public function getTitle(): string
    {
        return $this->specification->getTitle();
    }

    
    public function getSuite(): Suite
    {
        return $this->specification->getSuite();
    }

    
    public function getTime(): float
    {
        return $this->time;
    }

    
    public function getResult(): int
    {
        return $this->result;
    }
}

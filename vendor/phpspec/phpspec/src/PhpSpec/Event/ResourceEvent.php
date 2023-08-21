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

use PhpSpec\Locator\Resource;

class ResourceEvent extends BaseEvent implements PhpSpecEvent
{
    /**
     * @var Resource
     */
    private $resource;

    private function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    public static function ignored(Resource $resource): self
    {
        return new self($resource);
    }

    public function getResource(): Resource
    {
        return $this->resource;
    }
}

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

namespace PhpSpec\Exception\Fracture;

/**
 * Class PropertyNotFoundException holds information about property not found
 * exceptions
 */
class PropertyNotFoundException extends FractureException
{
    
    private $subject;

    /**
     * @var string
     */
    private $property;

    /**
     * @param string $property
     */
    public function __construct(string $message, $subject, $property)
    {
        parent::__construct($message);

        $this->subject = $subject;
        $this->property  = $property;
    }

    
    public function getSubject()
    {
        return $this->subject;
    }

    
    public function getProperty(): string
    {
        return $this->property;
    }
}

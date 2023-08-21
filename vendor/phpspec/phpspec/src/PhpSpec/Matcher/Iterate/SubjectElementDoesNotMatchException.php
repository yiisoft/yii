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

namespace PhpSpec\Matcher\Iterate;

use PhpSpec\Exception\Example\FailureException;

class SubjectElementDoesNotMatchException extends FailureException
{
    
    public function __construct(int $elementNumber, string $subjectKey, string $subjectValue, string $expectedKey, string $expectedValue)
    {
        parent::__construct(sprintf(
            'Expected subject to have element #%d with key %s and value %s, but got key %s and value %s.',
            $elementNumber,
            $expectedKey,
            $expectedValue,
            $subjectKey,
            $subjectValue
        ));
    }
}

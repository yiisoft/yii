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

namespace PhpSpec\Formatter\Presenter\Differ;

use SebastianBergmann\Exporter\Exporter;

final class ObjectEngine implements DifferEngine
{
    /**
     * @var Exporter
     */
    private $exporter;
    /**
     * @var StringEngine
     */
    private $stringDiffer;

    
    public function __construct(Exporter $exporter, StringEngine $stringDiffer)
    {
        $this->exporter = $exporter;
        $this->stringDiffer = $stringDiffer;
    }

    
    public function supports($expected, $actual): bool
    {
        return \is_object($expected) && \is_object($actual);
    }

    /**
     * @param object $expected
     * @param object $actual
     */
    public function compare($expected, $actual): string
    {
        return $this->stringDiffer->compare(
            $this->exporter->export($expected),
            $this->exporter->export($actual)
        );
    }
}

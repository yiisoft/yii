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

namespace PhpSpec\Formatter\Presenter;

final class TaggingPresenter implements Presenter
{
    /**
     * @var Presenter
     */
    private $presenter;

    
    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    
    public function presentException(\Exception $exception, bool $verbose = false): string
    {
        return $this->presenter->presentException($exception, $verbose);
    }

    
    public function presentString(string $string): string
    {
        return sprintf('<value>%s</value>', $string);
    }

    
    public function presentValue($value): string
    {
        return $this->presentString($this->presenter->presentValue($value));
    }
}

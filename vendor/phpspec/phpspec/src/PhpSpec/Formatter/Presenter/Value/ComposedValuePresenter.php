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

namespace PhpSpec\Formatter\Presenter\Value;

final class ComposedValuePresenter implements ValuePresenter
{
    /**
     * @var TypePresenter[]
     */
    private $typePresenters = array();

    
    public function addTypePresenter(TypePresenter $typePresenter)
    {
        $this->typePresenters[] = $typePresenter;

        @usort($this->typePresenters, function ($presenter1, $presenter2) {
            return $presenter2->getPriority() - $presenter1->getPriority();
        });
    }

    
    public function presentValue($value): string
    {
        foreach ($this->typePresenters as $typePresenter) {
            if ($typePresenter->supports($value)) {
                return $typePresenter->present($value);
            }
        }

        return sprintf('[%s:%s]', strtolower(\gettype($value)), $value);
    }
}

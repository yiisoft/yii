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

namespace PhpSpec\Loader\Transformer;

use PhpSpec\Loader\SpecTransformer;
use PhpSpec\CodeAnalysis\TypeHintRewriter as TypeHintRewriterInterface;

final class TypeHintRewriter implements SpecTransformer
{
    /**
     * @var TypeHintRewriterInterface
     */
    private $rewriter;

    
    public function __construct(TypeHintRewriterInterface $rewriter)
    {
        $this->rewriter = $rewriter;
    }

    
    public function transform(string $spec): string
    {
        return $this->rewriter->rewrite($spec);
    }
}

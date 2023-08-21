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

namespace PhpSpec\Formatter\Presenter\Exception;

use PhpSpec\Formatter\Presenter\Differ\Differ;
use Prophecy\Argument\Token\ExactValueToken;
use Prophecy\Exception\Call\UnexpectedCallException;
use Prophecy\Prophecy\MethodProphecy;

class CallArgumentsPresenter
{
    /**
     * @var Differ
     */
    private $differ;

    
    public function __construct(Differ $differ)
    {
        $this->differ = $differ;
    }

    
    public function presentDifference(UnexpectedCallException $exception): string
    {
        $actualArguments = $exception->getArguments();
        $methodProphecies = $exception->getObjectProphecy()->getMethodProphecies($exception->getMethodName());

        if ($this->noMethodPropheciesForUnexpectedCall($methodProphecies)) {
            return '';
        }

        $presentedMethodProphecy = $this->findFirstUnexpectedArgumentsCallProphecy($methodProphecies, $exception);
        if (\is_null($presentedMethodProphecy)) {
            return '';
        }

        $expectedTokens = $presentedMethodProphecy->getArgumentsWildcard()->getTokens();
        if ($this->parametersCountMismatch($expectedTokens, $actualArguments)) {
            return '';
        }

        $expectedArguments = $this->convertArgumentTokensToDiffableValues($expectedTokens);
        $text = $this->generateArgumentsDifferenceText($actualArguments, $expectedArguments);

        return $text;
    }

    /**
     * @param MethodProphecy[] $methodProphecies
     */
    private function noMethodPropheciesForUnexpectedCall(array $methodProphecies): bool
    {
        return \count($methodProphecies) === 0;
    }

    /**
     * @param MethodProphecy[] $methodProphecies
     *
     * @return null|MethodProphecy
     */
    private function findFirstUnexpectedArgumentsCallProphecy(
        array $methodProphecies,
        UnexpectedCallException $exception
    ){
        $objectProphecy = $exception->getObjectProphecy();

        foreach ($methodProphecies as $methodProphecy) {
            $calls = $objectProphecy->findProphecyMethodCalls(
                $exception->getMethodName(),
                $methodProphecy->getArgumentsWildcard()
            );

            if (\count($calls)) {
                continue;
            }

            return $methodProphecy;
        }

        return null;
    }

    
    private function parametersCountMismatch(array $expectedTokens, array $actualArguments): bool
    {
        return \count($expectedTokens) !== \count($actualArguments);
    }

    
    private function convertArgumentTokensToDiffableValues(array $tokens): array
    {
        $values = array();
        foreach ($tokens as $token) {
            if ($token instanceof ExactValueToken) {
                $values[] = $token->getValue();
            } else {
                $values[] = (string)$token;
            }
        }

        return $values;
    }

    
    private function generateArgumentsDifferenceText(array $actualArguments, array $expectedArguments): string
    {
        $text = '';
        foreach($actualArguments as $i => $actualArgument) {
            $expectedArgument = $expectedArguments[$i];
            $actualArgument = \is_null($actualArgument) ? 'null' : $actualArgument;
            $expectedArgument = \is_null($expectedArgument) ? 'null' : $expectedArgument;

            $text .= $this->differ->compare($expectedArgument, $actualArgument);
        }

        return $text;
    }
}

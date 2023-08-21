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

namespace PhpSpec\CodeAnalysis;

use PhpSpec\Loader\Transformer\TypeHintIndex;

final class TokenizedTypeHintRewriter implements TypeHintRewriter
{
    const STATE_DEFAULT = 0;
    const STATE_READING_CLASS = 1;
    const STATE_READING_FUNCTION = 2;
    const STATE_READING_ARGUMENTS = 3;
    const STATE_READING_FUNCTION_BODY = 4;

    private $state = self::STATE_DEFAULT;

    private $currentClass;
    private $currentFunction;
    private $currentBodyLevel;

    private $typehintTokens = array(
        T_WHITESPACE, T_STRING, T_NS_SEPARATOR
    );

    /**
     * @var TypeHintIndex
     */
    private $typeHintIndex;
    /**
     * @var NamespaceResolver
     */
    private $namespaceResolver;

    
    public function __construct(TypeHintIndex $typeHintIndex, NamespaceResolver $namespaceResolver)
    {
        $this->typeHintIndex = $typeHintIndex;
        $this->namespaceResolver = $namespaceResolver;

        if (\PHP_VERSION_ID >= 80000) {
            $this->typehintTokens[] = T_NAME_FULLY_QUALIFIED;
            $this->typehintTokens[] = T_NAME_QUALIFIED;
        }

        if (\PHP_VERSION_ID >= 80100) {
            $this->typehintTokens[] = T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG;
        }
    }

    public function rewrite(string $classDefinition): string
    {
        $this->namespaceResolver->analyse($classDefinition);

        $this->reset();
        $tokens = $this->stripTypeHints(token_get_all($classDefinition));
        $tokensToString = $this->tokensToString($tokens);

        return $tokensToString;
    }

    private function reset(): void
    {
        $this->state = self::STATE_DEFAULT;
        $this->currentClass = '';
        $this->currentFunction = '';
    }

    private function stripTypeHints(array $tokens): array
    {
        foreach ($tokens as $index => $token) {
            if ($this->isToken($token, '{')) {
                $this->currentBodyLevel++;
            }
            elseif ($this->isToken($token, '}')) {
                $this->currentBodyLevel--;
            }

            switch ($this->state) {
                case self::STATE_READING_ARGUMENTS:
                    if (')' == $token) {
                        $this->state = self::STATE_READING_CLASS;
                    }
                    elseif ($this->tokenHasType($token, T_VARIABLE)) {
                        $this->extractTypehints($tokens, $index, $token);
                    }
                    break;
                case self::STATE_READING_FUNCTION:
                    if ('(' == $token) {
                        $this->state = self::STATE_READING_ARGUMENTS;
                    }
                    elseif ($this->tokenHasType($token, T_STRING) && !$this->currentFunction) {
                        $this->currentFunction = $token[1];
                    }
                    break;
                case self::STATE_READING_CLASS:
                    if ('{' == $token && $this->currentFunction) {
                        $this->state = self::STATE_READING_FUNCTION_BODY;
                        $this->currentBodyLevel = 1;
                    }
                    elseif ('}' == $token && $this->currentClass) {
                        $this->state = self::STATE_DEFAULT;
                        $this->currentClass = null;
                    }
                    elseif ($this->tokenHasType($token, T_STRING) && !$this->currentClass && $this->shouldExtractTokensOfClass($token[1])) {
                        $this->currentClass = $token[1];
                    }
                    elseif ($this->tokenHasType($token, T_FUNCTION) && $this->currentClass) {
                        $this->state = self::STATE_READING_FUNCTION;
                    }
                    break;
                case self::STATE_READING_FUNCTION_BODY:
                    if ('}' == $token && $this->currentBodyLevel === 0) {
                        $this->currentFunction = '';
                        $this->state = self::STATE_READING_CLASS;
                    }

                    break;
                default:
                    if ($this->tokenHasType($token, T_CLASS)) {
                        $this->state = self::STATE_READING_CLASS;
                    }
            }
        }

        return $tokens;
    }

    
    private function tokensToString(array $tokens): string
    {
        return join('', array_map(function ($token) {
            return \is_array($token) ? $token[1] : $token;
        }, $tokens));
    }

    private function extractTypehints(array &$tokens, int $index, array $token): void
    {
        $typehint = '';
        for ($i = $index - 1; !$this->haveNotReachedEndOfTypeHint($tokens[$i]); $i--) {
            $typehint = (is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i]) . $typehint;

            if (T_WHITESPACE !== $tokens[$i][0]) {
                unset($tokens[$i]);
            }
        }

        if ($typehint = trim($typehint)) {

            $class = $this->namespaceResolver->resolve($this->currentClass);

            if (\strpos($typehint, '|') !== false) {
                $this->typeHintIndex->addInvalid(
                    $class,
                    trim($this->currentFunction),
                    $token[1],
                    new DisallowedUnionTypehintException("Union type $typehint cannot be used to create a double")
                );

                return;
            }

            if (\strpos($typehint, '&') !== false) {
                $this->typeHintIndex->addInvalid(
                    $class,
                    trim($this->currentFunction),
                    $token[1],
                    new DisallowedUnionTypehintException("Intersection type $typehint cannot be used to create a double")
                );

                return;
            }

            try {
                $typehintFcqn = $this->namespaceResolver->resolve($typehint);
                $this->typeHintIndex->add(
                    $class,
                    trim($this->currentFunction),
                    $token[1],
                    $typehintFcqn
                );
            } catch (DisallowedNonObjectTypehintException $e) {
                $this->typeHintIndex->addInvalid(
                    $class,
                    trim($this->currentFunction),
                    $token[1],
                    $e
                );
            }
        }
    }

    private function haveNotReachedEndOfTypeHint($token) : bool
    {
        // PHP 8.1 returns the intersection token `&` as an array,
        // while previous versions return it as a string.
        if ($token == '|' || $token == '&' || (is_array($token) && $token[1] == '&')) {
            return false;
        }

        return !\in_array($token[0], $this->typehintTokens);
    }

    /**
     * @param array|string $token
     */
    private function tokenHasType($token, int $type): bool
    {
        return \is_array($token) && $type == $token[0];
    }

    private function shouldExtractTokensOfClass(string $className): bool
    {
        return substr($className, -4) == 'Spec';
    }

    /**
     * @param array|string $token
     */
    private function isToken($token, string $string): bool
    {
        return $token == $string || (\is_array($token) && $token[1] == $string);
    }
}

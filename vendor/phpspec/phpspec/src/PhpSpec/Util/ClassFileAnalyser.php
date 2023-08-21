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

namespace PhpSpec\Util;

use PhpSpec\Exception\Generator\NamedMethodNotFoundException;
use PhpSpec\Exception\Generator\NoMethodFoundInClass;

final class ClassFileAnalyser
{
    private $tokenLists = array();

    
    public function getStartLineOfFirstMethod(string $class): int
    {
        $tokens = $this->getTokensForClass($class);
        $index = $this->offsetForDocblock($tokens, $this->findIndexOfFirstMethod($tokens));
        return $tokens[$index][2];
    }

    
    public function getEndLineOfLastMethod(string $class): int
    {
        $tokens = $this->getTokensForClass($class);
        $index = $this->findEndOfLastMethod($tokens, $this->findIndexOfClassEnd($tokens));
        return $tokens[$index][2];
    }

    
    public function classHasMethods(string $class): bool
    {
        foreach ($this->getTokensForClass($class) as $token) {
            if (!\is_array($token)) {
                continue;
            }

            if ($token[0] === T_FUNCTION) {
                return true;
            }
        }

        return false;
    }

    
    public function getEndLineOfNamedMethod(string $class, string $methodName): int
    {
        $tokens = $this->getTokensForClass($class);

        $index = $this->findIndexOfNamedMethodEnd($tokens, $methodName);
        return $tokens[$index][2];
    }

    
    private function findIndexOfFirstMethod(array $tokens): int
    {
        for ($i = 0, $max = \count($tokens); $i < $max; $i++) {
            if ($this->tokenIsFunction($tokens[$i])) {
                return $i;
            }
        }

        throw new \RuntimeException('Could not find index of first method');
    }

    
    private function offsetForDocblock(array $tokens, int $index): int
    {
        $allowedTokens = array(
            T_FINAL,
            T_ABSTRACT,
            T_PUBLIC,
            T_PRIVATE,
            T_PROTECTED,
            T_STATIC,
            T_WHITESPACE
        );

        for ($i = $index - 1; $i >= 0; $i--) {
            $token = $tokens[$i];

            if (!\is_array($token)) {
                return $index;
            }

            if (\in_array($token[0], $allowedTokens)) {
                continue;
            }

            if ($token[0] === T_DOC_COMMENT) {
                return $i;
            }

            return $index;
        }

        throw new \RuntimeException('Could not find index of for docblock');
    }

    /**
     * @param $class
     */
    private function getTokensForClass($class): array
    {
        $hash = md5($class);

        if (!\in_array($hash, $this->tokenLists)) {
            $this->tokenLists[$hash] = token_get_all($class);
        }

        return $this->tokenLists[$hash];
    }

    
    private function findIndexOfNamedMethodEnd(array $tokens, string $methodName): int
    {
        $index = $this->findIndexOfNamedMethod($tokens, $methodName);
        return $this->findIndexOfMethodOrClassEnd($tokens, $index);
    }

    /**
     * @throws NamedMethodNotFoundException
     */
    private function findIndexOfNamedMethod(array $tokens, string $methodName): int
    {
        $searching = false;

        for ($i = 0, $max = \count($tokens); $i < $max; $i++) {
            $token = $tokens[$i];

            if (!\is_array($token)) {
                continue;
            }

            if ($token[0] === T_FUNCTION) {
                $searching = true;
            }

            if (!$searching) {
                continue;
            }

            if ($token[0] === T_STRING) {
                if ($token[1] === $methodName) {
                    return $i;
                }

                $searching = false;
            }
        }

        throw new NamedMethodNotFoundException('Target method not found');
    }

    
    private function findIndexOfMethodOrClassEnd(array $tokens, int $index): int
    {
        $braceCount = 0;

        for ($i = $index, $max = \count($tokens); $i < $max; $i++) {
            $token = $tokens[$i];

            if ('{' === $token || $this->isSpecialBraceToken($token)) {
                $braceCount++;
                continue;
            }

            if ('}' === $token) {
                $braceCount--;
                if ($braceCount === 0) {
                    return $i + 1;
                }
            }
        }

        throw new \RuntimeException('Could not find last method or class end');
    }

    private function isSpecialBraceToken($token): bool
    {
        if (!\is_array($token)) {
            return false;
        }

        return $token[1] === "{";
    }

    
    private function tokenIsFunction($token): bool
    {
        return \is_array($token) && $token[0] === T_FUNCTION;
    }

    
    private function findIndexOfClassEnd(array $tokens): int
    {
        $classTokens = array_filter($tokens, function ($token) {
            return \is_array($token) && $token[0] === T_CLASS;
        });
        $classTokenIndex = key($classTokens);
        return $this->findIndexOfMethodOrClassEnd($tokens, $classTokenIndex) - 1;
    }

    
    public function findEndOfLastMethod(array $tokens, int $index): int
    {
        for ($i = $index - 1; $i > 0; $i--) {
            if ($tokens[$i] == "}") {
                return $i + 1;
            }
        }
        throw new NoMethodFoundInClass();
    }
}

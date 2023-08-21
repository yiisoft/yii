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

use PhpSpec\Loader\StreamWrapper;

class MethodAnalyser
{
    
    public function methodIsEmpty(string $class, string $method): bool
    {
        return $this->reflectionMethodIsEmpty(new \ReflectionMethod($class, $method));
    }

    
    public function reflectionMethodIsEmpty(\ReflectionMethod $method): bool
    {
        if ($this->isNotImplementedInPhp($method)) {
            return false;
        }

        $code = $this->getCodeBody($method);
        $codeWithoutComments = $this->stripComments($code);

        return $this->codeIsOnlyBlocksAndWhitespace($codeWithoutComments);
    }

    
    public function getMethodOwnerName(string $class, string $method): string
    {
        $reflectionMethod = new \ReflectionMethod($class, $method);
        $startLine = $reflectionMethod->getStartLine();
        $endLine = $reflectionMethod->getEndLine();
        $reflectionClass  = $this->getMethodOwner($reflectionMethod, $startLine, $endLine);

        return $reflectionClass->getName();
    }

    
    private function getCodeBody(\ReflectionMethod $reflectionMethod): string
    {
        $endLine = $reflectionMethod->getEndLine();
        $startLine = $reflectionMethod->getStartLine();
        $reflectionClass = $this->getMethodOwner($reflectionMethod, $startLine, $endLine);

        $length = $endLine - $startLine;
        $lines = file(StreamWrapper::wrapPath($reflectionClass->getFileName()));
        $code = join(PHP_EOL, \array_slice($lines, $startLine - 1, $length + 1));

        return preg_replace('/.*function[^{]+{/s', '', $code);
    }

    
    private function getMethodOwner(\ReflectionMethod $reflectionMethod, int $methodStartLine, int $methodEndLine): \ReflectionClass
    {
        $reflectionClass = $reflectionMethod->getDeclaringClass();

        $fileName = $reflectionMethod->getFileName();
        $trait = $this->getDeclaringTrait($reflectionClass->getTraits(), $fileName, $methodStartLine, $methodEndLine);

        return $trait === null ? $reflectionClass : $trait;
    }

    /**
     * @param  \ReflectionClass[] $traits
     */
    private function getDeclaringTrait(array $traits, string $file, int $start, int $end): ?\ReflectionClass
    {
        foreach ($traits as $trait) {
            if ($trait->getFileName() == $file && $trait->getStartLine() <= $start && $trait->getEndLine() >= $end) {
                return $trait;
            }
            if (null !== ( $trait = $this->getDeclaringTrait($trait->getTraits(), $file, $start, $end) )) {
                return $trait;
            }
        }

        return null;
    }

    
    private function stripComments(string $code): string
    {
        $tokens = token_get_all('<?php ' . $code);

        $comments = array_map(
            function ($token) {
                return $token[1];
            },
            array_filter(
                $tokens,
                function ($token) {
                    return \is_array($token) && \in_array($token[0], array(T_COMMENT, T_DOC_COMMENT));
                })
        );

        $commentless = str_replace($comments, '', $code);

        return $commentless;
    }

    
    private function codeIsOnlyBlocksAndWhitespace(string $codeWithoutComments): bool
    {
        return (bool) preg_match('/^[\s{}]*$/s', $codeWithoutComments);
    }

    
    private function isNotImplementedInPhp(\ReflectionMethod $method): bool
    {
        $filename = $method->getDeclaringClass()->getFileName();

        if (false === $filename) {
            return true;
        }

        // HHVM <=3.2.0 does not return FALSE correctly
        if (preg_match('#^/([:/]systemlib.|/$)#', $filename)) {
            return true;
        }

        return false;
    }
}

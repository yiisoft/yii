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

namespace PhpSpec\CodeGenerator\Writer;

use PhpSpec\Exception\Generator\GenerationFailed;
use PhpSpec\Util\ClassFileAnalyser;

final class TokenizedCodeWriter implements CodeWriter
{
    /**
     * @var ClassFileAnalyser
     */
    private $analyser;

    
    public function __construct(ClassFileAnalyser $analyser)
    {
        $this->analyser = $analyser;
    }

    public function insertMethodFirstInClass(string $class, string $method): string
    {
        if (!$this->analyser->classHasMethods($class)) {
            return $this->writeAtEndOfClass($class, $method);
        }

        $line = $this->analyser->getStartLineOfFirstMethod($class);

        return $this->insertStringBeforeLine($class, $method, $line);
    }

    public function insertMethodLastInClass(string $class, string $method): string
    {
        if ($this->analyser->classHasMethods($class)) {
            $line = $this->analyser->getEndLineOfLastMethod($class);
            return $this->insertStringAfterLine($class, $method, $line);
        }

        return $this->writeAtEndOfClass($class, $method);
    }

    public function insertAfterMethod(string $class, string $methodName, string $method): string
    {
        $line = $this->analyser->getEndLineOfNamedMethod($class, $methodName);

        return $this->insertStringAfterLine($class, $method, $line);
    }

    private function insertStringAfterLine(string $target, string $toInsert, int $line, bool $leadingNewline = true): string
    {
        $lines = explode("\n", $target);
        $lastLines = \array_slice($lines, $line);
        $toInsert = trim($toInsert, "\n\r");
        if ($leadingNewline) {
            $toInsert = "\n" . $toInsert;
        }
        array_unshift($lastLines, $toInsert);
        array_splice($lines, $line, \count($lines), $lastLines);

        return implode("\n", $lines);
    }

    private function insertStringBeforeLine(string $target, string $toInsert, int $line): string
    {
        $line--;
        $lines = explode("\n", $target);
        $lastLines = \array_slice($lines, $line);
        array_unshift($lastLines, trim($toInsert, "\n\r") . "\n");
        array_splice($lines, $line, \count($lines), $lastLines);

        return implode("\n", $lines);
    }

    private function writeAtEndOfClass(string $class, string $method): string
    {
        $tokens = token_get_all($class);
        $searching = false;
        $inString = false;
        $searchPattern = array();

        for ($i = \count($tokens) - 1; $i >= 0; $i--) {
            $token = $tokens[$i];

            if ($token === '}' && !$inString) {
                $searching = true;
                continue;
            }

            if (!$searching) {
                continue;
            }

            if ($token === '"') {
                $inString = !$inString;
                continue;
            }

            if ($this->isWritePoint($token)) {
                $line = (int) $token[2];
                $prependNewLine = $token[0] === T_COMMENT || ($i != 0 && $tokens[$i-1][0] === T_COMMENT);
                return $this->insertStringAfterLine($class, $method, $line, $prependNewLine);
            }

            array_unshift($searchPattern, \is_array($token) ? $token[1] : $token);

            if ($token === '{') {
                $search = implode('', $searchPattern);
                $position = strpos($class, $search) + \strlen($search) - 1;

                return substr_replace($class, "\n" . $method . "\n", $position, 0);
            }
        }

        throw new GenerationFailed('Could not locate end of class');
    }

    /**
     * @param $token
     */
    private function isWritePoint($token): bool
    {
        return \is_array($token) && ($token[1] === "\n" || $token[0] === T_COMMENT);
    }
}

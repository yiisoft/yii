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

final class TokenizedNamespaceResolver implements NamespaceResolver
{
    const STATE_DEFAULT = 0;
    const STATE_READING_NAMESPACE = 1;
    const STATE_READING_USE = 2;
    const STATE_READING_USE_GROUP = 3;

    private $state = self::STATE_DEFAULT;

    private $currentNamespace;
    private $currentUseGroup;
    private $currentUse;
    private $uses = array();

    
    public function analyse(string $code): void
    {
        $this->state = self::STATE_DEFAULT;
        $this->currentUse = null;
        $this->currentUseGroup = null;
        $this->uses = array();

        $tokens = token_get_all($code);

        foreach ($tokens as $index => $token) {

            switch ($this->state) {
                case self::STATE_READING_NAMESPACE:
                    if (';' == $token) {
                        $this->currentNamespace = trim($this->currentNamespace);
                        $this->state = self::STATE_DEFAULT;
                    }
                    elseif (\is_array($token)) {
                        $this->currentNamespace .= $token[1];
                    }
                    break;
                case self::STATE_READING_USE_GROUP:
                    if ('}' == $token) {
                        $this->state = self::STATE_READING_USE;
                        $this->currentUseGroup = null;
                    }
                    elseif (',' == $token) {
                        $this->storeCurrentUse();
                    }
                    elseif (\is_array($token)) {
                        $this->currentUse = $this->currentUseGroup . trim($token[1]);
                    }
                    break;

                case self::STATE_READING_USE:
                    if (';' == $token) {
                        $this->storeCurrentUse();
                        $this->state = self::STATE_DEFAULT;
                    }
                    if ('{' == $token) {
                        $this->currentUseGroup = trim($this->currentUse);
                        $this->state = self::STATE_READING_USE_GROUP;
                    }
                    elseif (',' == $token) {
                        $this->storeCurrentUse();
                    }
                    elseif (\is_array($token)) {
                        $this->currentUse .= $token[1];
                    }
                    break;
                default:
                    if (\is_array($token) && T_NAMESPACE == $token[0]) {
                        $this->state = self::STATE_READING_NAMESPACE;
                        $this->currentNamespace = '';
                        $this->uses = array();
                    }
                    elseif (\is_array($token) && T_USE == $token[0]) {
                        $this->state = self::STATE_READING_USE;
                        $this->currentUse = '';
                    }

            }
        }
    }

    public function resolve(string $typeAlias): string
    {
        if (strpos($typeAlias, '\\') === 0) {
            return substr($typeAlias, 1);
        }
        if (($divider = strpos($typeAlias, '\\')) && array_key_exists(strtolower(substr($typeAlias, 0, $divider)), $this->uses)) {
            return $this->uses[strtolower(substr($typeAlias, 0, $divider))] . substr($typeAlias, $divider);
        }
        if (array_key_exists(strtolower($typeAlias), $this->uses)) {
            return $this->uses[strtolower($typeAlias)];
        }
        if ($this->currentNamespace) {
            return $this->currentNamespace . '\\' . $typeAlias;
        }

        return $typeAlias;
    }

    private function storeCurrentUse()
    {
        if (preg_match('/\s*(.*)\s+as\s+(.*)\s*/', $this->currentUse, $matches)) {
            $this->uses[strtolower(trim($matches[2]))] = trim($matches[1]);
        }
        elseif(preg_match('/\\\\([^\\\\]+)\s*$/', $this->currentUse, $matches)){
            $this->uses[strtolower($matches[1])] = trim($this->currentUse);
        }
        else {
            $this->uses[strtolower(trim($this->currentUse))] = trim($this->currentUse);
        }

        $this->currentUse = '';
    }
}

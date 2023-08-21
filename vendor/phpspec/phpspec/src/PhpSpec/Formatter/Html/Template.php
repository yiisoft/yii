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

namespace PhpSpec\Formatter\Html;

use PhpSpec\Formatter\Template as TemplateInterface;
use PhpSpec\IO\IO;

final class Template implements TemplateInterface
{
    const DIR = __DIR__;

    /**
     * @var IO
     */
    private $io;

    
    public function __construct(IO $io)
    {
        $this->io = $io;
    }

    
    public function render(string $text, array $templateVars = array()): void
    {
        if (file_exists($text)) {
            $text = file_get_contents($text);
        }
        $templateKeys = $this->extractKeys($templateVars);
        $output = str_replace($templateKeys, array_values($templateVars), $text);
        $this->io->write($output);
    }

    
    private function extractKeys(array $templateVars): array
    {
        return array_map(function ($e) {
            return '{'.$e.'}';
        }, array_keys($templateVars));
    }
}

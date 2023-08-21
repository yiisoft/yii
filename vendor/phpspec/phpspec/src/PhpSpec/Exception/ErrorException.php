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

namespace PhpSpec\Exception;

final class ErrorException extends \Exception
{
    public function __construct(\Error $error)
    {
        parent::__construct($error->getMessage(), $error->getCode(), $error);

        $this->line = $error->getLine();
        $this->file = $error->getFile();
    }
}

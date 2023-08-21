<?php

/*
 * This file is part of the JSON Lint package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seld\JsonLint;

class DuplicateKeyException extends ParsingException
{
    /**
     * @phpstan-ignore-next-line
     * @var array{key: string, line: int}
     */
    protected $details;

    /**
     * @param string $message
     * @param string $key
     * @phpstan-param array{line: int} $details
     */
    public function __construct($message, $key, array $details)
    {
        $details['key'] = $key;
        parent::__construct($message, $details);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->details['key'];
    }

    /**
     * @phpstan-return array{key: string, line: int}
     */
    public function getDetails()
    {
        return $this->details;
    }
}

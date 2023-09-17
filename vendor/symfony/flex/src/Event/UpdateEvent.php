<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Flex\Event;

use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class UpdateEvent extends Event
{
    private $force;
    private $reset;

    public function __construct(bool $force, bool $reset)
    {
        $this->name = ScriptEvents::POST_UPDATE_CMD;
        $this->force = $force;
        $this->reset = $reset;
    }

    public function force(): bool
    {
        return $this->force;
    }

    public function reset(): bool
    {
        return $this->reset;
    }
}

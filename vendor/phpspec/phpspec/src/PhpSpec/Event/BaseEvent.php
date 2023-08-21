<?php

namespace PhpSpec\Event;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event as OldEvent;
use Symfony\Contracts\EventDispatcher\Event as ContractEvent;

if (\is_subclass_of(EventDispatcher::class, EventDispatcherInterface::class)) {
    class BaseEvent extends ContractEvent
    {
    }
} else {
    class BaseEvent extends OldEvent
    {
    }
}

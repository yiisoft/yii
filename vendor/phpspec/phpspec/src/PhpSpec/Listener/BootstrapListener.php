<?php

namespace PhpSpec\Listener;

use PhpSpec\Console\ConsoleIO;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class BootstrapListener implements EventSubscriberInterface
{
    /**
     * @var ConsoleIO
     */
    private $io;

    public function __construct(ConsoleIO $io)
    {
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return array('beforeSuite' => array('beforeSuite', 1100));
    }

    public function beforeSuite(): void
    {
        if ($bootstrap = $this->io->getBootstrapPath()) {
            if (!is_file($bootstrap)) {
                throw new \RuntimeException(sprintf("Bootstrap file '%s' does not exist", $bootstrap));
            }

            require $bootstrap;
        }
    }
}

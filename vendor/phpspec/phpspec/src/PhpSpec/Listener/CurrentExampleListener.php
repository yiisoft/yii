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

namespace PhpSpec\Listener;

use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Message\CurrentExampleTracker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CurrentExampleListener implements EventSubscriberInterface {

    /**
     * @var CurrentExampleTracker
     */
    private $currentExample;

    public static function getSubscribedEvents()
    {
        return array(
            'beforeExample' => array('beforeCurrentExample', -20),
            'afterExample' => array('afterCurrentExample', -20),
            'afterSuite' => array('afterSuiteEvent', -20),
        );
    }

    public function __construct(CurrentExampleTracker $currentExample)
    {
        $this->currentExample = $currentExample;
    }

    public function beforeCurrentExample(ExampleEvent $event): void
    {
        $this->currentExample->setCurrentExample($event->getTitle());
    }

    public function afterCurrentExample(): void
    {
        $this->currentExample->setCurrentExample(null);
    }

    public function afterSuiteEvent(SuiteEvent $event): void
    {
        $this->currentExample->setCurrentExample('Exited with code: ' . $event->getResult());
    }
}

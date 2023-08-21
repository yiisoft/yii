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

use PhpSpec\Event\SuiteEvent;
use PhpSpec\Process\Prerequisites\PrerequisiteTester;
use PhpSpec\Process\ReRunner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RerunListener implements EventSubscriberInterface
{
    /**
     * @var ReRunner
     */
    private $reRunner;

    /**
     * @var PrerequisiteTester
     */
    private $suitePrerequisites;

    
    public function __construct(ReRunner $reRunner, PrerequisiteTester $suitePrerequisites)
    {
        $this->reRunner = $reRunner;
        $this->suitePrerequisites = $suitePrerequisites;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'beforeSuite' => array('beforeSuite', 1000),
            'afterSuite' => array('afterSuite', -1000)
        );
    }

    
    public function beforeSuite(SuiteEvent $suiteEvent): void
    {
        $this->suitePrerequisites->guardPrerequisites();
    }

    
    public function afterSuite(SuiteEvent $suiteEvent): void
    {
        if ($suiteEvent->isWorthRerunning()) {
            $this->reRunner->reRunSuite();
        }
    }
}

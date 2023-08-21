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

use PhpSpec\Event\ExampleEvent;
use PhpSpec\Formatter\Template as TemplateInterface;

class ReportPendingItem
{
    /**
     * @var TemplateInterface
     */
    private $template;
    /**
     * @var ExampleEvent
     */
    private $event;
    /**
     * @var int
     */
    private static $pendingExamplesCount = 1;

    
    public function __construct(TemplateInterface $template, ExampleEvent $event)
    {
        $this->template = $template;
        $this->event = $event;
    }

    public function write(): void
    {
        $this->template->render(Template::DIR.'/Template/ReportPending.html', array(
            'title' => $this->event->getTitle(),
            'pendingExamplesCount' => self::$pendingExamplesCount
        ));
        self::$pendingExamplesCount++;
    }
}

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

namespace PhpSpec\Formatter;

use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Event\SpecificationEvent;
use PhpSpec\Formatter\Presenter\Presenter;
use PhpSpec\IO\IO;
use PhpSpec\Listener\StatisticsCollector;

final class HtmlFormatter extends BasicFormatter
{
    /**
     * @var Html\ReportItemFactory
     */
    private $reportItemFactory;

    /**
     * @var int
     */
    private $index = 1;

    public function __construct(
        Html\ReportItemFactory $reportItemFactory,
        Presenter $presenter,
        IO $io,
        StatisticsCollector $stats
    ) {
        $this->reportItemFactory = $reportItemFactory;

        parent::__construct($presenter, $io, $stats);
    }


    public function beforeSuite(SuiteEvent $event)
    {
        include __DIR__."/Html/Template/ReportHeader.html";
    }


    public function beforeSpecification(SpecificationEvent $event)
    {
        $index = $this->index++;
        $name = $event->getTitle();
        include __DIR__."/Html/Template/ReportSpecificationStarts.html";
    }


    public function afterSpecification(SpecificationEvent $event)
    {
        include __DIR__."/Html/Template/ReportSpecificationEnds.html";
    }


    public function afterExample(ExampleEvent $event)
    {
        $reportLine = $this->reportItemFactory->create($event, $this->getPresenter());
        $reportLine->write($this->index - 1);
        $this->getIO()->write(PHP_EOL);
    }


    public function afterSuite(SuiteEvent $event)
    {
        include __DIR__."/Html/Template/ReportSummary.html";
        include __DIR__."/Html/Template/ReportFooter.html";
    }
}

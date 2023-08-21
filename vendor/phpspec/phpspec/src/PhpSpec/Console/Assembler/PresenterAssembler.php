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

namespace PhpSpec\Console\Assembler;

use PhpSpec\Formatter\Presenter\Differ\ArrayEngine;
use PhpSpec\Formatter\Presenter\Differ\Differ;
use PhpSpec\Formatter\Presenter\Differ\ObjectEngine;
use PhpSpec\Formatter\Presenter\Differ\StringEngine;
use PhpSpec\Formatter\Presenter\Exception\CallArgumentsPresenter;
use PhpSpec\Formatter\Presenter\Exception\GenericPhpSpecExceptionPresenter;
use PhpSpec\Formatter\Presenter\Exception\HtmlPhpSpecExceptionPresenter;
use PhpSpec\Formatter\Presenter\Exception\SimpleExceptionPresenter;
use PhpSpec\Formatter\Presenter\Exception\SimpleExceptionElementPresenter;
use PhpSpec\Formatter\Presenter\Exception\TaggingExceptionElementPresenter;
use PhpSpec\Formatter\Presenter\SimplePresenter;
use PhpSpec\Formatter\Presenter\TaggingPresenter;
use PhpSpec\Formatter\Presenter\Value\ArrayTypePresenter;
use PhpSpec\Formatter\Presenter\Value\BaseExceptionTypePresenter;
use PhpSpec\Formatter\Presenter\Value\BooleanTypePresenter;
use PhpSpec\Formatter\Presenter\Value\CallableTypePresenter;
use PhpSpec\Formatter\Presenter\Value\ComposedValuePresenter;
use PhpSpec\Formatter\Presenter\Value\NullTypePresenter;
use PhpSpec\Formatter\Presenter\Value\ObjectTypePresenter;
use PhpSpec\Formatter\Presenter\Value\QuotingStringTypePresenter;
use PhpSpec\Formatter\Presenter\Value\TruncatingStringTypePresenter;
use PhpSpec\ServiceContainer\IndexedServiceContainer;
use SebastianBergmann\Exporter\Exporter;

/**
 * @internal
 */
final class PresenterAssembler
{
    public function assemble(IndexedServiceContainer $container)
    {
        $this->assembleDiffer($container);
        $this->assembleDifferEngines($container);
        $this->assembleTypePresenters($container);
        $this->assemblePresenter($container);
        $this->assembleHtmlPresenter($container);
    }

    private function assembleDiffer(IndexedServiceContainer $container)
    {
        $container->define('formatter.presenter.differ', function (IndexedServiceContainer $c) {
            $differ = new Differ();

            array_map(
                array($differ, 'addEngine'),
                $c->getByTag('formatter.presenter.differ.engines')
            );

            return $differ;
        });
    }

    private function assembleDifferEngines(IndexedServiceContainer $container)
    {
        $container->define('formatter.presenter.differ.engines.string', function () {
            return new StringEngine();
        }, ['formatter.presenter.differ.engines']);

        $container->define('formatter.presenter.differ.engines.array', function () {
            return new ArrayEngine(new Exporter());
        }, ['formatter.presenter.differ.engines']);

        $container->define('formatter.presenter.differ.engines.object', function (IndexedServiceContainer $c) {
            return new ObjectEngine(
                new Exporter(),
                $c->get('formatter.presenter.differ.engines.string')
            );
        }, ['formatter.presenter.differ.engines']);
    }

    private function assembleTypePresenters(IndexedServiceContainer $container)
    {
        $container->define('formatter.presenter.value.array_type_presenter', function () {
            return new ArrayTypePresenter();
        }, ['formatter.presenter.value']);

        $container->define('formatter.presenter.value.boolean_type_presenter', function () {
            return new BooleanTypePresenter();
        }, ['formatter.presenter.value']);

        $container->define('formatter.presenter.value.callable_type_presenter', function (IndexedServiceContainer $c) {
            return new CallableTypePresenter($c->get('formatter.presenter'));
        }, ['formatter.presenter.value']);

        $container->define('formatter.presenter.value.exception_type_presenter', function () {
            return new BaseExceptionTypePresenter();
        }, ['formatter.presenter.value']);

        $container->define('formatter.presenter.value.null_type_presenter', function () {
            return new NullTypePresenter();
        }, ['formatter.presenter.value']);

        $container->define('formatter.presenter.value.object_type_presenter', function () {
            return new ObjectTypePresenter();
        }, ['formatter.presenter.value']);

        $container->define('formatter.presenter.value.string_type_presenter', function () {
            return new TruncatingStringTypePresenter(new QuotingStringTypePresenter());
        }, ['formatter.presenter.value']);

        $container->addConfigurator(function (IndexedServiceContainer $c) {
            array_map(
                array($c->get('formatter.presenter.value_presenter'), 'addTypePresenter'),
                $c->getByTag('formatter.presenter.value')
            );
        });
    }

    private function assemblePresenter(IndexedServiceContainer $container)
    {
        $container->define('formatter.presenter', function (IndexedServiceContainer $c) {
            return new TaggingPresenter(
                new SimplePresenter(
                    $c->get('formatter.presenter.value_presenter'),
                    new SimpleExceptionPresenter(
                        $c->get('formatter.presenter.differ'),
                        $c->get('formatter.presenter.exception_element_presenter'),
                        new CallArgumentsPresenter($c->get('formatter.presenter.differ')),
                        $c->get('formatter.presenter.exception.phpspec')
                    )
                )
            );
        });

        $container->define('formatter.presenter.value_presenter', function () {
            return new ComposedValuePresenter();
        });

        $container->define('formatter.presenter.exception_element_presenter', function (IndexedServiceContainer $c) {
            return new TaggingExceptionElementPresenter(
                $c->get('formatter.presenter.value.exception_type_presenter'),
                $c->get('formatter.presenter.value_presenter')
            );
        });

        $container->define('formatter.presenter.exception.phpspec', function (IndexedServiceContainer $c) {
            return new GenericPhpSpecExceptionPresenter(
                $c->get('formatter.presenter.exception_element_presenter')
            );
        });
    }

    private function assembleHtmlPresenter(IndexedServiceContainer $container)
    {
        $container->define('formatter.presenter.html', function (IndexedServiceContainer $c) {
            return new SimplePresenter(
                $c->get('formatter.presenter.value_presenter'),
                new SimpleExceptionPresenter(
                    $c->get('formatter.presenter.differ'),
                    $c->get('formatter.presenter.html.exception_element_presenter'),
                    new CallArgumentsPresenter($c->get('formatter.presenter.differ')),
                    $c->get('formatter.presenter.html.exception.phpspec')
                )
            );
        });

        $container->define('formatter.presenter.html.exception_element_presenter', function (IndexedServiceContainer $c) {
            return new SimpleExceptionElementPresenter(
                $c->get('formatter.presenter.value.exception_type_presenter'),
                $c->get('formatter.presenter.value_presenter')
            );
        });

        $container->define('formatter.presenter.html.exception.phpspec', function () {
            return new HtmlPhpSpecExceptionPresenter();
        });
    }
}

<?php

declare(strict_types=1);

namespace yii1\tests\framework\web\PsrMessage;

use CTestCase;
use Psr\Http\Message\ResponseInterface;
use TestApplication;

class PsrMessageTest extends CTestCase
{
    /**
     * @test
     */
    public function it_should_return_psr_message(): void
    {
        $app = new TestApplication([
            'catchAllRequest' => ['filters'],
            'controllerMap' => [
                'filters' => ['class' => ControllerWithFilters::class],
            ],
        ]);
        self::assertInstanceOf(ResponseInterface::class, $app->run());

        $app = new TestApplication([
            'catchAllRequest' => ['without'],
            'controllerMap' => [
                'without' => ['class' => ControllerWithoutFilters::class],
            ],
        ]);
        self::assertInstanceOf(ResponseInterface::class, $app->run());
    }
}

<?php

declare(strict_types=1);

namespace yii1\tests\framework\console;

use CConsoleCommandRunner;
use CException;
use CTestCase;
use GlobalNamespacedCommand;
use yii1\tests\framework\console\commands\NamespacedCommand;

/**
 * @covers \CConsoleCommandRunner
 */
class CConsoleCommandRunnerTest extends CTestCase
{
    /**
     * @test
     */
    public function it_should_support_command_namespace(): void
    {
        $sut = new CConsoleCommandRunner();
        $sut->addCommands(__DIR__ . '/commands');

        ob_start();
        $sut->run(['', 'GlobalNamespaced']);
        static::assertEquals(GlobalNamespacedCommand::class, ob_get_clean());

        try {
            $sut->run(['', 'Namespaced']);
            static::fail('This should not happen');
        } catch (CException $exception) {
            static::assertEquals('Alias "" is invalid. Make sure it points to an existing directory or file.', $exception->getMessage());
        }

        $sut->commandNamespace = __NAMESPACE__ . '\\commands';
        ob_start();
        $sut->run(['', 'Namespaced']);
        static::assertEquals(NamespacedCommand::class, ob_get_clean());

        // проверить ещё раз запуск GlobalNamespaced нельзя, так как будет сделана попытка переопределения класса
        // и выброшен fatal error. В общем, команда не запустится :)
    }
}

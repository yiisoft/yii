<?php

declare(strict_types=1);

namespace Phing\PhingComposerConfigurator;

use Composer\Composer;
use Composer\Config;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

/**
 * Class ExtensionInstallerPluginTest
 * @package Phing\PhingComposerConfigurator
 * @covers \Phing\PhingComposerConfigurator\ExtensionInstallerPlugin
 */
final class ExtensionInstallerPluginTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     */
    public function testActivate(): void
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->exactly(3))
            ->method('get')
            ->willReturn(null);

        $installationManager = $this->createMock(InstallationManager::class);
        $installationManager->expects($this->once())
            ->method('addInstaller')
            ->with(new IsInstanceOf(ExtensionInstaller::class));

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->once())
            ->method('getInstallationManager')
            ->willReturn($installationManager);
        $composer->expects($this->exactly(3))
            ->method('getConfig')
            ->willReturn($config);

        $io = $this->createMock(IOInterface::class);
        $io->expects($this->never())
            ->method('writeError');
        $io->expects($this->never())
            ->method('write');

        $object = new ExtensionInstallerPlugin();

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object->activate($composer, $io);
    }
}

<?php

declare(strict_types=1);

namespace Phing\PhingComposerConfigurator;

use Composer\Composer;
use Composer\Config;
use Composer\Downloader\DownloadManager;
use Composer\Installer\BinaryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * Class ExtensionInstallerTest
 * @package Phing\PhingComposerConfigurator
 * @covers \Phing\PhingComposerConfigurator\ExtensionInstaller
 */
final class ExtensionInstallerTest extends TestCase
{
    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testSupports(): void
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->exactly(3))
            ->method('get')
            ->willReturn(null);

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->exactly(3))
            ->method('getConfig')
            ->willReturn($config);

        $io = $this->createMock(IOInterface::class);
        $io->expects($this->never())
            ->method('writeError');
        $io->expects($this->never())
            ->method('write');

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object = new ExtensionInstaller(
            $io,
            $composer
        );

        $this->assertTrue($object->supports(ExtensionInstaller::EXTENSTION_NAME));
        $this->assertFalse($object->supports('library'));
    }

    /**
     * @throws \RuntimeException
     */
    public function testInstallNoExtraConfigured(): void
    {
        $vendor     = 'vendor';
        $prettyName = 'test/test-name';
        $targetDir  = null;
        $extra      = [];

        $config = $this->createMock(Config::class);
        $config->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['vendor-dir'], [], [])
            ->willReturnOnConsecutiveCalls($vendor, '', '');

        $package = $this->createMock(PackageInterface::class);
        $package->expects($this->exactly(3))
            ->method('getPrettyName')
            ->willReturn($prettyName);
        $package->expects($this->exactly(3))
            ->method('getTargetDir')
            ->willReturn($targetDir);
        $package->expects($this->once())
            ->method('getExtra')
            ->willReturn($extra);

        $getDownloadManager = $this->createMock(DownloadManager::class);

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->exactly(3))
            ->method('getConfig')
            ->willReturn($config);
        $composer->expects($this->once())
            ->method('getDownloadManager')
            ->willReturn($getDownloadManager);

        $io = $this->createMock(IOInterface::class);
        $io->expects($this->never())
            ->method('writeError');
        $io->expects($this->never())
            ->method('write');

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object = new ExtensionInstaller(
            $io,
            $composer
        );

        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $repo->expects($this->exactly(2))
            ->method('hasPackage')
            ->with($package)
            ->willReturn(false);

        $object->install($repo, $package);
    }

    /**
     * @throws \RuntimeException
     * @throws InvalidArgumentException
     */
    public function testInstallATaskOnce(): void
    {
        $vendor     = 'vendor';
        $prettyName = 'test/test-name';
        $targetDir  = null;
        $filename   = 'custom.task.properties';
        $taskClass  = 'Phing\Tasks\Ext\ApiGenTask';

        $root = vfsStream::setup(
            'root',
            null,
            [
                (new vfsStreamFile($filename, 0777))->setContent(''),
            ]
        );

        $root->url();
        $uri        = $root->getChild($filename)->url();
        $extra      = ['phing-custom-taskdefs' => ['apigen' => $taskClass]];

        $config = $this->createMock(Config::class);
        $config->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['vendor-dir'], [], [])
            ->willReturnOnConsecutiveCalls($vendor, '', '');

        $package = $this->createMock(PackageInterface::class);
        $package->expects($this->exactly(3))
            ->method('getPrettyName')
            ->willReturn($prettyName);
        $package->expects($this->exactly(3))
            ->method('getTargetDir')
            ->willReturn($targetDir);
        $package->expects($this->once())
            ->method('getExtra')
            ->willReturn($extra);

        $getDownloadManager = $this->createMock(DownloadManager::class);

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->exactly(3))
            ->method('getConfig')
            ->willReturn($config);
        $composer->expects($this->once())
            ->method('getDownloadManager')
            ->willReturn($getDownloadManager);

        $io = $this->createMock(IOInterface::class);
        $io->expects($this->never())
            ->method('writeError');
        $io->expects($this->once())
            ->method('write')
            ->with('  - Installing new custom phing vfs://root/custom.task.properties <apigen>.');

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object = new ExtensionInstaller(
            $io,
            $composer
        );
        $object->setTaskFile($uri);

        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $repo->expects($this->exactly(2))
            ->method('hasPackage')
            ->with($package)
            ->willReturn(false);

        $object->install($repo, $package);

        $content = file_get_contents($uri);

        $this->assertSame('apigen=' . $taskClass . PHP_EOL, $content);
    }

    /**
     * @throws \RuntimeException
     * @throws InvalidArgumentException
     */
    public function testInstallATaskTwice(): void
    {
        $vendor     = 'vendor';
        $prettyName = 'test/test-name';
        $targetDir  = null;
        $filename   = 'custom.task.properties';
        $taskClass  = 'Phing\Tasks\Ext\ApiGenTask';

        $root = vfsStream::setup(
            'root',
            null,
            [
                (new vfsStreamFile($filename, 0777))->setContent(''),
            ]
        );

        $root->url();
        $uri        = $root->getChild($filename)->url();
        $extra      = ['phing-custom-taskdefs' => ['apigen' => $taskClass]];

        $config = $this->createMock(Config::class);
        $config->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['vendor-dir'], [], [])
            ->willReturnOnConsecutiveCalls($vendor, '', '');

        $package = $this->createMock(PackageInterface::class);
        $package->expects($this->exactly(6))
            ->method('getPrettyName')
            ->willReturn($prettyName);
        $package->expects($this->exactly(6))
            ->method('getTargetDir')
            ->willReturn($targetDir);
        $package->expects($this->exactly(2))
            ->method('getExtra')
            ->willReturn($extra);

        $getDownloadManager = $this->createMock(DownloadManager::class);

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->exactly(3))
            ->method('getConfig')
            ->willReturn($config);
        $composer->expects($this->once())
            ->method('getDownloadManager')
            ->willReturn($getDownloadManager);

        $io = $this->createMock(IOInterface::class);
        $io->expects($this->never())
            ->method('writeError');
        $io->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(['  - Installing new custom phing vfs://root/custom.task.properties <apigen>.'], ['  - <warning>custom phing vfs://root/custom.task.properties <apigen> was already installed.</warning>']);

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object = new ExtensionInstaller(
            $io,
            $composer
        );
        $object->setTaskFile($uri);

        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $repo->expects($this->exactly(4))
            ->method('hasPackage')
            ->with($package)
            ->willReturn(false);

        $object->install($repo, $package);
        $object->install($repo, $package);

        $content = file_get_contents($uri);

        $this->assertSame('apigen=' . $taskClass . PHP_EOL, $content);
    }

    /**
     * @throws RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testUpdateNoExtraConfigured(): void
    {
        $vendor     = 'vendor';
        $prettyName = 'test/test-name';
        $targetDir  = null;
        $extra      = [];
        $binaries   = [];

        $config = $this->createMock(Config::class);
        $config->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive(['vendor-dir'], [], [])
            ->willReturnOnConsecutiveCalls($vendor, '', '');

        $initial = $this->createMock(PackageInterface::class);
        $initial->expects($this->once())
            ->method('getPrettyName')
            ->willReturn($prettyName);
        $initial->expects($this->once())
            ->method('getTargetDir')
            ->willReturn($targetDir);
        $initial->expects($this->once())
            ->method('getExtra')
            ->willReturn($extra);
        $initial->expects($this->never())
            ->method('getBinaries')
            ->willReturn($binaries);

        $target = $this->createMock(PackageInterface::class);
        $target->expects($this->exactly(2))
            ->method('getPrettyName')
            ->willReturn($prettyName);
        $target->expects($this->exactly(2))
            ->method('getTargetDir')
            ->willReturn($targetDir);
        $target->expects($this->once())
            ->method('getExtra')
            ->willReturn($extra);
        $target->expects($this->never())
            ->method('getBinaries')
            ->willReturn($binaries);

        $getDownloadManager = $this->createMock(DownloadManager::class);

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);
        $composer->expects($this->once())
            ->method('getDownloadManager')
            ->willReturn($getDownloadManager);

        $io = $this->createMock(IOInterface::class);
        $io->expects($this->never())
            ->method('writeError');
        $io->expects($this->never())
            ->method('write');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->exactly(4))
            ->method('ensureDirectoryExists');

        $binaryInstaller = $this->createMock(BinaryInstaller::class);
        $binaryInstaller->expects($this->once())
            ->method('removeBinaries')
            ->with($initial);
        $binaryInstaller->expects($this->once())
            ->method('installBinaries')
            ->with($target, realpath($vendor) . '/' . $prettyName);

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object = new ExtensionInstaller(
            $io,
            $composer,
            'library',
            $filesystem,
            $binaryInstaller
        );

        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $repo->expects($this->exactly(2))
            ->method('hasPackage')
            ->with($initial)
            ->willReturn(true);

        $object->update($repo, $initial, $target);
    }

    /**
     * @throws RuntimeException
     * @throws ExpectationFailedException
     * @throws \InvalidArgumentException
     */
    public function testUpdateATaskOnce(): void
    {
        $filename   = 'custom.task.properties';
        $taskClassI = 'Phing\Tasks\Ext\ApiGenTask';
        $taskClassT = 'Phing\Tasks\Ext\ApiGenTask2';

        $root = vfsStream::setup(
            'root',
            null,
            [
                (new vfsStreamFile($filename, 0777))->setContent('apigen=' . $taskClassI . PHP_EOL),
            ]
        );

        $vendor      = 'vendor';
        $prettyNameI = 'test/test-name';
        $prettyNameT = 'test/test-name2';
        $targetDir   = null;
        $uri         = $root->getChild($filename)->url();
        $extraI      = ['phing-custom-taskdefs' => ['apigen' => $taskClassI]];
        $extraT      = ['phing-custom-taskdefs' => ['apigen' => $taskClassT]];
        $binaries    = [];

        $config = $this->createMock(Config::class);
        $config->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive(['vendor-dir'], [], [])
            ->willReturnOnConsecutiveCalls($vendor, '', '');

        $initial = $this->createMock(PackageInterface::class);
        $initial->expects($this->exactly(2))
            ->method('getPrettyName')
            ->willReturn($prettyNameI);
        $initial->expects($this->exactly(3))
            ->method('getTargetDir')
            ->willReturn($targetDir);
        $initial->expects($this->once())
            ->method('getExtra')
            ->willReturn($extraI);
        $initial->expects($this->never())
            ->method('getBinaries')
            ->willReturn($binaries);

        $target = $this->createMock(PackageInterface::class);
        $target->expects($this->exactly(3))
            ->method('getPrettyName')
            ->willReturn($prettyNameT);
        $target->expects($this->exactly(3))
            ->method('getTargetDir')
            ->willReturn($targetDir);
        $target->expects($this->once())
            ->method('getExtra')
            ->willReturn($extraT);
        $target->expects($this->never())
            ->method('getBinaries')
            ->willReturn($binaries);

        $getDownloadManager = $this->createMock(DownloadManager::class);

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);
        $composer->expects($this->once())
            ->method('getDownloadManager')
            ->willReturn($getDownloadManager);

        $io = $this->createMock(IOInterface::class);
        $io->expects($this->never())
            ->method('writeError');
        $io->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                ['  - Removing custom phing vfs://root/custom.task.properties <apigen>.', true, 2],
                ['  - Installing new custom phing vfs://root/custom.task.properties <apigen>.', true, 2]
            );

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->exactly(6))
            ->method('ensureDirectoryExists');

        $binaryInstaller = $this->createMock(BinaryInstaller::class);
        $binaryInstaller->expects($this->once())
            ->method('removeBinaries')
            ->with($initial);
        $binaryInstaller->expects($this->once())
            ->method('installBinaries')
            ->with($target, realpath($vendor) . '/' . $prettyNameT);

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object = new ExtensionInstaller(
            $io,
            $composer,
            'library',
            $filesystem,
            $binaryInstaller
        );
        $object->setTaskFile($uri);

        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $repo->expects($this->exactly(2))
            ->method('hasPackage')
            ->with($initial)
            ->willReturn(true);

        $object->update($repo, $initial, $target);

        $content = file_get_contents($uri);

        $this->assertSame('apigen=' . $taskClassT . PHP_EOL, $content);
    }

    /**
     * @throws RuntimeException
     * @throws ExpectationFailedException
     * @throws \InvalidArgumentException
     */
    public function testUpdateATaskTwice(): void
    {
        $filename   = 'custom.task.properties';
        $taskClassI = 'Phing\Tasks\Ext\ApiGenTask';
        $taskClassT = 'Phing\Tasks\Ext\ApiGenTask2';

        $root = vfsStream::setup(
            'root',
            null,
            [
                (new vfsStreamFile($filename, 0777))->setContent('apigen=' . $taskClassI . PHP_EOL),
            ]
        );

        $vendor      = 'vendor';
        $prettyNameI = 'test/test-name';
        $prettyNameT = 'test/test-name2';
        $targetDir   = null;
        $uri         = $root->getChild($filename)->url();
        $extraI      = ['phing-custom-taskdefs' => ['apigen' => $taskClassI]];
        $extraT      = ['phing-custom-taskdefs' => ['apigen' => $taskClassT]];
        $binaries    = [];

        $config = $this->createMock(Config::class);
        $config->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive(['vendor-dir'], [], [])
            ->willReturnOnConsecutiveCalls($vendor, '', '');

        $initial = $this->createMock(PackageInterface::class);
        $initial->expects($this->exactly(4))
            ->method('getPrettyName')
            ->willReturn($prettyNameI);
        $initial->expects($this->exactly(6))
            ->method('getTargetDir')
            ->willReturn($targetDir);
        $initial->expects($this->exactly(2))
            ->method('getExtra')
            ->willReturn($extraI);
        $initial->expects($this->never())
            ->method('getBinaries')
            ->willReturn($binaries);

        $target = $this->createMock(PackageInterface::class);
        $target->expects($this->exactly(6))
            ->method('getPrettyName')
            ->willReturn($prettyNameT);
        $target->expects($this->exactly(6))
            ->method('getTargetDir')
            ->willReturn($targetDir);
        $target->expects($this->exactly(2))
            ->method('getExtra')
            ->willReturn($extraT);
        $target->expects($this->never())
            ->method('getBinaries')
            ->willReturn($binaries);

        $getDownloadManager = $this->createMock(DownloadManager::class);

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);
        $composer->expects($this->once())
            ->method('getDownloadManager')
            ->willReturn($getDownloadManager);

        $io = $this->createMock(IOInterface::class);
        $io->expects($this->never())
            ->method('writeError');
        $io->expects($this->exactly(4))
            ->method('write')
            ->withConsecutive(
                ['  - Removing custom phing vfs://root/custom.task.properties <apigen>.', true, 2],
                ['  - Installing new custom phing vfs://root/custom.task.properties <apigen>.', true, 2],
                ['  - <warning>custom phing vfs://root/custom.task.properties <apigen> is not installed.</warning>', true, 2],
                ['  - <warning>custom phing vfs://root/custom.task.properties <apigen> was already installed.</warning>', true, 2]
            );

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->exactly(12))
            ->method('ensureDirectoryExists');

        $binaryInstaller = $this->createMock(BinaryInstaller::class);
        $binaryInstaller->expects($this->exactly(2))
            ->method('removeBinaries')
            ->with($initial);
        $binaryInstaller->expects($this->exactly(2))
            ->method('installBinaries')
            ->with($target, realpath($vendor) . '/' . $prettyNameT);

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object = new ExtensionInstaller(
            $io,
            $composer,
            'library',
            $filesystem,
            $binaryInstaller
        );
        $object->setTaskFile($uri);

        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $repo->expects($this->exactly(4))
            ->method('hasPackage')
            ->with($initial)
            ->willReturn(true);

        $object->update($repo, $initial, $target);
        $object->update($repo, $initial, $target);

        $content = file_get_contents($uri);

        $this->assertSame('apigen=' . $taskClassT . PHP_EOL, $content);
    }

    /**
     * @throws RuntimeException
     */
    public function testUninstallNoExtraConfigured(): void
    {
        $vendor     = 'vendor';
        $prettyName = 'test/test-name';
        $targetDir  = null;
        $extra      = [];

        $config = $this->createMock(Config::class);
        $config->expects($this->once())
            ->method('get')
            ->withConsecutive(['vendor-dir'], [], [])
            ->willReturnOnConsecutiveCalls($vendor, '', '');

        $package = $this->createMock(PackageInterface::class);
        $package->expects($this->exactly(2))
            ->method('getPrettyName')
            ->willReturn($prettyName);
        $package->expects($this->exactly(4))
            ->method('getTargetDir')
            ->willReturn($targetDir);
        $package->expects($this->once())
            ->method('getExtra')
            ->willReturn($extra);

        $getDownloadManager = $this->createMock(DownloadManager::class);

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);
        $composer->expects($this->once())
            ->method('getDownloadManager')
            ->willReturn($getDownloadManager);

        $io = $this->createMock(IOInterface::class);
        $io->expects($this->never())
            ->method('writeError');
        $io->expects($this->never())
            ->method('write');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->exactly(2))
            ->method('ensureDirectoryExists');

        $binaryInstaller = $this->createMock(BinaryInstaller::class);
        $binaryInstaller->expects($this->once())
            ->method('removeBinaries')
            ->with($package);
        $binaryInstaller->expects($this->never())
            ->method('installBinaries')
            ->with($package, realpath($vendor) . '/' . $prettyName);

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object = new ExtensionInstaller(
            $io,
            $composer,
            'library',
            $filesystem,
            $binaryInstaller
        );

        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('hasPackage')
            ->with($package)
            ->willReturn(true);

        $object->uninstall($repo, $package);
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public function testUninstallATaskOnce(): void
    {
        $filename   = 'custom.type.properties';
        $taskClass  = 'Phing\Tasks\Ext\ApiGenTask';

        $root = vfsStream::setup(
            'root',
            null,
            [
                (new vfsStreamFile($filename, 0777))->setContent('apigen=' . $taskClass . PHP_EOL),
            ]
        );

        $vendor     = 'vendor';
        $prettyName = 'test/test-name';
        $targetDir  = null;
        $uri        = $root->getChild($filename)->url();
        $extra      = ['phing-custom-typedefs' => ['apigen' => $taskClass]];

        $config = $this->createMock(Config::class);
        $config->expects($this->once())
            ->method('get')
            ->withConsecutive(['vendor-dir'], [], [])
            ->willReturnOnConsecutiveCalls($vendor, '', '');

        $package = $this->createMock(PackageInterface::class);
        $package->expects($this->exactly(2))
            ->method('getPrettyName')
            ->willReturn($prettyName);
        $package->expects($this->exactly(4))
            ->method('getTargetDir')
            ->willReturn($targetDir);
        $package->expects($this->once())
            ->method('getExtra')
            ->willReturn($extra);

        $getDownloadManager = $this->createMock(DownloadManager::class);

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);
        $composer->expects($this->once())
            ->method('getDownloadManager')
            ->willReturn($getDownloadManager);

        $io = $this->createMock(IOInterface::class);
        $io->expects($this->never())
            ->method('writeError');
        $io->expects($this->once())
            ->method('write')
            ->with('  - Removing custom phing vfs://root/custom.type.properties <apigen>.', true, 2);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->exactly(2))
            ->method('ensureDirectoryExists');

        $binaryInstaller = $this->createMock(BinaryInstaller::class);
        $binaryInstaller->expects($this->once())
            ->method('removeBinaries')
            ->with($package);
        $binaryInstaller->expects($this->never())
            ->method('installBinaries')
            ->with($package, realpath($vendor) . '/' . $prettyName);

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object = new ExtensionInstaller(
            $io,
            $composer,
            'library',
            $filesystem,
            $binaryInstaller
        );
        $object->setTypeFile($uri);

        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('hasPackage')
            ->with($package)
            ->willReturn(true);

        $object->uninstall($repo, $package);

        $content = file_get_contents($uri);

        $this->assertSame('', $content);
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public function testUninstallATaskTwice(): void
    {
        $filename   = 'custom.type.properties';
        $taskClass  = 'Phing\Tasks\Ext\ApiGenTask';

        $root = vfsStream::setup(
            'root',
            null,
            [
                (new vfsStreamFile($filename, 0777))->setContent('apigen=' . $taskClass . PHP_EOL),
            ]
        );

        $vendor     = 'vendor';
        $prettyName = 'test/test-name';
        $targetDir  = null;
        $uri        = $root->getChild($filename)->url();
        $extra      = ['phing-custom-typedefs' => ['apigen' => $taskClass]];

        $config = $this->createMock(Config::class);
        $config->expects($this->once())
            ->method('get')
            ->withConsecutive(['vendor-dir'], [], [])
            ->willReturnOnConsecutiveCalls($vendor, '', '');

        $package = $this->createMock(PackageInterface::class);
        $package->expects($this->exactly(4))
            ->method('getPrettyName')
            ->willReturn($prettyName);
        $package->expects($this->exactly(8))
            ->method('getTargetDir')
            ->willReturn($targetDir);
        $package->expects($this->exactly(2))
            ->method('getExtra')
            ->willReturn($extra);

        $getDownloadManager = $this->createMock(DownloadManager::class);

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);
        $composer->expects($this->once())
            ->method('getDownloadManager')
            ->willReturn($getDownloadManager);

        $io = $this->createMock(IOInterface::class);
        $io->expects($this->never())
            ->method('writeError');
        $io->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                ['  - Removing custom phing vfs://root/custom.type.properties <apigen>.', true, 2],
                ['  - <warning>custom phing vfs://root/custom.type.properties <apigen> is not installed.</warning>', true, 2]
            );

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->exactly(4))
            ->method('ensureDirectoryExists');

        $binaryInstaller = $this->createMock(BinaryInstaller::class);
        $binaryInstaller->expects($this->exactly(2))
            ->method('removeBinaries')
            ->with($package);
        $binaryInstaller->expects($this->never())
            ->method('installBinaries')
            ->with($package, realpath($vendor) . '/' . $prettyName);

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object = new ExtensionInstaller(
            $io,
            $composer,
            'library',
            $filesystem,
            $binaryInstaller
        );
        $object->setTypeFile($uri);

        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $repo->expects($this->exactly(2))
            ->method('hasPackage')
            ->with($package)
            ->willReturn(true);

        $object->uninstall($repo, $package);
        $object->uninstall($repo, $package);

        $content = file_get_contents($uri);

        $this->assertSame('', $content);
    }
}

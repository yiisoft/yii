<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Flex;

use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Intervals;
use Composer\Semver\VersionParser;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class PackageFilter
{
    private $versions;
    private $versionParser;
    private $symfonyRequire;
    private $symfonyConstraints;
    private $downloader;
    private $io;

    public function __construct(IOInterface $io, string $symfonyRequire, Downloader $downloader)
    {
        $this->versionParser = new VersionParser();
        $this->symfonyRequire = $symfonyRequire;
        $this->symfonyConstraints = $this->versionParser->parseConstraints($symfonyRequire);
        $this->downloader = $downloader;
        $this->io = $io;
    }

    /**
     * @param PackageInterface[] $data
     * @param PackageInterface[] $lockedPackages
     *
     * @return PackageInterface[]
     */
    public function removeLegacyPackages(array $data, RootPackageInterface $rootPackage, array $lockedPackages): array
    {
        if (!$this->symfonyConstraints || !$data) {
            return $data;
        }

        $lockedVersions = [];
        foreach ($lockedPackages as $package) {
            $lockedVersions[$package->getName()] = [$package->getVersion()];
            if ($package instanceof AliasPackage) {
                $lockedVersions[$package->getName()][] = $package->getAliasOf()->getVersion();
            }
        }

        $rootConstraints = [];
        foreach ($rootPackage->getRequires() + $rootPackage->getDevRequires() as $name => $link) {
            $rootConstraints[$name] = $link->getConstraint();
        }

        $knownVersions = $this->getVersions();
        $filteredPackages = [];
        $symfonyPackages = [];
        $oneSymfony = false;
        foreach ($data as $package) {
            $name = $package->getName();
            $versions = [$package->getVersion()];
            if ($package instanceof AliasPackage) {
                $versions[] = $package->getAliasOf()->getVersion();
            }

            if ('symfony/symfony' !== $name && (
                !isset($knownVersions['splits'][$name])
                || array_intersect($versions, $lockedVersions[$name] ?? [])
                || (isset($rootConstraints[$name]) && !Intervals::haveIntersections($this->symfonyConstraints, $rootConstraints[$name]))
            )) {
                $filteredPackages[] = $package;
                continue;
            }

            if (null !== $alias = $package->getExtra()['branch-alias'][$package->getVersion()] ?? null) {
                $versions[] = $this->versionParser->normalize($alias);
            }

            foreach ($versions as $version) {
                if ($this->symfonyConstraints->matches(new Constraint('==', $version))) {
                    $filteredPackages[] = $package;
                    $oneSymfony = $oneSymfony || 'symfony/symfony' === $name;
                    continue 2;
                }
            }

            if ('symfony/symfony' === $name) {
                $symfonyPackages[] = $package;
            } elseif (null !== $this->io) {
                $this->io->writeError(sprintf('<info>Restricting packages listed in "symfony/symfony" to "%s"</>', $this->symfonyRequire));
                $this->io = null;
            }
        }

        if ($symfonyPackages && !$oneSymfony) {
            $filteredPackages = array_merge($filteredPackages, $symfonyPackages);
        }

        return $filteredPackages;
    }

    private function getVersions(): array
    {
        if (null !== $this->versions) {
            return $this->versions;
        }

        $versions = $this->downloader->getVersions();
        $this->downloader = null;
        $okVersions = [];

        if (!isset($versions['splits'])) {
            throw new \LogicException('The Flex index is missing a "splits" entry. Did you forget to add "flex://defaults" in the "extra.symfony.endpoint" array of your composer.json?');
        }
        foreach ($versions['splits'] as $name => $vers) {
            foreach ($vers as $i => $v) {
                if (!isset($okVersions[$v])) {
                    $okVersions[$v] = false;
                    $w = '.x' === substr($v, -2) ? $versions['next'] : $v;

                    for ($j = 0; $j < 60; ++$j) {
                        if ($this->symfonyConstraints->matches(new Constraint('==', $w.'.'.$j.'.0'))) {
                            $okVersions[$v] = true;
                            break;
                        }
                    }
                }

                if (!$okVersions[$v]) {
                    unset($vers[$i]);
                }
            }

            if (!$vers || $vers === $versions['splits'][$name]) {
                unset($versions['splits'][$name]);
            }
        }

        return $this->versions = $versions;
    }
}

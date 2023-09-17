<?php

declare(strict_types=1);

namespace PackageVersions;

use Composer\InstalledVersions;
use OutOfBoundsException;
use UnexpectedValueException;

class_exists(InstalledVersions::class);

/**
 * This is a stub class: it is in place only for scenarios where PackageVersions
 * is installed with a `--no-scripts` flag, in which scenarios the Versions class
 * is not being replaced.
 *
 * If you are reading this docBlock inside your `vendor/` dir, then this means
 * that PackageVersions didn't correctly install, and is in "fallback" mode.
 *
 * @deprecated in favor of the Composer\InstalledVersions class provided by Composer 2. Require composer-runtime-api:^2 to ensure it is present.
 */
final class Versions
{
    /**
     * @deprecated please use {@see self::rootPackageName()} instead.
     *             This constant will be removed in version 2.0.0.
     */
    const ROOT_PACKAGE_NAME = 'unknown/root-package@UNKNOWN';

    /** @internal */
    const VERSIONS          = [];

    private function __construct()
    {
    }

    /**
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function rootPackageName() : string
    {
        if (!class_exists(InstalledVersions::class, false) || !InstalledVersions::getRawData()) {
            return self::ROOT_PACKAGE_NAME;
        }

        return InstalledVersions::getRootPackage()['name'];
    }

    /**
     * @throws OutOfBoundsException if a version cannot be located.
     * @throws UnexpectedValueException if the composer.lock file could not be located.
     */
    public static function getVersion(string $packageName): string
    {
        if (!self::composer2ApiUsable()) {
            return FallbackVersions::getVersion($packageName);
        }

        /** @psalm-suppress DeprecatedConstant */
        if ($packageName === self::ROOT_PACKAGE_NAME) {
            $rootPackage = InstalledVersions::getRootPackage();

            return $rootPackage['pretty_version'] . '@' . $rootPackage['reference'];
        }

        return InstalledVersions::getPrettyVersion($packageName)
            . '@' . InstalledVersions::getReference($packageName);
    }

    private static function composer2ApiUsable(): bool
    {
        if (!class_exists(InstalledVersions::class, false)) {
            return false;
        }

        if (method_exists(InstalledVersions::class, 'getAllRawData')) {
            $rawData = InstalledVersions::getAllRawData();
            if (count($rawData) === 1 && count($rawData[0]) === 0) {
                return false;
            }
        } else {
            $rawData = InstalledVersions::getRawData();
            if ($rawData === null || $rawData === []) {
                return false;
            }
        }

        return true;
    }
}

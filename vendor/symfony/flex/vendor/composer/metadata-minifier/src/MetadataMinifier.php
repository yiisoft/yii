<?php

/*
 * This file is part of composer/metadata-minifier.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\MetadataMinifier;

class MetadataMinifier
{
    /**
     * Expands an array of minified versions back to their original format
     *
     * @param array[] $versions A list of minified version arrays
     * @return array[] A list of version arrays
     */
    public static function expand(array $versions)
    {
        $expanded = array();
        $expandedVersion = null;
        foreach ($versions as $versionData) {
            if (!$expandedVersion) {
                $expandedVersion = $versionData;
                $expanded[] = $expandedVersion;
                continue;
            }

            // add any changes from the previous version to the expanded one
            foreach ($versionData as $key => $val) {
                if ($val === '__unset') {
                    unset($expandedVersion[$key]);
                } else {
                    $expandedVersion[$key] = $val;
                }
            }

            $expanded[] = $expandedVersion;
        }

        return $expanded;
    }

    /**
     * Minifies an array of versions into a set of version diffs
     *
     * @param array[] $versions A list of version arrays
     * @return array[] A list of versions minified with each array only containing the differences to the previous one
     */
    public static function minify(array $versions)
    {
        $minifiedVersions = array();

        $lastKnownVersionData = null;
        foreach ($versions as $version) {
            if (!$lastKnownVersionData) {
                $lastKnownVersionData = $version;
                $minifiedVersions[] = $version;
                continue;
            }

            $minifiedVersion = array();

            // add any changes from the previous version
            foreach ($version as $key => $val) {
                if (!isset($lastKnownVersionData[$key]) || $lastKnownVersionData[$key] !== $val) {
                    $minifiedVersion[$key] = $val;
                    $lastKnownVersionData[$key] = $val;
                }
            }

            // store any deletions from the previous version for keys missing in current one
            foreach ($lastKnownVersionData as $key => $val) {
                if (!isset($version[$key])) {
                    $minifiedVersion[$key] = "__unset";
                    unset($lastKnownVersionData[$key]);
                }
            }

            $minifiedVersions[] = $minifiedVersion;
        }

        return $minifiedVersions;
    }
}

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

use Composer\Cache;
use Composer\Composer;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Util\Http\Response as ComposerResponse;
use Composer\Util\HttpDownloader;
use Composer\Util\Loop;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Nicolas Grekas <p@tchwork.com>
 */
class Downloader
{
    private const DEFAULT_ENDPOINTS = [
        'https://raw.githubusercontent.com/symfony/recipes/flex/main/index.json',
        'https://raw.githubusercontent.com/symfony/recipes-contrib/flex/main/index.json',
    ];
    private const MAX_LENGTH = 1000;

    private static $versions;
    private static $aliases;

    private $io;
    private $sess;
    private $cache;

    private HttpDownloader $rfs;
    private $degradedMode = false;
    private $endpoints;
    private $index;
    private $conflicts;
    private $legacyEndpoint;
    private $caFile;
    private $enabled = true;
    private $composer;

    public function __construct(Composer $composer, IoInterface $io, HttpDownloader $rfs)
    {
        if (getenv('SYMFONY_CAFILE')) {
            $this->caFile = getenv('SYMFONY_CAFILE');
        }

        if (null === $endpoint = $composer->getPackage()->getExtra()['symfony']['endpoint'] ?? null) {
            $this->endpoints = self::DEFAULT_ENDPOINTS;
        } elseif (\is_array($endpoint) || false !== strpos($endpoint, '.json') || 'flex://defaults' === $endpoint) {
            $this->endpoints = array_values((array) $endpoint);
            if (\is_string($endpoint) && false !== strpos($endpoint, '.json')) {
                $this->endpoints[] = 'flex://defaults';
            }
        } else {
            $this->legacyEndpoint = rtrim($endpoint, '/');
        }

        if (false === $endpoint = getenv('SYMFONY_ENDPOINT')) {
            // no-op
        } elseif (false !== strpos($endpoint, '.json') || 'flex://defaults' === $endpoint) {
            $this->endpoints ?? $this->endpoints = self::DEFAULT_ENDPOINTS;
            array_unshift($this->endpoints, $endpoint);
            $this->legacyEndpoint = null;
        } else {
            $this->endpoints = null;
            $this->legacyEndpoint = rtrim($endpoint, '/');
        }

        if (null !== $this->endpoints) {
            if (false !== $i = array_search('flex://defaults', $this->endpoints, true)) {
                array_splice($this->endpoints, $i, 1, self::DEFAULT_ENDPOINTS);
            }

            $this->endpoints = array_fill_keys($this->endpoints, []);
        }

        $this->io = $io;
        $config = $composer->getConfig();
        $this->rfs = $rfs;
        $this->cache = new Cache($io, $config->get('cache-repo-dir').'/flex');
        $this->sess = bin2hex(random_bytes(16));
        $this->composer = $composer;
    }

    public function getSessionId(): string
    {
        return $this->sess;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function disable()
    {
        $this->enabled = false;
    }

    public function getVersions()
    {
        $this->initialize();

        return self::$versions ?? self::$versions = current($this->get([$this->legacyEndpoint.'/versions.json']));
    }

    public function getAliases()
    {
        $this->initialize();

        return self::$aliases ?? self::$aliases = current($this->get([$this->legacyEndpoint.'/aliases.json']));
    }

    /**
     * Downloads recipes.
     *
     * @param OperationInterface[] $operations
     */
    public function getRecipes(array $operations): array
    {
        $this->initialize();

        if ($this->conflicts) {
            $lockedRepository = $this->composer->getLocker()->getLockedRepository();
            foreach ($this->conflicts as $conflicts) {
                foreach ($conflicts as $package => $versions) {
                    foreach ($versions as $version => $conflicts) {
                        foreach ($conflicts as $conflictingPackage => $constraint) {
                            if ($lockedRepository->findPackage($conflictingPackage, $constraint)) {
                                unset($this->index[$package][$version]);
                            }
                        }
                    }
                }
            }
            $this->conflicts = [];
        }

        $data = [];
        $urls = [];
        $chunk = '';
        $recipeRef = null;
        foreach ($operations as $operation) {
            $o = 'i';
            if ($operation instanceof UpdateOperation) {
                $package = $operation->getTargetPackage();
                $o = 'u';
            } else {
                $package = $operation->getPackage();
                if ($operation instanceof UninstallOperation) {
                    $o = 'r';
                }

                if ($operation instanceof InformationOperation) {
                    $recipeRef = $operation->getRecipeRef();
                }
            }

            $version = $package->getPrettyVersion();
            if ($operation instanceof InformationOperation && $operation->getVersion()) {
                $version = $operation->getVersion();
            }
            if (0 === strpos($version, 'dev-') && isset($package->getExtra()['branch-alias'])) {
                $branchAliases = $package->getExtra()['branch-alias'];
                if (
                    (isset($branchAliases[$version]) && $alias = $branchAliases[$version]) ||
                    (isset($branchAliases['dev-main']) && $alias = $branchAliases['dev-main']) ||
                    (isset($branchAliases['dev-trunk']) && $alias = $branchAliases['dev-trunk']) ||
                    (isset($branchAliases['dev-develop']) && $alias = $branchAliases['dev-develop']) ||
                    (isset($branchAliases['dev-default']) && $alias = $branchAliases['dev-default']) ||
                    (isset($branchAliases['dev-latest']) && $alias = $branchAliases['dev-latest']) ||
                    (isset($branchAliases['dev-next']) && $alias = $branchAliases['dev-next']) ||
                    (isset($branchAliases['dev-current']) && $alias = $branchAliases['dev-current']) ||
                    (isset($branchAliases['dev-support']) && $alias = $branchAliases['dev-support']) ||
                    (isset($branchAliases['dev-tip']) && $alias = $branchAliases['dev-tip']) ||
                    (isset($branchAliases['dev-master']) && $alias = $branchAliases['dev-master'])
                ) {
                    $version = $alias;
                }
            }

            if ($recipeVersions = $this->index[$package->getName()] ?? null) {
                $version = explode('.', preg_replace('/^dev-|^v|\.x-dev$|-dev$/', '', $version));
                $version = $version[0].'.'.($version[1] ?? '9999999');

                foreach (array_reverse($recipeVersions) as $v => $endpoint) {
                    if (version_compare($version, $v, '<')) {
                        continue;
                    }

                    $data['locks'][$package->getName()]['version'] = $version;
                    $data['locks'][$package->getName()]['recipe']['version'] = $v;
                    $links = $this->endpoints[$endpoint]['_links'];

                    if (null !== $recipeRef && isset($links['archived_recipes_template'])) {
                        if (isset($links['archived_recipes_template_relative'])) {
                            $links['archived_recipes_template'] = preg_replace('{[^/\?]*+(?=\?|$)}', $links['archived_recipes_template_relative'], $endpoint, 1);
                        }

                        $urls[] = strtr($links['archived_recipes_template'], [
                            '{package_dotted}' => str_replace('/', '.', $package->getName()),
                            '{ref}' => $recipeRef,
                        ]);

                        break;
                    }

                    if (isset($links['recipe_template_relative'])) {
                        $links['recipe_template'] = preg_replace('{[^/\?]*+(?=\?|$)}', $links['recipe_template_relative'], $endpoint, 1);
                    }

                    $urls[] = strtr($links['recipe_template'], [
                        '{package_dotted}' => str_replace('/', '.', $package->getName()),
                        '{package}' => $package->getName(),
                        '{version}' => $v,
                    ]);

                    break;
                }

                continue;
            }

            if (\is_array($recipeVersions)) {
                $data['conflicts'][$package->getName()] = true;
            }

            if (null !== $this->endpoints) {
                continue;
            }

            // FIXME: Multi name with getNames()
            $name = str_replace('/', ',', $package->getName());
            $path = sprintf('%s,%s%s', $name, $o, $version);
            if ($date = $package->getReleaseDate()) {
                $path .= ','.$date->format('U');
            }
            if (\strlen($chunk) + \strlen($path) > self::MAX_LENGTH) {
                $urls[] = $this->legacyEndpoint.'/p/'.$chunk;
                $chunk = $path;
            } elseif ($chunk) {
                $chunk .= ';'.$path;
            } else {
                $chunk = $path;
            }
        }
        if ($chunk) {
            $urls[] = $this->legacyEndpoint.'/p/'.$chunk;
        }

        if (null === $this->endpoints) {
            foreach ($this->get($urls, true) as $body) {
                foreach ($body['manifests'] ?? [] as $name => $manifest) {
                    $data['manifests'][$name] = $manifest;
                }
                foreach ($body['locks'] ?? [] as $name => $lock) {
                    $data['locks'][$name] = $lock;
                }
            }
        } else {
            foreach ($this->get($urls, true) as $body) {
                foreach ($body['manifests'] ?? [] as $name => $manifest) {
                    if (null === $version = $data['locks'][$name]['recipe']['version'] ?? null) {
                        continue;
                    }
                    $endpoint = $this->endpoints[$this->index[$name][$version]];

                    $data['locks'][$name]['recipe'] = [
                        'repo' => $endpoint['_links']['repository'],
                        'branch' => $endpoint['branch'],
                        'version' => $version,
                        'ref' => $manifest['ref'],
                    ];

                    foreach ($manifest['files'] ?? [] as $i => $file) {
                        $manifest['files'][$i]['contents'] = \is_array($file['contents']) ? implode("\n", $file['contents']) : base64_decode($file['contents']);
                    }

                    $data['manifests'][$name] = $manifest + [
                        'repository' => $endpoint['_links']['repository'],
                        'package' => $name,
                        'version' => $version,
                        'origin' => strtr($endpoint['_links']['origin_template'], [
                            '{package}' => $name,
                            '{version}' => $version,
                        ]),
                        'is_contrib' => $endpoint['is_contrib'] ?? false,
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * Used to "hide" a recipe version so that the next most-recent will be returned.
     *
     * This is used when resolving "conflicts".
     */
    public function removeRecipeFromIndex(string $packageName, string $version)
    {
        unset($this->index[$packageName][$version]);
    }

    /**
     * Fetches and decodes JSON HTTP response bodies.
     */
    private function get(array $urls, bool $isRecipe = false, int $try = 3): array
    {
        $responses = [];
        $retries = [];
        $options = [];

        foreach ($urls as $url) {
            $cacheKey = self::generateCacheKey($url);
            $headers = [];

            if (preg_match('{^https?://api\.github\.com/}', $url)) {
                $headers[] = 'Accept: application/vnd.github.v3.raw';
            } elseif (preg_match('{^https?://raw\.githubusercontent\.com/}', $url) && $this->io->hasAuthentication('github.com')) {
                $auth = $this->io->getAuthentication('github.com');
                if ('x-oauth-basic' === $auth['password']) {
                    $headers[] = 'Authorization: token '.$auth['username'];
                }
            } elseif ($this->legacyEndpoint) {
                $headers[] = 'Package-Session: '.$this->sess;
            }

            if ($contents = $this->cache->read($cacheKey)) {
                $cachedResponse = Response::fromJson(json_decode($contents, true));
                if ($lastModified = $cachedResponse->getHeader('last-modified')) {
                    $headers[] = 'If-Modified-Since: '.$lastModified;
                }
                if ($eTag = $cachedResponse->getHeader('etag')) {
                    $headers[] = 'If-None-Match: '.$eTag;
                }
                $responses[$url] = $cachedResponse->getBody();
            }

            $options[$url] = $this->getOptions($headers);
        }

        $loop = new Loop($this->rfs);
        $jobs = [];
        foreach ($urls as $url) {
            $jobs[] = $this->rfs->add($url, $options[$url])->then(function (ComposerResponse $response) use ($url, &$responses) {
                if (200 === $response->getStatusCode()) {
                    $cacheKey = self::generateCacheKey($url);
                    $responses[$url] = $this->parseJson($response->getBody(), $url, $cacheKey, $response->getHeaders())->getBody();
                }
            }, function (\Exception $e) use ($url, &$retries) {
                $retries[] = [$url, $e];
            });
        }
        $loop->wait($jobs);

        if (!$retries) {
            return $responses;
        }

        if (0 < --$try) {
            usleep(100000);

            return $this->get(array_column($retries, 0), $isRecipe, $try) + $responses;
        }

        foreach ($retries as [$url, $e]) {
            if (isset($responses[$url])) {
                $this->switchToDegradedMode($e, $url);
            } elseif ($isRecipe) {
                $this->io->writeError('<warning>Failed to download recipe: '.$e->getMessage().'</>');
            } else {
                throw $e;
            }
        }

        return $responses;
    }

    private function parseJson(string $json, string $url, string $cacheKey, array $lastHeaders): Response
    {
        $data = JsonFile::parseJson($json, $url);
        if (!empty($data['warning'])) {
            $this->io->writeError('<warning>Warning from '.$url.': '.$data['warning'].'</>');
        }
        if (!empty($data['info'])) {
            $this->io->writeError('<info>Info from '.$url.': '.$data['info'].'</>');
        }

        $response = new Response($data, $lastHeaders);
        if ($cacheKey && ($response->getHeader('last-modified') || $response->getHeader('etag'))) {
            $this->cache->write($cacheKey, json_encode($response));
        }

        return $response;
    }

    private function switchToDegradedMode(\Exception $e, string $url)
    {
        if (!$this->degradedMode) {
            $this->io->writeError('<warning>'.$e->getMessage().'</>');
            $this->io->writeError('<warning>'.$url.' could not be fully loaded, package information was loaded from the local cache and may be out of date</>');
        }
        $this->degradedMode = true;
    }

    private function getOptions(array $headers): array
    {
        $options = ['http' => ['header' => $headers]];

        if (null !== $this->caFile) {
            $options['ssl']['cafile'] = $this->caFile;
        }

        return $options;
    }

    private function initialize()
    {
        if (null !== $this->index || null === $this->endpoints) {
            $this->index ?? $this->index = [];

            return;
        }

        $indexes = self::$versions = self::$aliases = [];

        foreach ($this->get(array_keys($this->endpoints)) as $endpoint => $index) {
            $indexes[$endpoint] = $index;
        }

        foreach ($this->endpoints as $endpoint => $config) {
            $config = $indexes[$endpoint] ?? [];
            foreach ($config['recipes'] ?? [] as $package => $versions) {
                $this->index[$package] = $this->index[$package] ?? array_fill_keys($versions, $endpoint);
            }
            $this->conflicts[] = $config['recipe-conflicts'] ?? [];
            self::$versions += $config['versions'] ?? [];
            self::$aliases += $config['aliases'] ?? [];
            unset($config['recipes'], $config['recipe-conflicts'], $config['versions'], $config['aliases']);
            $this->endpoints[$endpoint] = $config;
        }
    }

    private static function generateCacheKey(string $url): string
    {
        $url = preg_replace('{^https://api.github.com/repos/([^/]++/[^/]++)/contents/}', '$1/', $url);
        $url = preg_replace('{^https://raw.githubusercontent.com/([^/]++/[^/]++)/}', '$1/', $url);

        $key = preg_replace('{[^a-z0-9.]}i', '-', $url);

        // eCryptfs can have problems with filenames longer than around 143 chars
        return \strlen($key) > 140 ? md5($url) : $key;
    }
}

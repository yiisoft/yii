<?php

namespace PhpSpec\NamespaceProvider;

use Composer\Autoload\ClassLoader;

/**
 * Provides project namespaces and where to find them.
 */
class ComposerPsrNamespaceProvider
{
    /**
     * @var string path to the root directory of the project, without a trailing slash
     */
    private $rootDirectory;

    /**
     * @var string prefix of the specifications namespace
     */
    private $specPrefix;

    public function __construct(string $rootDirectory, string $specPrefix)
    {
        $this->rootDirectory = $rootDirectory;
        $this->specPrefix = $specPrefix;
    }

    /**
     * @return NamespaceLocation[] a map associating a namespace to a location, e.g
     *                  [
     *                      'My\PSR4Namespace' => 'my/PSR4Namespace',
     *                      'My\PSR0Namespace' => '',
     *                  ]
     */
    public function getNamespaces(): array
    {
        $vendors = array();
        foreach (get_declared_classes() as $class) {
            if ('C' === $class[0] && 0 === strpos($class, 'ComposerAutoloaderInit')) {
                $r = new \ReflectionClass($class);
                $v = dirname(dirname($r->getFileName()));
                if (file_exists($v.'/composer/installed.json')) {
                    $vendors[] = $v;
                }
            }
        }
        $classLoader = require $this->rootDirectory . '/vendor/autoload.php';

        $namespaces = array();
        foreach (array(
            NamespaceProvider::AUTOLOADING_STANDARD_PSR0 => $classLoader->getPrefixes(),
            NamespaceProvider::AUTOLOADING_STANDARD_PSR4 => $classLoader->getPrefixesPsr4(),
        ) as $standard => $prefixes) {
            $namespaces = array_merge($namespaces, $this->getNamespacesFromPrefixes(
                $prefixes,
                $vendors,
                $standard
            ));
        }

        return $namespaces;
    }

    private function getNamespacesFromPrefixes(array $prefixes, array $vendors, $standard) : array
    {
        $namespaces = array();
        foreach ($prefixes as $namespace => $psrPrefix) {
            foreach ($psrPrefix as $location) {
                foreach ($vendors as $vendor) {
                    $realPath = realpath($location);
                    if ($realPath === false || strpos($realPath, $vendor) === 0) {
                        break 2;
                    }
                }
                if (strpos($namespace, $this->specPrefix) !== 0) {
                    $namespaces[$namespace] = new NamespaceLocation(
                        $namespace,
                        $this->normaliseLocation($location),
                        $standard
                    );
                }
            }
        }

        return $namespaces;
    }

    private function normaliseLocation($location)
    {
        return strpos(realpath($location), realpath($this->rootDirectory)) === 0 ?
            substr(
                realpath($location),
                \strlen(realpath($this->rootDirectory)) + 1 // trailing slash
            ) : '';
    }
}

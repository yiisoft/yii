<?php

namespace PhpSpec\NamespaceProvider;

/**
 * Provides project namespaces and where to find them.
 */
interface NamespaceProvider
{
    const AUTOLOADING_STANDARD_PSR0 = 'PSR0';
    const AUTOLOADING_STANDARD_PSR4 = 'PSR4';

    /**
     * @return string[] a map associating a namespace to a location, e.g
     *                  ['My\Namespace' => new NamespaceLocation(
     *                     'My\Namespace',
     *                     'my/location/relative/to/spec/or/src/directory',
     *                     NamespaceProvider::SUPPORTED_AUTOLOADING_STANDARD
     *                  )]
     */
    public function getNamespaces(): array;
}

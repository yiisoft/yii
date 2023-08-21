<?php

namespace PhpSpec\NamespaceProvider;

use Composer\Autoload\ClassLoader;

final class NamespaceLocation
{
    private $namespace;
    private $location;
    private $autoloadingStandard;

    public function __construct($namespace, $location, $autoloadingStandard)
    {
        $this->namespace = $namespace;
        $this->location = $location;
        $this->autoloadingStandard = $autoloadingStandard;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getAutoloadingStandard()
    {
        return $this->autoloadingStandard;
    }
}

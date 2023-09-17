<?php

namespace Composer\Installers;

class ProcessWireInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'module'  => 'site/modules/{$name}/',
    );

    /**
     * Format package name to CamelCase
     */
    public function inflectPackageVars(array $vars): array
    {
        $vars['name'] = strtolower($this->pregReplace('/(?<=\\w)([A-Z])/', '_\\1', $vars['name']));
        $vars['name'] = str_replace(array('-', '_'), ' ', $vars['name']);
        $vars['name'] = str_replace(' ', '', ucwords($vars['name']));

        return $vars;
    }
}

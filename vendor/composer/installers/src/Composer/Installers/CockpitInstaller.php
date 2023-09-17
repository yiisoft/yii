<?php

namespace Composer\Installers;

class CockpitInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'module' => 'cockpit/modules/addons/{$name}/',
    );

    /**
     * Format module name.
     *
     * Strip `module-` prefix from package name.
     */
    public function inflectPackageVars(array $vars): array
    {
        if ($vars['type'] == 'cockpit-module') {
            return $this->inflectModuleVars($vars);
        }

        return $vars;
    }

    /**
     * @param array<string, string> $vars
     * @return array<string, string>
     */
    public function inflectModuleVars(array $vars): array
    {
        $vars['name'] = ucfirst($this->pregReplace('/cockpit-/i', '', $vars['name']));

        return $vars;
    }
}

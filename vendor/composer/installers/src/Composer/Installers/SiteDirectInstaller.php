<?php

namespace Composer\Installers;

class SiteDirectInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'module' => 'modules/{$vendor}/{$name}/',
        'plugin' => 'plugins/{$vendor}/{$name}/'
    );

    /**
     * @param array<string, string> $vars
     * @return array<string, string>
     */
    public function inflectPackageVars(array $vars): array
    {
        return $this->parseVars($vars);
    }

    /**
     * @param array<string, string> $vars
     * @return array<string, string>
     */
    protected function parseVars(array $vars): array
    {
        $vars['vendor'] = strtolower($vars['vendor']) == 'sitedirect' ? 'SiteDirect' : $vars['vendor'];
        $vars['name'] = str_replace(array('-', '_'), ' ', $vars['name']);
        $vars['name'] = str_replace(' ', '', ucwords($vars['name']));

        return $vars;
    }
}

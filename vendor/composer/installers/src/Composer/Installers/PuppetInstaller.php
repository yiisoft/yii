<?php

namespace Composer\Installers;

class PuppetInstaller extends BaseInstaller
{

    /** @var array<string, string> */
    protected $locations = array(
        'module' => 'modules/{$name}/',
    );
}

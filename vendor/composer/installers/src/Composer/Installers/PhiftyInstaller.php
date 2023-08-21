<?php

namespace Composer\Installers;

class PhiftyInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'bundle' => 'bundles/{$name}/',
        'library' => 'libraries/{$name}/',
        'framework' => 'frameworks/{$name}/',
    );
}

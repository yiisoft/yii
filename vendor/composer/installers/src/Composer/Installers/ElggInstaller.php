<?php

namespace Composer\Installers;

class ElggInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'plugin' => 'mod/{$name}/',
    );
}

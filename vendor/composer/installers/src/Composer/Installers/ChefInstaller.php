<?php

namespace Composer\Installers;

class ChefInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'cookbook'  => 'Chef/{$vendor}/{$name}/',
        'role'      => 'Chef/roles/{$name}/',
    );
}

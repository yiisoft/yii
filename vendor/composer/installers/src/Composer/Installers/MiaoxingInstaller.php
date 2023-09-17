<?php

namespace Composer\Installers;

class MiaoxingInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'plugin' => 'plugins/{$name}/',
    );
}

<?php

namespace Composer\Installers;

class WolfCMSInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'plugin' => 'wolf/plugins/{$name}/',
    );
}

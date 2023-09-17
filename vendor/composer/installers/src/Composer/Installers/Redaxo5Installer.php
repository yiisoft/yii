<?php

namespace Composer\Installers;

class Redaxo5Installer extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'addon'          => 'redaxo/src/addons/{$name}/',
        'bestyle-plugin' => 'redaxo/src/addons/be_style/plugins/{$name}/'
    );
}

<?php

namespace Composer\Installers;

class UserFrostingInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'sprinkle' => 'app/sprinkles/{$name}/',
    );
}

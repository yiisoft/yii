<?php

namespace Composer\Installers;

use Composer\Package\PackageInterface;

class SilverStripeInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array(
        'module' => '{$name}/',
        'theme'  => 'themes/{$name}/',
    );

    /**
     * Return the install path based on package type.
     *
     * Relies on built-in BaseInstaller behaviour with one exception: silverstripe/framework
     * must be installed to 'sapphire' and not 'framework' if the version is <3.0.0
     */
    public function getInstallPath(PackageInterface $package, string $frameworkType = ''): string
    {
        if (
            $package->getName() == 'silverstripe/framework'
            && preg_match('/^\d+\.\d+\.\d+/', $package->getVersion())
            && version_compare($package->getVersion(), '2.999.999') < 0
        ) {
            return $this->templatePath($this->locations['module'], array('name' => 'sapphire'));
        }

        return parent::getInstallPath($package, $frameworkType);
    }
}

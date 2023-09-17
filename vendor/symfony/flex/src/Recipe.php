<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Flex;

use Composer\Package\PackageInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Recipe
{
    private $package;
    private $name;
    private $job;
    private $data;
    private $lock;

    public function __construct(PackageInterface $package, string $name, string $job, array $data, array $lock = [])
    {
        $this->package = $package;
        $this->name = $name;
        $this->job = $job;
        $this->data = $data;
        $this->lock = $lock;
    }

    public function getPackage(): PackageInterface
    {
        return $this->package;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getJob(): string
    {
        return $this->job;
    }

    public function getManifest(): array
    {
        if (!isset($this->data['manifest'])) {
            throw new \LogicException(sprintf('Manifest is not available for recipe "%s".', $this->name));
        }

        return $this->data['manifest'];
    }

    public function getFiles(): array
    {
        return $this->data['files'] ?? [];
    }

    public function getOrigin(): string
    {
        return $this->data['origin'] ?? '';
    }

    public function getFormattedOrigin(): string
    {
        if (!$this->getOrigin()) {
            return '';
        }

        // symfony/translation:3.3@github.com/symfony/recipes:branch
        if (!preg_match('/^([^:]++):([^@]++)@(.+)$/', $this->getOrigin(), $matches)) {
            return $this->getOrigin();
        }

        return sprintf('<info>%s</> (<comment>>=%s</>): From %s', $matches[1], $matches[2], 'auto-generated recipe' === $matches[3] ? '<comment>'.$matches[3].'</>' : $matches[3]);
    }

    public function getURL(): string
    {
        if (!$this->data['origin']) {
            return '';
        }

        // symfony/translation:3.3@github.com/symfony/recipes:branch
        if (!preg_match('/^([^:]++):([^@]++)@([^:]++):(.+)$/', $this->data['origin'], $matches)) {
            // that excludes auto-generated recipes, which is what we want
            return '';
        }

        return sprintf('https://%s/tree/%s/%s/%s', $matches[3], $matches[4], $matches[1], $matches[2]);
    }

    public function isContrib(): bool
    {
        return $this->data['is_contrib'] ?? false;
    }

    public function getRef()
    {
        return $this->lock['recipe']['ref'] ?? null;
    }

    public function isAuto(): bool
    {
        return !isset($this->lock['recipe']);
    }

    public function getVersion(): string
    {
        return $this->lock['recipe']['version'] ?? $this->lock['version'];
    }

    public function getLock(): array
    {
        return $this->lock;
    }
}

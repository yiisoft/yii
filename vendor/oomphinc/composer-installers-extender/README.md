# Composer Installers Extender

The `composer-installers-extender` is a plugin for [Composer][] that allows
any package to be installed to a directory other than the default `vendor`
directory within a project on a package-by-package basis. This plugin extends
the [`composer/installers`][] plugin to allow any arbitrary package type to be
handled by their custom installer.

The [`composer/installers`][] plugin has a finite set of supported package types
and we recognize the need for any arbitrary package type to be installed to a
specific directory other than `vendor`. This plugin allows additional package
types to be handled by the [`composer/installers`][] plugin, benefiting from
their explicit install path mapping and token replacement of package properties.

## How to Install

Add `oomphinc/composer-installers-extender` as a dependency of your project:

```bash
$ composer require oomphinc/composer-installers-extender
```

This plugin requires at least PHP 7.1. If you're using a lower version of PHP
use the latest stable 1.x release:

```bash
$ composer require oomphinc/composer-installers-extender:^1.1
```

## How to Use

The [`composer/installers`][] plugin is a dependency of this plugin and will be
automatically required as well if not already required.

To support additional package types, add an array of these types in the
`extra` property in your `composer.json`:

with [`composer/installers`][] < v1.0.13:
```json
{
    "extra": {
        "installer-types": ["library"]
    }
}
```
with [`composer/installers`][] >= v1.0.13:
```json
{
    "extra": {
        "installer-types": ["drupal-library"]
    }
}
```
Then refer to that type when adding to `installer-paths`:

with [`composer/installers`][] < v1.0.13:
```json
{
    "extra": {
        "installer-types": ["library"],
        "installer-paths": {
            "special/package/": ["my/package"],
            "path/to/libraries/{$name}/": ["type:library"]
        }
    }
}
```
with [`composer/installers`][] >= v1.0.13:
```json
{
    "extra": {
        "installer-types": ["drupal-library"],
        "installer-paths": {
            "special/package/": ["my/package"],
            "path/to/libraries/{$name}/": ["type:drupal-library"]
        }
    }
}
```

By default, packages that do not specify a `type` will be considered the type
`library`. Adding support for this type allows any of these packages to be
placed in a different install path.

If a type has been added to `installer-types`, the plugin will attempt to find
an explicit installer path in the mapping. If there is no match either by name
or by type, the default installer path for all packages will be used instead.

**Please see the README for [`composer/installers`][] to see the supported syntax
for package and type matching as well as the supported replacement tokens in
the path (e.g. `{$name}`).**

## License

[MIT License][]

[Composer]: https://getcomposer.org
[`composer/installers`]: https://github.com/composer/installers
[MIT License]: LICENSE

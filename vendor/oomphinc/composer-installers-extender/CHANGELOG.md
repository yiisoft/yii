# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog][] and this project adheres to the
[Semantic Versioning][] scheme.

[Keep a Changelog]: http://keepachangelog.com
[Semantic Versioning]: https://semver.org

## [Unreleased]
### Added

### Changed

### Deprecated

### Removed

### Fixed

### Security

## [2.0.1] - 2021-12-15
### Changed
- Add support for composer/installers 2.x

## [2.0.0] - 2020-08-11
### Added
- Add `.editorconfig`, `.gitignore`, `.lando.yml`, `phpcs.xml` and `phpunit.xml`
  files to support local development
- Add `LICENSE` and `CHANGELOG.md` files
- Add `phpunit/phpunit` and `squizlabs/php_codesniffer` as development
  dependencies
- Add requirement for PHP 7.1
- Add support for Composer 2

### Changed
- Move `OomphInc\ComposerInstallersExtender\Installer` to
  `OomphInc\ComposerInstallersExtender\Installers\Installer`
- Move `OomphInc\ComposerInstallersExtender\InstallerHelper` to
  `OomphInc\ComposerInstallersExtender\Installers\CustomInstaller`
- Implement PSR-2 standards and PHP 7.1 syntax
- Update project `README.md` file

## [1.1.2] - 2017-03-31
### Changed
- Minor syntax update to provide compatibility with PHP 5.3

## [1.1.1] - 2016-07-05
### Changed
- Update composer/installers version requirement

## [1.1.0] - 2016-03-02
### Changed
- Update package requirements to be less restrictive

## [1.0.0] - 2016-01-07
### Added
- Initial release

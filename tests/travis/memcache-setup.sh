#!/bin/sh -e

if (php --version | grep -i HipHop > /dev/null); then
  echo "skipping memcache on HHVM"
elif [ $(phpenv version-name) = "7.0" ] || [ $(phpenv version-name) = "7.1" ] || [ $(phpenv version-name) = "7.2" ] || [ $(phpenv version-name) = "7.3" ] || [ $(phpenv version-name) = "nightly" ]; then
  echo "skipping memcache on php 7"
else
  mkdir -p ~/.phpenv/versions/$(phpenv version-name)/etc
  echo "extension=memcache.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
  echo "extension=memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi

#!/bin/bash

echo 'y' | pecl install memcache > ~/memcache.log || ( echo "=== MEMCACHE BUILD FAILED ==="; cat ~/memcache.log )
if [[ $TRAVIS_PHP_VERSION < 5.4 ]]; then
  echo "extension=memcache.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
fi

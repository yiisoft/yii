#!/bin/bash

if [[ $TRAVIS_PHP_VERSION < 5.5 ]]; then
  echo 'y' | pecl install memcache > ~/memcache.log || ( echo "=== MEMCACHE BUILD FAILED ==="; cat ~/memcache.log )
else
  wget http://pecl.php.net/get/memcache-2.2.7.tgz
  tar -zxf memcache-2.2.7.tgz
  sh -c "cd memcache-2.2.7 && phpize && ./configure --enable-memcache && make && sudo make install"
  echo "memcache.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s/.*:\s*//"`
fi

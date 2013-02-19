#!/bin/sh

install_memcache() {
    if [ "$(expr "$TRAVIS_PHP_VERSION" "<" "5.5")" -eq 1 ]; then
        echo 'y' | pecl install memcache
    else
        MEMCACHE_VERSION="2.2.7"
        wget "http://pecl.php.net/get/memcache-$MEMCACHE_VERSION.tgz" &&
        tar -zxf "memcache-$MEMCACHE_VERSION.tgz" &&
        sh -c "cd memcache-$MEMCACHE_VERSION && phpize && ./configure --enable-memcache && make && sudo make install" &&
        echo "extension=memcache.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s/.*:\s*//"`
    fi

    return $?
}

install_memcache > ~/memcache.log || ( echo "=== MEMCACHE BUILD FAILED ==="; cat ~/memcache.log )

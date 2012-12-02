#!/bin/bash

echo 'y' | pecl install memcache > ~/memcache.log || ( echo "=== MEMCACHE BUILD FAILED ==="; cat ~/memcache.log )

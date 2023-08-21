#!/bin/sh

sed -i "/mirror:\\/\\//d" /etc/apt/sources.list
sed -i "1ideb mirror://mirrors.ubuntu.com/mirrors.txt precise main restricted universe multiverse" /etc/apt/sources.list
sed -i "1ideb mirror://mirrors.ubuntu.com/mirrors.txt precise-updates main restricted universe multiverse" /etc/apt/sources.list
sed -i "1ideb mirror://mirrors.ubuntu.com/mirrors.txt precise-backports main restricted universe multiverse" /etc/apt/sources.list
sed -i "1ideb mirror://mirrors.ubuntu.com/mirrors.txt precise-security main restricted universe multiverse" /etc/apt/sources.list

apt-get update

# installing xvfb, java and php
apt-get install xvfb openjdk-7-jre-headless php5-cli php5-curl php5-xdebug -y --no-install-recommends

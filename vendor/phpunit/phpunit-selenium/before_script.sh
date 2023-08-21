serverUrl='http://127.0.0.1:4444'
serverVersion='2.35.0'
serverFile=selenium-server-standalone-$serverVersion.jar
firefoxUrl=http://ftp.mozilla.org/pub/mozilla.org/firefox/releases/23.0/linux-x86_64/en-US/firefox-23.0.tar.bz2
firefoxFile=firefox.tar.bz2
fixturePort=8080
phpVersion=`php -v`


if [ ! -f composer.phar ]; then
    echo "Getting composer"
    curl -O http://getcomposer.org/composer.phar
else
    php composer.phar self-update
fi

echo "Dependencies"
if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then 
    php composer.phar install --dev
else
    php composer.phar update --dev
fi

cd selenium-1-tests
if $(echo "$phpVersion" | grep --quiet 'PHP 5.4'); then
    echo "Starting PHP 5.4 web server"
    php -S localhost:$fixturePort > /tmp/server.log 2>&1 &
else
    echo "Starting Python web server"
    python -m SimpleHTTPServer $fixturePort > /tmp/server.log 2>&1 &
fi
cd ..

echo "Downloading Firefox"
wget $firefoxUrl -O $firefoxFile
tar xvjf $firefoxFile

echo "Starting xvfb"
echo "Starting Selenium"
if [ ! -f $serverFile ]; then
    wget http://selenium.googlecode.com/files/selenium-server-standalone-$serverVersion.jar -O $serverFile
fi
xvfb-run java -Dwebdriver.firefox.bin=firefox/firefox-bin  -jar $serverFile > /tmp/selenium.log 2>&1 &

wget --retry-connrefused --tries=60 --waitretry=1 --output-file=/dev/null $serverUrl/wd/hub/status -O /dev/null
if [ ! $? -eq 0 ]; then
    echo "Selenium Server not started"
else
    echo "Finished setup"
fi

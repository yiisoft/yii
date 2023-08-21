#!/bin/bash

sudo supervisorctl reload

wget --retry-connrefused --tries=60 --waitretry=1 --output-file=/dev/null "$SELENIUM_HUB_URL/wd/hub/status" -O /dev/null
if [ ! $? -eq 0 ]; then
    echo "Selenium Server not started"
else
    echo "Finished setup"
fi

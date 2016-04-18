
                CLDR v23.1 (May 15, 2013)

This directory contains the CLDR data files in form of PHP scripts.
They are obtained by extracting the CLDR data (http://cldr.unicode.org/index/downloads/cldr-23-1)
with the script "build/build cldr".

Only the data relevant to date and number formatting are extracted.
Each PHP file contains an array representing the data for a particular
locale. Data inherited from parent locales are also in the array.

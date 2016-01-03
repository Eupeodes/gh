#Changelog

## v2.0.7 - 2016-01-03
* Separate user config and system config in preparation for storing user confing in a cookie
* Rename settings.php to config.php, make it object based
* Minor cosmetic changes (cursor: pointer) on controls

## v2.0.6 - 2016-01-03
Replace existing google markers with own markers, more xkcd style
* Both foreground and background color are customizable
* Markers can show either day of week or day of month (2 characters max)
* Interface to choose colors from a limited set and toggle day of week or month added to interface

## v2.0.5 - 2016-01-03
Map type added as url parameter in both short and long url format

## v2.0.4 - 2016-01-02
Zoom level set to 2 when no home location is set

## v2.0.3 - 2016-01-01
Use position: fixed and top/bottom/left/right: 0px instead of width/height:100%, prevents overflow in browser which indicate wrong height

## v2.0.2 - 2016-01-01
Fix data format being used, month should have leading zero, was omitted because of typing error.

## v2.0.1 - 2015-12-31
Fixing url compatibility with the old version (mainly the hashed graticules)

## v2.0.0 - 2015-12-31
The initial version
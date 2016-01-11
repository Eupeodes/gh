#Changelog

## v2.1.0 - 2016-01-11
Settings can now be saved

## v2.0.14 - 2016-01-10
* Implemented setting map center and grid center through url parameters, documentation follows
* Help page added

## v2.0.13 - 2016-01-08
Follow @geohashing on twitter link added

## v2.0.12 - 2016-01-08
* Better scroll of greybox on iOS
* Scroll greybox back to top when opening
* Greybox full screen on small screen

## v2.0.11 - 2016-01-07
* Fix some pixel defects in markers
* Rewrite of views in php

## v2.0.10 - 2016-01-05
* Show changelog
* Fix for date url parameter (caused by code change in v2.0.7)

## v2.0.9 - 2016-01-04
Fix for display in Twitter app on iOS

## v2.0.8 - 2016-01-04
* Fixing timezone issue which caused a wrong date to be displayed on the markers west of UTC
* Fixing display issues on Windows in the Zoom Control
* Extend execution of js until after css has loaded

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
Fixing date format being used, month should have leading zero, was omitted because of typing error.

## v2.0.1 - 2015-12-31
Fixing url compatibility with the old version (mainly the hashed graticules)

## v2.0.0 - 2015-12-31
The initial version

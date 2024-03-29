# Changelog

## v2.9.0 - 2023-05-17
Add bing maps link

## v2.8.0 - 2022-12-23
Add Map click / redetect action to cookie

## v2.7.0 - 2021-11-26
- Remove defunct navigation compass
- Add OSM link

## v2.6.4 - 2020-03-19
Fix decimal precision

## v2.6.3 - 2020-03-01
Commit transition from wiki.xkcd.com to geohashing.site

## v2.6.2 - 2019-03-26
Detect iOS 12.2 and give instructions how to allow Access to Motion & Direction

## v2.6.1 - 2019-03-24
Make url bar updating optional

## v2.6.0 - 2019-03-22
Url bar updates with change of map view giving direct url to current page (like google maps).

## v2.5.1 - 2019-02-07
Fix for display of popover when no home location available

## v.2.5.0 - 2018-07-27
Added mobile navigation page with link in the popup balloon

## v.2.4.1 - 2018-05-01
Fix GPX link

## v.2.4.0 - 2017-08-04
Separated data in subdomain in preparation of new plans

## v.2.3.0 - 2017-08-03
Upgraded OpenLayers to version 4.2.0

## v2.2.2 - 2017-04-25
Added the option to add a new DOW to the database while still triggering the irc and twitter bot

## v2.2.1 - 2016-02-12
Fixed bug that broke the whole system

## v2.2.0 - 2016-02-12
* Added warning about the 30W rule which is shown when requesting hashes west of 30 degrees west for the last date the datapicker permits.
* Fixed an issue causing longitudes that exceeded 180 degrees 

## v2.1.7 - 2016-02-11
Added link to wiki in help function

## v2.1.6 - 2016-02-09
If no date is given in the url use the date of the users pc, previously the date of the server was used

## v2.1.5 - 2016-01-28
Added option to leave the click/redetect action set

## v2.1.4 - 2016-01-21
* Fix bug that messed up East coordinates before introduction of W30
* Fix bug which caused cookie parameters taking precedence over url parameters

## v2.1.3 - 2016-01-14
Added disclaimer

## v2.1.2 - 2016-01-11
* Display scrollbar again, but now not disrupting layout of controls using niceScroll (https://code.google.com/p/jquery-nicescroll/)
* Upgrade jQuery to v2.2.0

## v2.1.1 - 2016-01-11
Do not display scrollbar controls on windows/linux, better scrollbar needed which doesn't break layout

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

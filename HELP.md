# Help

Geohashing.info is written by Marten Tacoma. Code can be found on https://github.com/Eupeodes/gh

## Short url parameters
Parameters should be separated by forward slashes (/), most can be combined with each other (except global with any lat/lng)

| Parameter | Short url | Remarks |
|---|---|---|
| Date | <y>/<m>/<d> | <y>=4 digit year, <m>=2 digit month, <d>=2 digit day |
| Home | [h:]<lat>,<lng> | <lat> and <lng> can contain decimals, decimal separator is dot (.), h: is optional when not combining with any center in a single statement \* |
| Grid center | g:<lat>,<lng> | see home, (g: is required) \* |
| Map center | c:<lat>,<lng> | see home, (c: is required) \* |
| Single day | s[ingle] | Otherwise up to six following days are shown|
| Global hash | g[lobal] | home, grid center and map center are ignored with this parameter |
| Zoom level | z<int> | <int>=integer from 1 till 19 |
| Map type | <type> | <type> is either *map* (Openstreetmap, default), *hyb* (Bing Hybrid) or sat (Bing Satellite) |
*\* If home is given but either grid center or map center isn't those are set to the same value as home.
The fields for home, grid center and map center can be combined in any combination, do so by combining the letters of the parameters you want before the colon (:)*

## Long url parameters
*Everywhere where **lng** is used you can also use **lon***
The parameters should be appended to the url in the form ?param1=value1&param2=value2 (add as many as needed with &param...)

| Parameter | Variable name | Remarks | 
|---|---|
| Home | lat/lng | latitude & longitude of home |
| Map Center | clat/clng | latitude & longitude of map center |
| Grid center | glat/glng | latitude & longitude of center cell of grid |
| Map type | type | map type, either map (default), hyb (for hybrid) or sat (for satellite) |
| Zoom level | zoom | zoom level |
| Date| date | date for which to show hashes, if omitted it defaults to current date |
| Single day | multi | use multi=false to show a single day, otherwise up to six following days will be shown |
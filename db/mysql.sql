DROP TABLE IF EXISTS `dow`;

CREATE TABLE `dow` (
  `date` date NOT NULL,
  `dow` float(8,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`date`),
  UNIQUE KEY `Index` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `page`;

CREATE TABLE `page` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` text,
  `title` text,
  `content` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`(10))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `page` (`url`, `title`, `content`)
VALUES
	('help','Help','<p>Geohashing.info is written by Marten Tacoma. Code can be found on <a href=\"https://github.com/Eupeodes/gh\">https://github.com/Eupeodes/gh</a></p>\n<p>Settings can be passed from cookies or url parameters. Location can be determined by geolocation. Url parameters are the most important, followed by cookies. Geolocation is only used if a location is not given in another way.</p>\n<h2>Short url parameters</h2>\n<p>Parameters should be separated by forward slashes (/), most can be combined with each other (except global with any lat/lng)</p>\n<table>\n<thead>\n<tr>\n<th>Parameter</th>\n<th>Short url</th>\n<th>Remarks</th>\n</tr>\n</thead>\n<tbody>\n<tr>\n<td>Date</td>\n<td>&lt;y&gt;/&lt;m&gt;/&lt;d&gt;</td>\n<td>&lt;y&gt;=4 digit year, &lt;m&gt;=2 digit month, &lt;d&gt;=2 digit day</td>\n</tr>\n<tr>\n<td>Home</td>\n<td>[h:]&lt;lat&gt;,&lt;lng&gt;</td>\n<td>&lt;lat&gt; and &lt;lng&gt; can contain decimals, decimal separator is dot (.), when no decimals are given .5 is used, h: is optional when not combining with any center in a single statement *</td>\n</tr>\n<tr>\n<td>Grid center</td>\n<td>g:&lt;lat&gt;,&lt;lng&gt;</td>\n<td>see home, (g: is required) *</td>\n</tr>\n<tr>\n<td>Map center</td>\n<td>c:&lt;lat&gt;,&lt;lng&gt;</td>\n<td>see home, (c: is required) *</td>\n</tr>\n<tr>\n<td>Single day</td>\n<td>s[ingle]</td>\n<td>Otherwise up to six following days are shown</td>\n</tr>\n<tr>\n<td>Global hash</td>\n<td>g[lobal]</td>\n<td>home, grid center and map center are ignored with this parameter</td>\n</tr>\n<tr>\n<td>Zoom level</td>\n<td>z:&lt;int&gt;</td>\n<td>&lt;int&gt;=integer from 1 till 19</td>\n</tr>\n<tr>\n<td>Map type</td>\n<td>t:&lt;type&gt;</td>\n<td>&lt;type&gt; is either <em>map</em> (Openstreetmap, default), <em>hyb</em> (Bing Hybrid) or sat (Bing Satellite)</td>\n</tr>\n</tbody>\n</table>\n<p><em>* If home is given but either grid center or map center isn\'t, those are set to the same value as home.\nThe fields for home, grid center and map center can be combined in any combination in a single statement, do so by combining the letters of the parameters you want before the colon (:), of course they can also be used in own separate statements</em></p>\n<h2>Long url parameters</h2>\n<p><em>Everywhere where <strong>lng</strong> is used you can also use <strong>lon</strong></em>\nThe parameters should be appended to the url in the form ?param1=value1&amp;param2=value2 (add as many as needed with &amp;param...)</p>\n<table>\n<thead>\n<tr>\n<th>Parameter</th>\n<th>Variable name</th>\n<th>Remarks</th>\n</tr>\n</thead>\n<tbody>\n<tr>\n<td>Home</td>\n<td>lat/lng</td>\n<td>latitude &amp; longitude of home</td>\n</tr>\n<tr>\n<td>Map Center</td>\n<td>clat/clng</td>\n<td>latitude &amp; longitude of map center</td>\n</tr>\n<tr>\n<td>Grid center</td>\n<td>glat/glng</td>\n<td>latitude &amp; longitude of center cell of grid</td>\n</tr>\n<tr>\n<td>Map type</td>\n<td>type</td>\n<td>map type, either map (default), hyb (for hybrid) or sat (for satellite)</td>\n</tr>\n<tr>\n<td>Zoom level</td>\n<td>zoom</td>\n<td>zoom level</td>\n</tr>\n<tr>\n<td>Date</td>\n<td>date</td>\n<td>date for which to show hashes, if omitted it defaults to current date</td>\n</tr>\n<tr>\n<td>Single day</td>\n<td>multi</td>\n<td>use multi=false to show a single day, otherwise up to six following days will be shown</td>\n</tr>\n</tbody>\n</table>'),
	('disclaimer','Disclaimer','<p>This site tries to provide the hashes as accurate as possible. Due to various reasons mistakes can occur in the location. Using this service is at own risk. The site owner can never be held responsible or liable for any costs or damage caused by using this site.</p>\n\n<p><strong>The algoritm Disclaimer:</strong> When any coordinates generated by the Geohashing algorithm fall within a dangerous area, are inaccessible, or would require illegal trespass, <strong>DO NOT</strong> attempt to reach them. Please research each potential location before attempting to access it. <strong>You are expected to use proper judgment in all cases and are solely responsible for your own actions.</strong> See <a href=\"https://geohashing.site/geohashing/Guidelines\">more guidelines</a>.</p>\n\n<button onclick=\"closeDisclaimer()\">Close disclaimer and don\'t show it again when loading this site</button>\n<em>(You can always open this disclaimer from the bottom of the controls panel)</em>');

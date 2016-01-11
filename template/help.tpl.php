<?php 
$title = 'Help';
$content = '<p>Geohashing.info is written by Marten Tacoma. Code can be found on <a href="https://github.com/Eupeodes/gh">https://github.com/Eupeodes/gh</a></p>
<h2>Short url parameters</h2>
<p>Parameters should be separated by forward slashes (/), most can be combined with each other (except global with any lat/lng)</p>
<table>
<thead>
<tr>
<th>Parameter</th>
<th>Short url</th>
<th>Remarks</th>
</tr>
</thead>
<tbody>
<tr>
<td>Date</td>
<td>&lt;y&gt;/&lt;m&gt;/&lt;d&gt;</td>
<td>&lt;y&gt;=4 digit year, &lt;m&gt;=2 digit month, &lt;d&gt;=2 digit day</td>
</tr>
<tr>
<td>Home</td>
<td>[h:]&lt;lat&gt;,&lt;lng&gt;</td>
<td>&lt;lat&gt; and &lt;lng&gt; can contain decimals, decimal separator is dot (.), when no decimals are given .5 is used, h: is optional when not combining with any center in a single statement *</td>
</tr>
<tr>
<td>Grid center</td>
<td>g:&lt;lat&gt;,&lt;lng&gt;</td>
<td>see home, (g: is required) *</td>
</tr>
<tr>
<td>Map center</td>
<td>c:&lt;lat&gt;,&lt;lng&gt;</td>
<td>see home, (c: is required) *</td>
</tr>
<tr>
<td>Single day</td>
<td>s[ingle]</td>
<td>Otherwise up to six following days are shown</td>
</tr>
<tr>
<td>Global hash</td>
<td>g[lobal]</td>
<td>home, grid center and map center are ignored with this parameter</td>
</tr>
<tr>
<td>Zoom level</td>
<td>z:&lt;int&gt;</td>
<td>&lt;int&gt;=integer from 1 till 19</td>
</tr>
<tr>
<td>Map type</td>
<td>t:&lt;type&gt;</td>
<td>&lt;type&gt; is either <em>map</em> (Openstreetmap, default), <em>hyb</em> (Bing Hybrid) or sat (Bing Satellite)</td>
</tr>
</tbody>
</table>
<p><em>* If home is given but either grid center or map center isn\'t those are set to the same value as home.
The fields for home, grid center and map center can be combined in any combination in a single statement, do so by combining the letters of the parameters you want before the colon (:), of course they can also be used in own separate statements</em></p>
<h2>Long url parameters</h2>
<p><em>Everywhere where <strong>lng</strong> is used you can also use <strong>lon</strong></em>
The parameters should be appended to the url in the form ?param1=value1&amp;param2=value2 (add as many as needed with &amp;param...)</p>
<table>
<thead>
<tr>
<th>Parameter</th>
<th>Variable name</th>
<th>Remarks</th>
</tr>
</thead>
<tbody>
<tr>
<td>Home</td>
<td>lat/lng</td>
<td>latitude &amp; longitude of home</td>
</tr>
<tr>
<td>Map Center</td>
<td>clat/clng</td>
<td>latitude &amp; longitude of map center</td>
</tr>
<tr>
<td>Grid center</td>
<td>glat/glng</td>
<td>latitude &amp; longitude of center cell of grid</td>
</tr>
<tr>
<td>Map type</td>
<td>type</td>
<td>map type, either map (default), hyb (for hybrid) or sat (for satellite)</td>
</tr>
<tr>
<td>Zoom level</td>
<td>zoom</td>
<td>zoom level</td>
</tr>
<tr>
<td>Date</td>
<td>date</td>
<td>date for which to show hashes, if omitted it defaults to current date</td>
</tr>
<tr>
<td>Single day</td>
<td>multi</td>
<td>use multi=false to show a single day, otherwise up to six following days will be shown</td>
</tr>
</tbody>
</table>';
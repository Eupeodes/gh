<?php
/*
 * file:		maxDate.php
 * author:		Marten Tacoma
 * contents:	get location name
 */

header('Content-Type: application/json');
echo json_encode(\model\GeoName::get(filter_input(INPUT_GET, 'lat'), filter_input(INPUT_GET, 'lng')));
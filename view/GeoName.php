<?php
namespace view;

class GeoName {
	public function view($url){
		$parts = explode('/', $url);
		header('Content-Type: application/json');
		
		echo json_encode(\model\GeoName::get(filter_input(INPUT_GET, 'lat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? $parts[2], filter_input(INPUT_GET, 'lng', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? $parts[3]));
	}
}

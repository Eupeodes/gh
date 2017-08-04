<?php

namespace view;

class Globals extends Base{
	public function view($url){
		$this->url = $url;
		$this->settings = [
			'home'=>'[0,0]',
			'zoom'=>8,
			'type'=>'map',
			'colorSet'=>'0ff',
			'controlsVisible'=>true,
			'unit'=>'km'
		];
		$this->readPath();
		$list = '<ol>';
		foreach(\model\GlobalHash::getClosest(json_decode($this->settings['home'])[1], json_decode($this->settings['home'])[0], 100) as $hash){
			$list .= '<li>'.$hash['date'].' <span class="km">'.round($hash['distance']).'km</span><span class="miles">'.round($hash['distance']*0.621371).'mi</span> '.$this->parseAzimuth($hash['direction']).'</li>';
		}
		$list .= '</ol>';
		require('../template/globals.tpl.php');
	}

	public function parseAzimuth($a, $asText = true){
		$directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW', 'N'];
		return $asText ? $directions[round($a/M_PI*8)] : round($a/M_PI*180);
	}
}
<?php

namespace view;

class Index {
	private $settings;
	private $date;
	private $maxDate;
	private $version;
	private $set = false;
	private $matches;
	private $url;

	public function __construct($url) {
		$this->url = $url;
		$this->settings = [
			'home'=>'[0,0]',
			'center'=>'[0,0]',
			'single'=>false,
			'zoom'=>8,
			'type'=>'map',
			'dayOf'=>'month',
			'colorSet'=>4
		];

		$this->maxDate = \model\Date::max();
		$this->date = date('Y-m-d');
		$this->version = file_get_contents('../version');

		if(!empty(filter_input_array(INPUT_GET))){
			$this->readGet();
		}
		
		if(!$this->set){
			$this->readPath();
		}
		
		$this->settings['center'] = $this->settings['home'];
	}
	
	public function get(){
		require('../template/index.tpl.php');
	}

	private function readGet(){
		$params = array('date', 'lng', 'lon', 'multi', 'zoom', 'type');
		foreach($params as $param){
			if(array_key_exists($param, filter_input_array(INPUT_GET))){
				switch($param){
					case 'multi':
						$this->settings['single'] = filter_input(INPUT_GET, 'multi', FILTER_SANITIZE_STRING) === 'false';
						break;
					case 'lng':
						$this->settings['home'] = '['.filter_input(INPUT_GET, 'lng', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).','.filter_input(INPUT_GET, 'lat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).']';
						break;
					case 'lon':
						$this->settings['home'] = '['.filter_input(INPUT_GET, 'lon', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).','.filter_input(INPUT_GET, 'lat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).']';
						break;
					case 'date':
						$date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING);
						list($y,$m,$d) = explode('-', $date);
						if(checkdate($m, $d, $y)){
							$this->date = $date;
						}
						break;
					default:
						$this->settings[$param] = filter_input(INPUT_GET, $param, FILTER_SANITIZE_ENCODED);
				}
				$this->set = true;
			}
		}
	}
	
	private function readPath(){
		if(preg_match('/^(\/index.php)?(\/|)?((?P<type>'.\lib\RegExp::mapType().')\/)?(?P<date>'.\lib\RegExp::date().'(\/(?P<single>s(ingle)?))?(\/(?P<home>'.\lib\RegExp::hash().')(\/(?P<zoom>[[0|1]?[0-9])(\/(?P<center>'.\lib\RegExp::hash().'))?)?)?)?(\/)?$/i', $this->url, $this->matches)){
			if(\lib\RegExp::partExists('type', $this->matches)){
				$this->settings['type'] = $this->matches['type'];
			}
			if(\lib\RegExp::partExists('date', $this->matches)){
				\lib\RegExp::parseDate($this->matches);
				list($y,$m,$d) = \lib\RegExp::parseDate($this->matches);
				$this->date($y, $m, $d);
			}
			if(\lib\RegExp::partExists('home', $this->matches)){
				$geohash = new \lib\external\GeoHash();
				list($lat, $lng) = $geohash->decode($this->matches['home']);
				$this->settings['home'] = '['.$lng.', '.$lat.']';
			}
			if(\lib\RegExp::partExists('zoom', $this->matches)){
				$this->settings['zoom'] = $this->matches['zoom'];
			}
			if(\lib\RegExp::partExists('single', $this->matches)){
				$this->settings['single'] = true;
			}
			//center is currently not being used
		} else {
			if(preg_match('/(\/|^)(?P<type>'. \lib\RegExp::mapType().')(\/|$)/i', $this->url, $this->matches)){
				$this->settings['type'] = $this->matches['type'];
			}
			if(preg_match('/(\/|^)'. \lib\RegExp::date().'(\/|$)/i', $this->url, $this->matches)){
				list($y,$m,$d) = \lib\RegExp::parseDate($this->matches);
				$this->date($y, $m, $d);
			}
			if(preg_match('/(\/|^)(?P<graticule>'.\lib\RegExp::graticule().')(\/|$)/i', $this->url, $this->matches)){
				if($this->matches['graticule'] === 'global'){
					$hash = new \view\Hash($this->date);
					$this->settings['home'] = '['.$hash->getInt($hash->output['global']->lng).'.5,'.$hash->getInt($hash->output['global']->lat).'.5]';
				} else {
					list($lat, $lng) = \lib\RegExp::parseGraticule($this->matches);
					$this->settings['home'] = '['.$lng.'.5, '.$lat.'.5]';
				}
			}

			if(preg_match('/(\/|^)s(ingle)?(\/|$)/i', $this->url, $this->matches)){
				$this->settings['single'] = true;
			}

			if(preg_match('/(\/|^)'.\lib\RegExp::zoom().'(\/|$)/i', $this->url, $this->matches)){
				$this->settings['zoom'] = $this->matches['zoom'];
			}
		}
	}
	
	private function date($y,$m,$d){
		if(checkdate($m,$d,$y) && $y.'-'.$m.'-'.$d <= $this->maxDate){
			$this->date = $y.'-'.$m.'-'.$d;
		}
	}
}

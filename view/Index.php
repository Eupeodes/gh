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
	private $showDisclaimer = true;
	
	public function __construct() {
		if(array_key_exists('disclaimer', $_COOKIE)){
			$this->showDisclaimer = false;
			setcookie("disclaimer", time(), time()+30*24*60*60, '/', '', true);
		}
	}

	public function view($url) {
		$this->url = $url;
		$this->settings = [
			'home'=>'[0,0]',
			'center'=>'[0,0]',
			'grid'=>'[0,0]',
			'single'=>false,
			'zoom'=>8,
			'type'=>'map',
			'dayOf'=>'month',
			'colorSet'=>'0ff',
			'controlsVisible'=>true
		];
		$cookie = filter_input(INPUT_COOKIE, 'config');
		if(!is_null($cookie)){
			$cookieData = json_decode($cookie);
			foreach($cookieData as $key=>$val){
				switch($key){
					case 'home':
					case 'center':
					case 'grid':
						$this->settings[$key] = '['.$val[0].','.$val[1].']';
						break;
					case 'single':
					case 'controlsVisible':
						$this->settings[$key] = ($val === 'true');
					default:
						$this->settings[$key] = $val;
				}
			}
		}

		$this->maxDate = \model\Date::max();
		$this->date = date('Y-m-d');
		$this->version = file_get_contents('../version');

		if(!empty(filter_input_array(INPUT_GET))){
			$this->readGet();
		}
		
		if(!$this->set){
			$this->readPath();
		}
		
		if($this->settings['home'] !== '[0,0]'){
			$this->settings['center'] = $this->settings['center'] === '[0,0]' ? $this->settings['home'] : $this->settings['center'];
			$this->settings['grid'] = $this->settings['grid'] === '[0,0]' ? $this->settings['home'] : $this->settings['grid'];
		}
		require('../template/index.tpl.php');
	}

	private function readGet(){
		$params = array('date', 'lng', 'lon', 'multi', 'zoom', 'type', 'glng', 'glon', 'clng', 'clon');
		foreach($params as $param){
			if(array_key_exists($param, filter_input_array(INPUT_GET))){
				switch($param){
					case 'multi':
						$this->settings['single'] = filter_input(INPUT_GET, 'multi', FILTER_SANITIZE_STRING) === 'false';
						break;
					case 'lng':
					case 'lon':
						$this->settings['home'] = '['.filter_input(INPUT_GET, $param, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).','.filter_input(INPUT_GET, 'lat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).']';
						break;
					case 'clng':
					case 'clon':
						$this->settings['center'] = '['.filter_input(INPUT_GET, $param, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).','.filter_input(INPUT_GET, 'clat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).']';
						break;
					case 'glng':
					case 'glon':
						$this->settings['grid'] = '['.filter_input(INPUT_GET, $param, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).','.filter_input(INPUT_GET, 'glat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).']';
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
			if(\lib\RegExp::partExists('center', $this->matches)){
				$geohash = new \lib\external\GeoHash();
				list($lat, $lng) = $geohash->decode($this->matches['center']);
				$this->settings['center'] = '['.$lng.', '.$lat.']';
			}
		} else {
			if(preg_match('/(\/|^)t:(?P<type>'. \lib\RegExp::mapType().')(\/|$)/i', $this->url, $this->matches)){
				$this->settings['type'] = $this->matches['type'];
			}
			if(preg_match('/(\/|^)'. \lib\RegExp::date().'(\/|$)/i', $this->url, $this->matches)){
				list($y,$m,$d) = \lib\RegExp::parseDate($this->matches);
				$this->date($y, $m, $d);
			}
			
			if(preg_match('/(\/|^)g(lobal)?(\/|$)/i', $this->url)){
				$hash = new \view\Hash();
				$data = $hash->getGlobal($this->date);
				$this->settings['home'] = '['.$hash->getInt($data['global']->lng).'.5,'.$hash->getInt($data['global']->lat).'.5]';
				$this->settings['center'] = $this->settings['home'];
				$this->settings['grid'] = $this->settings['home'];
			} else if(preg_match_all('/(?=(\/|^)(?P<graticule>(?P<w>[cgh]{0,}:)?'.\lib\RegExp::graticule().')(\/|$))/i', $this->url, $this->matches, PREG_SET_ORDER)){
				foreach($this->matches as $match){
					list($lat, $lng) = \lib\RegExp::parseGraticule($match);
					$lat .= strpos($lat, '.') === false ? '.5' : '';
					$lng .= strpos($lng, '.') === false ? '.5' : '';
					if($match['w'] === ''){
						$match['w'] = 'h';
					}
					if(strpos($match['w'], 'c') !== false){
						$this->settings['center'] = '['.$lng.', '.$lat.']';
					}
					if(strpos($match['w'], 'g') !== false){
						$this->settings['grid'] = '['.$lng.', '.$lat.']';
					}
					if(strpos($match['w'], 'h') !== false){
						$this->settings['home'] = '['.$lng.', '.$lat.']';
					}
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

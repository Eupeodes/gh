<?php

namespace view;

class Map extends Base{
	protected $settings;
	protected $date;
	protected $maxDate;
	protected $version;
	protected $set = false;
	protected $url;
	protected $showDisclaimer = true;
	protected $centerSet = false;
	protected $gridSet = false;
	protected $homeSet = false;
	protected $cookie = [];
	
	public function __construct() {
		if(array_key_exists('disclaimer', $_COOKIE) && $_COOKIE['disclaimer'] > 1533883501){
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
			'controlsVisible'=>true,
			'resetSetHomeGrid'=>true,
			'setHomeGrid' => null
		];
		$cookie = filter_input(INPUT_COOKIE, 'config');
		if(!is_null($cookie)){
			$this->parseCookie($cookie);
		}

		$this->maxDate = \model\Date::max();
		
		$this->version = json_decode(file_get_contents('../package.json'))->version;

		if(!empty(filter_input_array(INPUT_GET))){
			$this->readGet();
		}
		
		if(!$this->set){
			$this->readPath();
		}
		
		if($this->homeSet || $this->centerSet || $this->gridSet){
			$this->cookie = [];
		}

		$this->setLocation('home');
		$this->setLocation('center');
		$this->setLocation('grid');

		$this->settings['setHomeGrid'] = $this->setSetHomeGrid();

		require('../template/map.tpl.php');
	}

	private function setSetHomeGrid(){
		if(!is_null($this->settings['setHomeGrid'])){
			return $this->settings['setHomeGrid'];
		}
		if($this->settings['home'] !== '[0,0]'){
			return 'nothing';
		} else {
			if($this->settings['home'] == $this->settings['grid']){
				return 'both';
			} else {
				return 'home';
			}
		}
	}

	private function parseCookie($cookie){
		$cookieData = json_decode($cookie);
		foreach($cookieData as $key=>$val){
			switch($key){
				case 'home':
				case 'center':
				case 'grid':
					$this->cookie[$key] = '['.$val[0].','.$val[1].']';
					break;
				case 'single':
				case 'controlsVisible':
				case 'resetSetHomeGrid':
					$this->settings[$key] = ($val === 'true');
				default:
					$this->settings[$key] = $val;
			}
		}
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
						$this->homeSet = true;
						break;
					case 'clng':
					case 'clon':
						$this->settings['center'] = '['.filter_input(INPUT_GET, $param, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).','.filter_input(INPUT_GET, 'clat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).']';
						$this->centerSet = true;
						break;
					case 'glng':
					case 'glon':
						$this->settings['grid'] = '['.filter_input(INPUT_GET, $param, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).','.filter_input(INPUT_GET, 'glat', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION).']';
						$this->gridSet = true;
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
	
	private function setLocation($key){
		$bool = $key.'Set';
		if(!$this->$bool){
			if (array_key_exists($key, $this->cookie)){
				$this->settings[$key] = $this->cookie[$key];
				if($key === 'home'){
					$this->homeSet = true;
				}
			} elseif ($this->homeSet){
				$this->settings[$key] = $this->settings['home'];
			}
		}
	}
}

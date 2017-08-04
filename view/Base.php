<?php

namespace view;

class Base{
	protected $matches;
	protected function readPath(){
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
				$this->homeSet = true;
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
				$this->centerSet = true;
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
				$data = $hash->getHash($this->date);
				$this->settings['home'] = '['.$hash->getInt($data['global']->lng).'.5,'.$hash->getInt($data['global']->lat).'.5]';
				$this->settings['center'] = $this->settings['home'];
				$this->settings['grid'] = $this->settings['home'];
				$this->homeSet = true;
				$this->centerSet = true;
				$this->gridSet = true;
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
						$this->centerSet = true;
					}
					if(strpos($match['w'], 'g') !== false){
						$this->settings['grid'] = '['.$lng.', '.$lat.']';
						$this->gridSet = true;
					}
					if(strpos($match['w'], 'h') !== false){
						$this->settings['home'] = '['.$lng.', '.$lat.']';
						$this->homeSet = true;
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

	protected function date($y,$m,$d){
		if(checkdate($m,$d,$y) && $y.'-'.$m.'-'.$d <= $this->maxDate){
			$this->date = $y.'-'.$m.'-'.$d;
		}
	}
	
}
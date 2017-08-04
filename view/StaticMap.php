<?php

namespace view;

class StaticMap {
	protected $cacheFile;
	
	public function view($url){
		if(preg_match('/^\/staticMap\/((?P<maptype>[roadmap|satelite])\/)?'.\lib\RegExp::date().'\/(?P<graticule>'.\lib\RegExp::graticule().')'.'$/i', $url, $this->matches)){
			$y = $this->matches['y'];
			$m = $this->matches['m'];
			$d = $this->matches['d'];
			if(checkdate($m,$d,$y)){
				$this->date = $y.'-'.$m.'-'.$d;
				$this->graticule = $this->matches['graticule'] === 'global' ? ['global']: \lib\Regexp::parseGraticule($this->matches);
				$this->mapType = empty($this->matches['maptype']) ? 'roadmap' : $this->matches['maptype'];
				$this->cacheFile = str_replace('-', '', $this->date).'_'.implode('_', $this->graticule).'_'.$this->mapType.'.png';
				if(!file_exists('../cache/'.$this->cacheFile)){
					$img = $this->makeMap();
				} else {
					$img = file_get_contents('../cache/'.$this->cacheFile);
				}
				$this->displayMap($img);
			} else {
				\lib\Error::send(404, 'This date is not a valid date');
			}
		} else {
			\lib\Error::send(400, 'No valid url');
		}
	}

	private function makeMap(){
		$hash = new \view\Hash();
		$hash = $hash->getHash($this->date);
		if($this->graticule[0] === 'global'){
			if(is_null($hash['global'])){
				$this->notAvailable();
			}
			$lat = $hash['global']->lat;
			$lng = $hash['global']->lng;
			$zoom = 5;
		} else {
			$offset = $this->graticule[1] > -30 ? $hash['east'] : $hash['west'];
			if(is_null($offset)){
				$this->notAvailable();
			}
			$lat = $this->graticule[0].substr($offset->lat, 1);
			$lng = $this->graticule[1].substr($offset->lng, 1);
			if($lat < 60) {
				$zoom = 8;
			} elseif ($lat < 75) {
				$zoom = 7;
			} elseif ($lat < 82) {
				$zoom = 6;
			} elseif ($lat < 86) {
				$zoom = 5;
			} elseif ($lat < 88) {
				$zoom = 4;
			} elseif($lat >= 88) {
				$zoom = 3;
			}
		}
		$clat = intval($lat).".5";
		$clng = intval($lng).".5";
		$url = "http://maps.googleapis.com/maps/api/staticmap?center=$clat,$clng";
		$url .= "&size=300x400";
		$url .= "&sensor=false";
		$url .= "&zoom=$zoom";
		$url .= "&path=color:0xff0000ff|weight:3|".floor($lat).",".floor($lng)."|".ceil($lat).",".floor($lng)."|".ceil($lat).",".ceil($lng)."|".floor($lat).",".ceil($lng)."|".floor($lat).",".floor($lng);
		$url .= "&markers=color:blue%7C$lat,$lng";
		$url .= "&key=AIzaSyDZpNi_G0_KqacSGUWW6a76EvIZgvFNiVk";
		if($this->mapType === 'satellite'){
			$url .= '&maptype=satellite';
		}
		$img = file_get_contents($url);
		file_put_contents('../cache/'.$this->cacheFile, $img);
		return $img;
	}
	
	private function displayMap($img){
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime('../cache/'.$this->cacheFile)).' GMT', true, 200);
		header('Content-Length: '.filesize('../cache/'.$this->cacheFile));
		header('Content-Type: image/png');
		echo $img;
	}

	private function notAvailable(){
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header('Content-Type: image/png');
		readfile('../public/img/fail.png');
		die();
	}
}
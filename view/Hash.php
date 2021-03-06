<?php

namespace view;

class Hash {
	private $wk;
	private $date;
	private $ext;
	private $graticule;
	private $lat;
	private $lng;
	private $global;
	private $output;
	private $matches;
	
	public function view($url){
		if($url === '/hash/all'){
			$this->date = new \DateTime('1928-10-01');
			$this->getMultiple(99999);
		} elseif(preg_match('/^\/hash(?P<wk>\/wk)?\/'.\lib\RegExp::date().'(\/(?P<graticule>'.\lib\RegExp::graticule().'))?'.\lib\RegExp::ext(true).'$/i', $url, $this->matches)){
			$y = $this->matches['y'];
			$m = $this->matches['m'];
			$d = $this->matches['d'];
			if(checkdate($m,$d,$y)){
				$this->date = new \DateTime($y.'-'.$m.'-'.$d);
				$this->ext = array_key_exists('ext', $this->matches) ? $this->matches['ext'] : null;
				$this->wk = (strlen($this->matches['wk']) > 0);
				$this->get();
			} else {
				\lib\Error::send(404, 'This date is not a valid date');
			}
		} else {
			\lib\Error::send(400, 'No valid url');
		}
	}
	
	public function cron($date){
		$this->date = new \DateTime($date);
		return $this->getMultiple(null,null);
	}
	
	private function get(){
		if(\lib\RegExp::partExists('graticule', $this->matches)){
			$this->graticule = $this->matches['graticule'];
			$this->global = ($this->matches['graticule'] === 'global');
			if(!$this->global){
				list($this->lat,$this->lng) = \lib\RegExp::parseGraticule($this->matches);
			}
			$this->getLocation();
		} elseif ($this->ext === 'gpx'){
			\lib\Error::send(400, 'Gpx only valid if graticule is specified');
		} elseif(strlen($this->matches['wk']) === 0){
			$this->getSingle();
		} else {
			$this->getMultiple();
		}
	}
	
	public function getHash($date){
		$this->date = new \DateTime($date);
		$this->doCalc();
		return $this->output;
	}

	private function getSingle(){
		$this->doCalc();
		if(is_null($this->output['west'])){
			\lib\Cache::tillDow();
		} else {
			\lib\Cache::permanent();
		}
		switch($this->ext){
			case 'json':
				header('Content-Type: application/json');
				echo json_encode($this->output);
				break;
			default:
				header('Content-Type: text/plain');
				echo 'Date: '.$this->output['date']."\n"
					. ($this->output['west'] === null ? 'West not available' : 'Western offset (Non-W30): '.$this->output['west']->lat.', '.$this->output['west']->lng)."\n"
					. ($this->output['east'] === null ? 'East not available' : 'Eastern offset (W30): '.$this->output['east']->lat.', '.$this->output['east']->lng)."\n"
					. ($this->output['global'] === null ? 'Global not available' : 'Global hash: '.$this->output['global']->lat.', '.$this->output['global']->lng);
		}
	}
	
	private function getMultiple($max = 7, $outputFormat='json'){
		$output = [];
		$n = 0;
		$int = new \DateInterval('P1D');
		while(true){
			$this->doCalc();
			if(is_null($this->output['east'])){
				break;
			}
			$output[] = $this->output;
			$n++;
			if(!is_null($max) && $n >= $max){
				break;
			}
			$this->date->add($int);
		}
		if(is_null($this->output['west'])){
			\lib\Cache::tillDow();
		} else {
			\lib\Cache::permanent();
		}
		if(is_null($outputFormat)){
			return $output;
		} else {
			switch($outputFormat){
				case 'json':
				default:
					header('Content-Type: application/json');
					echo json_encode($output);
			}
		}
	}
	
	private function getLocation(){
		if($this->wk){
			\lib\Error::send(404, 'Week view for GPX is not (yet) implemented');
		} else {
			$this->doCalc();
			if($this->global){
				if(is_null($this->output['global'])){
					\lib\Error::send(404, 'Data not yet available');
				}
				$this->lat = $this->output['global']->lat;
				$this->lng = $this->output['global']->lng;
				$filename = 'global';
			} else {
				$hashData = ($this->lng> -30) ? $this->output['east'] : $this->output['west'];
				if(is_null($hashData)){
					\lib\Error::send(404, 'Data not yet available');
				}
				$filename = $this->lat.'_'.$this->lng;
				$this->lat .= substr($hashData->lat,1);
				$this->lng .= substr($hashData->lng,1);
				
			}
			switch($this->ext){
				case 'gpx':
					\lib\Cache::permanent();
					header('Content-type: text/xml');
					header('Content-type: application/force-download;charset=utf-8');

					header('Content-Disposition: attachment; filename="geohash_'.$this->date->format('Y-m-d').'_'.$filename.'.gpx"');
					echo '<?xml version="1.0"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" version="1.1" creator="geohashing.info">
<metadata>
	<name>Geohash '.$this->date->format('Y-m-d').'</name>
</metadata>
<wpt lat="'.$this->lat.'" lon="'.$this->lng.'">
	<name>GH '.$this->date->format('Y-m-d').' '.$this->graticule.'</name>
</wpt>
</gpx>';
					break;
				case 'json':
				default:
					header('Content-Type: application/json');
					echo json_encode(['date'=>$this->date->format('Y-m-d'), 'lat'=>$this->lat, 'lng'=>$this->lng]);
			}
		}
	}
	
	private function doCalc(){
		$this->output = ['date'=>$this->date->format('Y-m-d'), 'east'=>null, 'west'=>null, 'global'=>null];
		$dow = \model\Dow::get($this->date);

		$dateTimeDayBefore = clone $this->date;
		$dateTimeDayBefore->modify('-1 day');
		$dowDayBefore = \model\Dow::get($dateTimeDayBefore);

		if($dow !== false){
			$this->output['west'] = new \model\Hash($this->date, $dow);
		}
		if($this->date->format('Y-m-d') < '2008-05-27'){
			$this->output['east'] = $this->output['west'];
			if($dowDayBefore !== false){
				$this->output['global'] = new \model\Hash($this->date, $dowDayBefore, true);
			}
		} else {
			if($dowDayBefore !== false){
				$this->output['east'] = new \model\Hash($this->date, $dowDayBefore);
				$this->output['global'] = new \model\Hash($this->date, $dowDayBefore, true);
			}
		}
	}
	
	//needed for global
	public function getInt($hash){
		return substr($hash, 0, strpos($hash, '.'));
	}
}

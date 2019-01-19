<?php

namespace view;

class DataList {
	private $dateLimits;
	private $url;

	public function __construct(){
		$this->dateLimits = ['min'=>\model\Date::min(), 'max'=>\model\Date::max()];
	}

	public function view($url) {
		$this->url = $url;
		if(preg_match('/^(\/(datalist)?)?(\/'.\lib\RegExp::date(true,true).')?'.\lib\RegExp::ext(true).'$/', $url, $matches)){
			if(\lib\RegExp::partExists('d', $matches)){
				if(checkdate($matches['m'], $matches['d'], $matches['y'])){
					$dt_start = new \DateTime($matches['y'].'-'.$matches['m'].'-'.$matches['d']);
					$dt_end = $dt_start;
				} else {
					\lib\Error::send(404, 'Invalid date');
				}
			} elseif (\lib\RegExp::partExists('m', $matches)){
				if(checkdate($matches['m'], 1, $matches['y'])){
					$dt_start = new \DateTime($matches['y'].'-'.$matches['m'].'-01');
					$dt_end = new \DateTime($matches['y'].'-'.$matches['m'].'-'.cal_days_in_month(CAL_GREGORIAN, $matches['m'], $matches['y']));
				} else {
					\lib\Error::send(404, 'Invalid month');
				}
			} elseif (\lib\RegExp::partExists('y', $matches)) {
				$dt_start = new \DateTime($matches['y'].'-01-01');
				$dt_end = new \DateTime($matches['y'].'-12-31');
			} else {
				$dt_start = new \DateTime($this->dateLimits['min']);
				$dt_end = new \DateTime($this->dateLimits['max']);
			}
			if($dt_start > new \DateTime($this->dateLimits['max'])){
				\lib\Error::send(404, 'This date is not yet available');
			} elseif($dt_end < new \DateTime($this->dateLimits['min'])){
				\lib\Error::send(404, 'Dates before '.$this->dateLimits['min'].' are not available');
			} else {
				$this->displayList($matches['y'] ?? null,$matches['m'] ?? null);
			}
		} else {
			\lib\Error::send(400, 'No valid url, use <u>/[&lt;yyyy&gt;[/&lt;mm&gt;[/&lt;dd&gt;]][.(csv|json)]]</u>');
		}
	}
	
	private function displayList($year=null, $month=null){
		echo '<html><head><title>Geohashing Data</title></head><body><p>Here you can download historic dow openings and geohashes, data since <a href="http://xkcd.com/426">xkcd #426</a> (21 May 2008) is reliable according to crox, before that it is less reliable.</p>';
		$minyear = substr($this->dateLimits['min'],0,4);
		$maxyear = substr($this->dateLimits['max'],0,4);
		$minmonth = substr($this->dateLimits['min'],5,2);
		$maxmonth = substr($this->dateLimits['max'],5,2);
		$minday = substr($this->dateLimits['min'],-2);
		$maxday = substr($this->dateLimits['max'],-2);
			
		$firstHoliday = \model\Holiday::firstYear();			
		$lastHoliday = \model\Holiday::lastYear();			
		if(is_null($year)){
			echo 'Get full dow archive as <a href="/dow.json">json</a> or <a href="/dow.csv">csv</a>.<br/>Get all dow holidays from '.$firstHoliday.' to '.$lastHoliday.' as <a href="/holiday">html</a>, <a href="/holiday.json">json</a> or <a href="/holiday.csv">csv</a>.<ul>';
			for($i=$lastHoliday;$i>$maxyear;$i--){
				echo '<li><strong>'.$i.':</strong> Dow holidays as <a href="/holiday/'.$i.'">html</a>, <a href="/holiday/'.$i.'.json">json</a> or <a href="/holiday/'.$i.'.csv">csv</a></li>';
			}
			for($i=$maxyear;$i>=$minyear;$i--){
				echo '<li><strong>'.$i.':</strong> <a href="/'.$i.'">List months</a>'
					. ' | Dows as <a href="/dow/'.$i.'.json">json</a> or <a href="/dow/'.$i.'.csv">csv</a>'
					. ($i>=$firstHoliday ? ' | Dow holidays as <a href="/holiday/'.$i.'">html</a>, <a href="/holiday/'.$i.'.json">json</a> or <a href="/holiday/'.$i.'.csv">csv</a>' : '')
					. '</li>';
			}
			echo '</ul>';
		} elseif(is_null($month)){
			$min = $year == $minyear ? $minmonth : 1;
			$max = $year == $maxyear ? $maxmonth : 12;
			echo '<a href="/">Back</a> | Get all openings from '.$year.' as <a href="/dow/'.$year.'.json">json</a> or <a href="/dow/'.$year.'.csv">csv</a>'
				. ($year>=$firstHoliday ? ' | Dow holidays as <a href="/holiday/'.$year.'">html</a>, <a href="/holiday/'.$year.'.json">json</a> or <a href="/holiday/'.$year.'.csv">csv</a>' : '')
				.'<ul>';
			for($i=$max;$i>=$min;$i--){
				echo '<li><strong>'.$year.'-'.str_pad($i, 2, '0', STR_PAD_LEFT).':</strong> <a href="'.$this->url.'/'.str_pad($i, 2, '0', STR_PAD_LEFT).'">List days</a> | Dows as <a href="/dow/'.$year.'/'.str_pad($i, 2, '0', STR_PAD_LEFT).'.json">json</a> or <a href="/dow/'.$year.'/'.str_pad($i, 2, '0', STR_PAD_LEFT).'.csv">csv</a></li>';
			}
		} else {
			$min = $year == $minyear && $month == $minmonth ? $minday : 1;
			$max = $year == $maxyear && $month == $maxmonth ? $maxday : cal_days_in_month(CAL_GREGORIAN, $month, $year);
			echo '<a href="'.substr($this->url, 0, -strlen($month)-1).'">Back</a> | Get all openings from '.$year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT).' as <a href="/dow/'.$year.'/'.$month.'.json">json</a> or <a href="/dow/'.$year.'/'.$month.'.csv">csv</a><br/><br/>
			GPX of hash is only available with graticule in url, those can be retreived from the map<ul>';
			for($i=$max;$i>=$min;$i--){
				echo '<li><strong>'.$year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($i, 2, '0', STR_PAD_LEFT).':</strong> ';
				echo ($year == $maxyear && $month == $maxmonth && $i == $maxday) ? '<em>Dow opening not yet available</em>' :'Dow as <a href="/dow/'.$year.'/'.$month.'/'.str_pad($i, 2, '0', STR_PAD_LEFT).'">plain text</a>,  <a href="/dow/'.$year.'/'.$month.'/'.str_pad($i, 2, '0', STR_PAD_LEFT).'.json">json</a> or <a href="/dow/'.$year.'/'.$month.'/'.str_pad($i, 2, '0', STR_PAD_LEFT).'.csv">csv</a>';
				echo ' | Hash as <a href="/hash/'.$year.'/'.$month.'/'.str_pad($i, 2, '0', STR_PAD_LEFT).'">plain text</a> or <a href="/hash/'.$year.'/'.$month.'/'.str_pad($i, 2, '0', STR_PAD_LEFT).'.json">json</a>.</li>';
			}
		}
	}
}

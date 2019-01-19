<?php

namespace view;

class Holiday{
	private $dateLimits;
	private $url;
	private $model;

	public function __construct(){
		$this->dateLimits = ['min'=>\model\Holiday::firstYear(), 'max'=>\model\Holiday::lastYear()];
	}

	public function view($url) {
		$this->url = $url;
		if(preg_match('/^\/holiday(\/'.\lib\RegExp::$date_y.')?'.\lib\RegExp::ext(true).'$/', $url, $matches)){
			$year = \lib\RegExp::partExists('y', $matches) ? $matches['y'] : null;
			if(!is_null($year)){
				if($year < $this->dateLimits['min']){
					\lib\Error::send(404, 'This year is not available, the first year currently available is '.$this->dateLimits['min']);
				} elseif($year > $this->dateLimits['max']){
					\lib\Error::send(404, 'This year is not available, the last year currently available is '.$this->dateLimits['max']);
				}	
			}
			$ext = \lib\RegExp::partExists('ext', $matches) ? $matches['ext'] : null;
			$this->display($year, $ext);
		} else {
			\lib\Error::send(400, 'No valid url, use <u>/holiday[/&lt;yyyy&gt;][.(csv|json)]</u>');
		}
	}
	
	private function display($year, $ext){
		$data = \model\Holiday::getHolidays($year);
	
		switch($ext){
			case 'json':
				header('Content-Type: application/json');
				echo json_encode($data);
				break;

			case 'csv':
				header('Content-Type: text/csv');
				echo '"Date","Holiday"'."\r\n";
				foreach($data as $holiday){
					echo $holiday['date'].',"'.$holiday['holiday']."\"\r\n";
				}
				break;
			
			default:
				echo '<a href="/">Back</a> | List of dow holiday\'s'.(is_null($year) ? ' since '.$this->dateLimits['min'] : ' in '.$year)
					. ' | Download as <a href="holiday'.(is_null($year) ? '' : '/'.$year).'.json">json</a>' 
					. ' or <a href="holiday'.(is_null($year) ? '' : '/'.$year).'.csv">csv</a>'
					. '<ul>';
				foreach($data as $holiday){
					echo '<li>'.$holiday['date'].' | '.$holiday['holiday'].'</li>';
				}
				echo '</ul>';
		}
	}
}

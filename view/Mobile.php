<?php

namespace view;

class Mobile extends Base {
	public function view($url){
		$date = date('Y-m-d');
		if(preg_match('/((\/|^)(?P<date>'.\lib\RegExp::date().')(\/|$))/i', $url, $this->matches)){
			$date = $this->matches['date'];
		}
		if(preg_match('/((\/|^)(?P<graticule>'.\lib\RegExp::graticule().')(\/|$))/i', $url, $this->matches)){
			$graticule = $this->matches['graticule'];
		}
		require('../template/mobile.tpl.php');
	}
}
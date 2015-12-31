<?php

namespace view;

class Hash {
	private $east;
	private $west;
	private $global;
	public $output;
		
	public function __construct($date) {
		$this->calc($date);
		
		$this->output = array(
			'date' => $date,
			'west' => $this->west,
			'east' => $this->east,
			'global' => $this->global
		);
	}
	
	private function calc($date){
		$dateTime = new \DateTime($date);
		$dow = \model\Dow::get($dateTime);

		$dateTimeDayBefore = new \DateTime($date);
		$dateTimeDayBefore->modify('-1 day');
		$dowDayBefore = \model\Dow::get($dateTimeDayBefore);

		if($dow !== false){
			$this->west = new \model\Hash($dateTime, $dow);
		}
		if($date < 2008-05-27){
			$this->east = $this->west;
			if($dowDayBefore !== false){
				$this->global = new \model\Hash($dateTime, $dowDayBefore, true);
			}
		} else {
			if($dowDayBefore !== false){
				$this->east = new \model\Hash($dateTime, $dowDayBefore);
				$this->global = new \model\Hash($dateTime, $dowDayBefore, true);
			}
		}
	}
	
	public function getInt($hash){
		return substr($hash, 0, strpos($hash, '.'));
	}
}

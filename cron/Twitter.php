<?php

namespace cron;

use \PDO;

class Twitter {
	private $db;
	private $hash;
	private $connection;
	
	private $mastodon;
	
	private $table = 'tweets';
	
	public $short_url_length;
	
	public function __construct() {
		$this->connection = new \lib\external\Twitter(
				\config::$keys['twitter']['consumerKey'],
				\config::$keys['twitter']['consumerSecret'],
				\config::$keys['twitter']['accessToken'],
				\config::$keys['twitter']['accessTokenSecret']
		);
		
		$this->mastodon = new \lib\Mastodon(
			\config::$keys['mastodon']['host'],
			\config::$keys['mastodon']['key'],
		);
		$this->db = \lib\Db::getInstance();
		$this->shortUrlLength();
	}
	
	private function shortUrlLength(){
		if(date('i') == 29 && false){
			$data = $this->connection->request("help/configuration.json", "GET");
			$this->short_url_length = $data->short_url_length;
			$req = $this->db->prepare('UPDATE conf SET val=:val WHERE field=\'short_url_length\'');
			$req->bindParam(':val', $this->short_url_length, PDO::PARAM_INT);
			$req->execute();
		} else {
			$req = $this->db->prepare('SELECT val FROM conf WHERE field=\'short_url_length\'');
			$req->execute();
			$this->short_url_length = $req->fetch(PDO::FETCH_OBJ)->val;
		}
	}
	
	public function dailyCoords($date){
		$base_dir = BASE_DIR;
		$this->hash = new \view\Hash();
		$res = $this->hash->cron($date);
		
		$maxLength = 80 - 2 * $this->short_url_length;
		foreach($res as $key=>$hash){
			if($key > 0){
				$msg = 'Eastern (W30) offset '.$hash['date'].': '.round($hash['east']->lat,5).', '.round($hash['east']->lng,5).' https://geohashing.info/'.  str_replace('-', '', $hash['date']).'/s #geohashing';
				$this->queue($msg);
				
				$msg = 'Globalhash '.$hash['date'].': '.round($hash['global']->lat,5).', '.round($hash['global']->lng,5).', '.\model\GeoName::get($hash['global']->lat, $hash['global']->lng, $maxLength)->geoName.' https://geohashing.info/'.  str_replace('-', '', $hash['date']).'/global #geohashing';
				// copy('http://maps.googleapis.com/maps/api/staticmap?center=0,0&size=640x640&sensor=false&zoom=1&markers=color:blue%7C'.$hash['global']->lat.','.$hash['global']->lng.'&key=AIzaSyDZpNi_G0_KqacSGUWW6a76EvIZgvFNiVk&maptype=satellite', $base_dir.'/cache/global_'.str_replace('-', '', $hash['date']).'.png');
				copy('https://staticmap.test.eupeodes.nl/?lat='.$hash['global']->lat.'&lon='.$hash['global']->lng.'&val='.substr($hash['date'], -2).'', $base_dir.'/cache/global_'.str_replace('-', '', $hash['date']).'.png');
				$this->queue($msg, $base_dir.'/cache/global_'.str_replace('-', '', $hash['date']).'.png');
				//\model\GlobalHash::save($hash['date'], $hash['global']->lat, $hash['global']->lng);
			}
			if(!is_null($hash['west'])){
				$msg = 'Western (Non-W30) offset '.$hash['date'].': '.round($hash['west']->lat,5).', '.round($hash['west']->lng,5).' https://geohashing.info/'.  str_replace('-', '', $hash['date']).'/s #geohashing';
				$this->queue($msg);
			}
		}
		
	}

	public function queue($msg, $img = null, $status = 2){
		if(is_null($img)){
			$query = 'INSERT INTO '.$this->table.' (source, status, tweet) VALUES (\'bot\', :status, :msg)';
			$req = $this->db->prepare($query);
			$req->bindParam(':msg', $msg, PDO::PARAM_STR);
			$req->bindParam(':status', $status, PDO::PARAM_STR);
		} else {
			$query = 'INSERT INTO '.$this->table.' (source, status, tweet, img) VALUES (\'bot\', :status, :msg, :img)';
			$req = $this->db->prepare($query);
			$req->bindParam(':msg', $msg, PDO::PARAM_STR);
			$req->bindParam(':img', $img, PDO::PARAM_STR);
			$req->bindParam(':status', $status, PDO::PARAM_STR);
		}
		$req->execute();
	}
	
	public function sendQueue(){
		$query = 'SELECT * FROM '.$this->table.' WHERE status=2 ORDER BY queuedate,id';
		$req = $this->db->prepare($query);
		$req->execute();
		
		//$r1 = $this->db->prepare('DELETE FROM '.$this->table.' WHERE id=:id');
		$r1 = $this->db->prepare('UPDATE '.$this->table.' SET status=5 WHERE id=:id');
		$r2 = $this->db->prepare('UPDATE '.$this->table.' SET status=4 WHERE id=:id');
		$r3 = $this->db->prepare('INSERT INTO '.$this->table.'_errors (tweet_id, error) VALUES (:id, :error)');
		while($res = $req->fetch(PDO::FETCH_OBJ)){
			try {
				if(is_null($res->img) || !file_exists($res->img)){
					$this->connection->send($res->tweet);
					$this->mastodon->post($res->tweet);
				} else {
					$this->connection->send($res->tweet, $res->img);
					$this->mastodon->post(
						$res->tweet,
						[
							'path' => $res->img,
							'description' => 'Visual representation of the location in this toot'
						]
					);
					unlink($res->img);
				}
				$r1->execute([':id'=>$res->id]);
			} catch (\TwitterException $t){
				$r2->execute([':id'=>$res->id]);
				$r3->execute([':id'=>$res->id, ':error'=>$t]);
			}
		}
	}
}

<?php

namespace cron;

use \PDO;

class Push {
	private $db;
	private $hash;
	private $twitter;
	
	private $mastodon;
	
	private $table = 'push';
	
	public $short_url_length;
	
	public function __construct() {
		$this->twitter = new \lib\external\Twitter(
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
			$data = $this->twitter->request("help/configuration.json", "GET");
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
			$query = 'INSERT INTO '.$this->table.' (source, status_twitter, status_mastodon, message) VALUES (\'bot\', :status_twitter, :status_mastodon, :msg)';
			$req = $this->db->prepare($query);
		} else {
			$query = 'INSERT INTO '.$this->table.' (source, status_twitter, status_mastodon, message, img, status_img) VALUES (\'bot\', :status_twitter, :status_mastodon, :msg, :img, 1)';
			$req = $this->db->prepare($query);
			$req->bindParam(':img', $img, PDO::PARAM_STR);
		}
		$req->bindParam(':msg', $msg, PDO::PARAM_STR);
		$req->bindParam(':status_twitter', $status, PDO::PARAM_STR);
		$req->bindParam(':status_mastodon', $status, PDO::PARAM_STR);
		$req->execute();
	}
	
	public function sendQueue(){
		$this->sendQueueMastodon();
		$this->sendQueueTwitter();
		$this->cleanup();
	}
	
	public function sendQueueTwitter(){
		$query = 'SELECT * FROM '.$this->table.' WHERE status_twitter=2 ORDER BY queuedate,id';
		$req = $this->db->prepare($query);
		$req->execute();
		
		//$r1 = $this->db->prepare('DELETE FROM '.$this->table.' WHERE id=:id');
		$r1 = $this->db->prepare('UPDATE '.$this->table.' SET status_twitter=5, status_img=status_img+1 WHERE id=:id');
		$r2 = $this->db->prepare('UPDATE '.$this->table.' SET status_twitter=4 WHERE id=:id');
		$r3 = $this->db->prepare('INSERT INTO '.$this->table.'_errors (tweet_id, error) VALUES (:id, :error)');
		while($res = $req->fetch(PDO::FETCH_OBJ)){
			try {
				if(is_null($res->img) || !file_exists($res->img)){
					$this->twitter->send($res->message);
				} else {
					$this->twitter->send($res->message, $res->img);
				}
				$r1->execute([':id'=>$res->id]);
			} catch (\TwitterException $t){
				$r2->execute([':id'=>$res->id]);
				$r3->execute([':id'=>$res->id, ':error'=>$t]);
			}
		}
	}

	public function sendQueueMastodon(){
		$query = 'SELECT * FROM '.$this->table.' WHERE status_mastodon=2 ORDER BY queuedate,id';
		$req = $this->db->prepare($query);
		$req->execute();
		
		//$r1 = $this->db->prepare('DELETE FROM '.$this->table.' WHERE id=:id');
		$r1 = $this->db->prepare('UPDATE '.$this->table.' SET status_mastodon=5, status_img=status_img+1 WHERE id=:id');
		$r2 = $this->db->prepare('UPDATE '.$this->table.' SET status_mastodon=4 WHERE id=:id');
		while($res = $req->fetch(PDO::FETCH_OBJ)){
			try {
				if(is_null($res->img) || !file_exists($res->img)){
					$this->mastodon->post($res->message);
				} else {
					$this->mastodon->post(
						$res->message,
						[
							'path' => $res->img,
							'description' => 'Visual representation of the location in this toot'
						]
					);
				}
				$r1->execute([':id'=>$res->id]);
			} catch (\TwitterException $t){
				$r2->execute([':id'=>$res->id]);
			}
		}
	}
	
	
	public function cleanup(){
		$query = 'SELECT * FROM '.$this->table.' WHERE status_img=3';
		$req = $this->db->prepare($query);
		$req->execute();
		
		$r = $this->db->prepare('UPDATE '.$this->table.' SET status_img=4 WHERE id=:id');
		while($res = $req->fetch(PDO::FETCH_OBJ)){
			unlink($res->img);
			$r->execute([':id'=>$res->id]);
		}
	}

}

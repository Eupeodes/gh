<?php

namespace cron;

use \PDO;

class Wiki{
	private $db;
	private $arr;
	private $el;
	private $title;
	private $id;
	private $state;
	private $user;
	private $type;
	private $count;
	private $len;
	
	private $twitter;
	private $ircBot;
	
	public function __construct(){
		$this->db = \lib\Db::getInstance();
		$this->twitter = new \cron\Twitter();
		$this->ircBot = new \cron\IrcBot();
	}
	
	public function cron(){
		$this->watch();
		if($this->get()){
			if($this->count > 0){
				$this->loop();
			}
		}
	}
	private function get(){
		$req = $this->db->prepare('SELECT val FROM conf WHERE field=\'next\'');
		$req->execute();
		$next = $req->fetch(PDO::FETCH_OBJ)->val;
		$url = "http://wiki.xkcd.com/wgh/index.php?title=Special:RecentChanges&namespace=0&from=$next";
		
		$response = @file_get_contents($url);
		if($response !== false){
			$response = utf8_decode($response);
			$arr = explode("mw-changeslist-line-not-watched", $response);
			$matches = array();
			preg_match_all("~from=(\d+)~", $arr[0], $matches);
			$newnext = max($matches[1]);
			$req = $this->db->prepare('UPDATE conf SET val=:next WHERE field=\'next\'');
			$req->bindParam(':next', $newnext, PDO::PARAM_INT);
			$req->execute();
			array_shift($arr);
			$this->arr = array_reverse($arr);
			$this->count = count($this->arr);
			return true;
		} else {
		   return false;
		}
	}
	
	private function loop(){
		$req = $this->db->prepare('UPDATE watchlist SET rewatch=1 WHERE title=:title');
		//$req = $this->db->prepare('UPDATE watchlist SET rewatch=TRUE WHERE title=:title');
		foreach($this->arr as $this->el){
			$this->getTitle();
			$this->getType();
			if(strpos($this->el, "<abbr class='newpage' title='This edit created a new page'>N</abbr>") !== false){
				$this->getUser();
				$this->post();
			} elseif (in_array($this->type, array('report', 'global'))) {
				$req->bindParam(':title', $this->title, PDO::PARAM_STR);
				$req->execute();
			}
		}
	}
	
	private function getPart($str, $starttoken, $endtoken){
		$start = strpos($str, $starttoken)+strlen(str_replace(array("\\\"", "\\'"), array("\"", "'"), $starttoken));
		$end = strpos($str, $endtoken, $start)-$start;
		return substr($str, $start, $end);
	}
	
	private function getTitle(){
		$this-> title = $this->getPart($this->getPart($this->el, "<span class=\"mw-title\">", "</span>"), ">", "<");
	}
	
	private function getUser(){
		$sep = "<span class=\"mw-changeslist-separator\">. .</span>";
		$els = explode($sep, $this->el);
		$this->user = $this->getPart($els[2], ">", "</a>");
		if(strpos($this->user, '>')){
			$this->user = substr($this->user, strpos($this->user, '>')+1);
		}
	}
	
	private function getType(){
		if(preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9] [-]?[0-9]{1,2} [-]?[0-1]?[0-9]{1,2}$/i", $this->title)){
			$this->type = "report";
		} elseif(preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9] global$/i", $this->title)){
			$this->type = "global";
		} else {
			$this->type = "other";
		}
	}
   
	private function datestring(){
		$date = substr($this->title, 0, 10);
		list($y,$m,$d) = explode("-", $date);
		$this->datestring = date("j F Y", mktime(1, 1, 1, $m, $d, $y));
	}
	
	private function post(){
		switch ($this->type){
			case "report":
				$this->datestring();
				$graticule = str_replace(" ", ", ", substr($this->title, 11));
				$lstr = $this->user." created a report for the $graticule hash of ".$this->datestring.".";
				$str = $lstr;
				break;
			case "global":
				$this->datestring();
				$lstr = $this->user." created a report for the globalhash of ".$this->datestring.".";
				$str = $lstr;
				break;
			default:
				$lstr = $this->user." created a new page on the wiki with title ".$this->title.".";
				$this->len = strlen($lstr);
				if($this->len > 140-$this->twitter->short_url_length){
					$str = $this->user." made a new page on the wiki with title ".substr($this->title, 0, strlen($this->title)-($this->len-140)-2)."...";
				} else {
					$str = $lstr;
				}
		}
		if(in_array($this->user, ['Fippe','FippeBot']) && $this->type !== 'other'){
			return;
		}
		if($this->user !== 'FippeBot' || $this->type !== 'other'){
			$this->ircBot->send("WIKI: ".$lstr." http://geohashing.org/".str_replace(" ", "_", $this->title));
		}
		$str .= " http://geohashing.org/".str_replace(" ", "_", $this->title)." #geohashing";
		$tweet_status = (in_array($this->user, ['Fippe', 'FippeBot']) && $this->type === 'other') ? 9 : 2;
		$this->twitter->queue($str, null, $tweet_status);
		if($this->type != "other"){
			$req = $this->db->prepare('INSERT INTO watchlist (title, reporter) VALUES (:title, :reporter)');
			$req->execute([':title'=>$this->title, ':reporter'=>$this->user]);
		}
	}
	
	private function watch(){
		$req = $this->db->prepare('SELECT id, title FROM watchlist WHERE state IS NULL AND (rewatch=1 OR (TIMESTAMPDIFF(SECOND, queuedate, CURRENT_TIMESTAMP) > 30 AND ((TIMESTAMPDIFF(HOUR, queuedate, CURRENT_TIMESTAMP) > 0 AND TIMESTAMPDIFF(WEEK, queuedate, CURRENT_TIMESTAMP) <= 0 AND TIMESTAMPDIFF(HOUR, last_check, CURRENT_TIMESTAMP) > 0) OR (TIMESTAMPDIFF(WEEK, queuedate, CURRENT_TIMESTAMP) > 0 AND TIMESTAMPDIFF(DAY, last_check, CURRENT_TIMESTAMP) > 0))))');
		//pg query: $req = $this->db->prepare("SELECT id, title FROM watchlist WHERE state IS NULL AND (rewatch=TRUE OR (queuedate + INTERVAL '30 seconds' < CURRENT_TIMESTAMP AND ((queuedate + INTERVAL '1 hour' < CURRENT_TIMESTAMP AND queuedate + INTERVAL '1 week' >= CURRENT_TIMESTAMP AND last_check + INTERVAL '1 hour' < CURRENT_TIMESTAMP) OR (queuedate + INTERVAL '1 week' < CURRENT_TIMESTAMP AND last_check + INTERVAL '1 day' < CURRENT_TIMESTAMP))))");
		$req->execute();
		
		$r1 = $this->db->prepare('UPDATE watchlist SET state=:state WHERE id=:id');
		$r2 = $this->db->prepare('UPDATE watchlist SET last_check=CURRENT_TIMESTAMP, rewatch=0 WHERE id=:id');
		//$r2 = $this->db->prepare('UPDATE watchlist SET last_check=CURRENT_TIMESTAMP, rewatch=FALSE WHERE id=:id');
		while($row = $req->fetch(PDO::FETCH_OBJ)){
			$this->title = $row->title;
			$this->id = $row->id;
			if($this->doWatch()){
				$r1->execute([':state'=>$this->state, ':id'=>$this->id]);
			} else {
				$r2->execute([':id'=>$this->id]);
			}
		}
	}
	
	private function doWatch(){
		$url = "http://wiki.xkcd.com/geohashing/".str_replace(" ", "_", $this->title);
		
		$response = @file_get_contents($url);
		if($response !== false){
			if(strpos($response, "Category:New report") === false && strpos($response, "Category:Expedition planning") === false){
				if(strpos($response, "Category:Coordinates reached") !== false){
					$outcome = "It was a success!";
					$this->state = "success";
				} elseif(strpos($response, "Category:Not reached - No public access") !== false){
					$outcome = "The hash was not accessible to the public.";
					$this->state = "no access";
				} elseif(strpos($response, "Category:Not reached - Technology") !== false){
					$outcome = "Technical problems prevented reaching the hash.";
					$this->state = "technical problems";
				} elseif(strpos($response, "Category:Not reached - Time constraints") !== false){
					$outcome = "Not enough time to go.";
					$this->state = "no time";
				} elseif(strpos($response, "Category:Not reached - Did not attempt") !== false){
					$outcome = "Nobody tried.";
					$this->state = "nobody tried";
				} elseif(strpos($response, "Category:Not reached - Mother nature") !== false){
					$outcome = "Conditions were too bad.";
					$this->state = "bad conditions";
				} elseif(strpos($response, "Category:Not reached") !== false){
					$outcome = "It didn't go according to plan.";
					$this->state = "random failure";
				} else {
					//lets wait some more
					$outcome = false;
				}
				if($outcome !== false && str_replace('-', '', substr($this->title, 0,10)) > date('Ymd', time()-86400*14)){
					$str = "Expedition report ".$this->title." finished. $outcome http://geohashing.org/".str_replace(" ", "_", $this->title);
					$this->ircBot->send('WIKI: '.$str);
					$this->twitter->queue($str.' #geohashing');
					return true;
				}
			}
		}
		return false;
	}    
}

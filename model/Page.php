<?php

namespace model;

use \lib\Db, \PDO;

class Page{
	public $title;
	public $content;
	
	/* get dow of one day or range of days */
	public static function get($url){
		$db = Db::getInstance();
		$query = 'SELECT * FROM page WHERE url=:url';
		$req = $db->prepare($query);
		$req->bindParam(':url', $url, PDO::PARAM_STR);
		$req->execute();
		$res = $req->fetchAll(PDO::FETCH_CLASS, '\model\Page');
		return $res[0];
	}
}

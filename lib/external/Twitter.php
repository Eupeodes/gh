<?php

namespace lib\external;

class Twitter {
	private $twitter;
	
	public function __construct($consumerKey, $consumerSecret, $accessToken = NULL, $accessTokenSecret = NULL) {
		require_once dirname(__FILE__).'/twitter/Twitter.php';
		require_once dirname(__FILE__).'/twitter/OAuth.php';
		$this->twitter = new \Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
	}
	
	public function __call($name, $args) {
		return call_user_func_array([$this->twitter, $name], $args);
	}
}
<?php

/*
 * file:		db.php
 * author:		Marten Tacoma
 * contents:	db connection
 */

namespace lib;

use \PDO;

class Db {
	private static $instance = NULL;

	private function __construct() {}

	private function __clone() {}

	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::createInstance();
		}
		return self::$instance;
	}
	
	private static function createInstance(){
		$docroot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/';
		if(file_exists($docroot.'../settings.php')){
			require($docroot.'../settings.php');
		} elseif(file_exists($docroot.'settings.php')){
			require($docroot.'settings.php');
		} else {
			die('No settings file found');
		}
		$pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
		self::$instance = new PDO($db['type'].':host='.$db['host'].';dbname='.$db['name'], $db['user'], $db['pass'], $pdo_options);
	}
}
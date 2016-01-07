<?php

namespace lib;

class Error {
	public static function send($responseCode, $message){
		http_response_code($responseCode);
		echo str_replace(array('$code', '$error', '$message'), array($responseCode, 'Error '.$responseCode, $message), file_get_contents('../template/error.tpl.php'));
		die();
	}
}

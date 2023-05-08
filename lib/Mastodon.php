<?php

namespace lib;

class Mastodon {
	private $accessToken;
	private $host;
	public function __construct($host, $accessToken){
		$this->host = $host;
		$this->accessToken = $accessToken;
	}
	public function post($message, $image = null) {
		$url = 'https://'.$this->host.'/api/v1/statuses';

		$fields = [
			'status' => $message,
			'visibility' => (is_null($image) ? 'unlisted' : 'public')
		];
		if(!is_null($image)){
			$fields['media_ids'] = [$this->postImage($image)];
		}

		return $this->curl(
			$url,
			json_encode($fields),
			["Content-Type: application/json"]
		);
	}
	
	private function postImage($image){
		$url = 'https://'.$this->host.'/api/v2/media';
		$files = [file_get_contents($image['path'])];
		$fields = array(
			"description" => $image['description']
		);
		
		$boundary = uniqid();
		$delimiter = '-------------' . $boundary;

		$post_data = '';
		$eol = "\r\n";

		foreach ($fields as $name => $content) {
			$post_data .= "--" . $delimiter . $eol . 'Content-Disposition: form-data; name="' . $name . "\"" . $eol . $eol . $content . $eol;
		}

		foreach ($files as $name => $content) {
			$post_data .= "--" . $delimiter . $eol . 'Content-Disposition: form-data; name="file"; filename="' . $name . '"' . $eol . 'Content-Transfer-Encoding: binary' . $eol;
			$post_data .= $eol;
			$post_data .= $content . $eol;
		}

		$post_data .= "--" . $delimiter . "--".$eol;
		
		$headers = [
			"Content-Type: multipart/form-data; boundary=$delimiter",
    		"Content-Length: " . strlen($post_data)
		];
		$return = $this->curl($url, $post_data, $headers);
		if(in_array($return['info']['http_code'], [200, 202])){
			return $return['response']->id;
		} else {
			die();
		}

	}
	
	private function curl($url, $data, $headers = []){
		$headers[] = "Authorization: Bearer ".$this->accessToken;
		$headers[] = "Accept: application/json";
		$headers[] = "Accept-Charset: utf-8";

		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_POST => TRUE,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_TIMEOUT => 20
		);

		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);
		return [
			'response' => json_decode($response),
			'info' => $info
		];
	}
}

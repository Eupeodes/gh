<?php

namespace lib;

class Marker {
	private $data;
	private $image;
	public function __construct($data){
		$this->data = $data;
		$size = array_key_exists('global', $this->data) ? 'l' : 's';
		$text = !array_key_exists('text', $this->data) || strlen($this->data['text']) > 2 ? '??' : $this->data['text'];
		$font = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/img/base/xkcd.ttf';
		$fg = $this->hex2rgb($this->colorGiven('fg') ? $this->data['fg'] : '000');
		$bg = $this->hex2rgb($this->colorGiven('bg') ? $this->data['bg'] : '0ff');
		
		$this->image = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'].'/img/base/fill_'.$size.'.png');
		imageAlphaBlending($this->image, true);
		imageSaveAlpha($this->image, true);

		$fc = imagecolorallocate($this->image, $fg['r'], $fg['g'], $fg['b']);
		$c = imagecolorallocate($this->image, $bg['r'], $bg['g'], $bg['b']);
		
		imagefill($this->image, imagesx($this->image)/2, imagesy($this->image)/2, $c);
		
		$outline = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'].'/img/base/outline_'.$size.'.png');
		imageAlphaBlending($outline, true);
		imageSaveAlpha($outline, true);

		imagecopy($this->image, $outline, 0, 0, 0, 0, imagesx($outline), imagesy($this->image));

		if($size === 'l'){
			imagettftext($this->image, 12, 0, 3, 25, $fc, $_SERVER['DOCUMENT_ROOT'].'/img/base/xkcd.ttf', 'global');
			$bounds = imagettfbbox(20, 0, $_SERVER['DOCUMENT_ROOT'].'/img/base/xkcd.ttf', $text);
			imagettftext($this->image, 20, 0, (imagesx($this->image)-$bounds[4]-$bounds[0])/2, 50, $fc, $_SERVER['DOCUMENT_ROOT'].'/img/base/xkcd.ttf', $text);
		} else {
			$bounds = imagettfbbox(15, 0, $_SERVER['DOCUMENT_ROOT'].'/img/base/xkcd.ttf', $text);
			imagettftext($this->image, 15, 0, (imagesx($this->image)-$bounds[4]-$bounds[0])/2, 22, $fc, $_SERVER['DOCUMENT_ROOT'].'/img/base/xkcd.ttf', $text);

		}
	}
	
	public function show(){
		header('Content-type: image/png');
		imagepng($this->image);
	}
	
	private function hex2rgb($hexColor) {
		$shorthand = (strlen($hexColor) == 3);

		list($r, $g, $b) = $shorthand ? sscanf($hexColor, "%1s%1s%1s") : sscanf($hexColor, "%2s%2s%2s");

		return [
			"r" => hexdec($shorthand ? "$r$r" : $r),
			"g" => hexdec($shorthand ? "$g$g" : $g),
			"b" => hexdec($shorthand ? "$b$b" : $b)
		];
	}
	private function colorGiven($key){
		return array_key_exists($key, $this->data) && in_array(strlen($this->data[$key]), array(3,6));
	}
}
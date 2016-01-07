<?php

namespace view;

class Text{
	private $file;
	private $matches;
	
	function __construct($url){
		strtok($url, '/');
		$this->file = strtok('/');
		$files = array('changelog', 'help', 'readme');
		
		if(!in_array($this->file, $files)){
			\lib\Error::send(401, 'You are not permitted to view this file');
		} 
	}
	
	public function get(){
		$Parsedown = new \lib\external\parseDown\ParseDown();
		$text = $Parsedown->text(file_get_contents(dirname(__FILE__).'/../'.strtoupper($this->file).'.md'));
		preg_match('/^<h1?.*>(?P<title>.*)<\/h1>\n(?P<content>.*)$/ims', $text, $this->matches);
		header('Content-type: text/json');
		echo json_encode(['title'=>  $this->matches['title'],'content'=>$this->matches['content']]);
	}
}
<?php

namespace view;

class Text{
	private $file;
	private $files = array('changelog'=>'md', 'help'=>'tpl', 'settings'=>'tpl');
	private $matches;
	
	function __construct($url){
		strtok($url, '/');
		$this->file = strtok('/');
		
		if(!array_key_exists($this->file, $this->files)){
			\lib\Error::send(401, 'You are not permitted to view this file');
		} 
	}
	
	public function get(){
		switch($this->files[$this->file]){
			case 'md':
				$parseDown = new \lib\external\parseDown\ParseDown();
				$parseDown->setMarkupEscaped(true);
				$text = $parseDown->text(file_get_contents(dirname(__FILE__).'/../'.strtoupper($this->file).'.md'));
				preg_match('/^<h1?.*>(?P<title>.*)<\/h1>\n(?P<content>.*)$/ims', $text, $this->matches);
				$title = $this->matches['title'];
				$content = $this->matches['content'];
				break;
			case 'tpl':
				include(dirname(__FILE__).'/../template/'.strtolower($this->file).'.tpl.php');
				break;
		}
		if(DEBUG){
			die($this->matches['content']);
		}
		header('Content-type: text/json');
		echo json_encode(['title'=>$title,'content'=>$content]);
			
	}
}
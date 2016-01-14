<?php

namespace view;

class Text{
	private $files = array('changelog'=>'md', 'help'=>'tpl', 'settings'=>'tpl');
	
	public function view($url){
		strtok($url, '/');
		$file = strtok('/');
		
		if(!array_key_exists($file, $this->files)){
			\lib\Error::send(401, 'You are not permitted to view this file');
		} 
	
		switch($this->files[$file]){
			case 'md':
				$parseDown = new \lib\external\parseDown\ParseDown();
				$parseDown->setMarkupEscaped(true);
				$text = $parseDown->text(file_get_contents(dirname(__FILE__).'/../'.strtoupper($file).'.md'));
				preg_match('/^<h1?.*>(?P<title>.*)<\/h1>\n(?P<content>.*)$/ims', $text, $matches);
				$title = $matches['title'];
				$content = $matches['content'];
				break;
			case 'tpl':
				require_once dirname(__FILE__).'/../template/'.strtolower($file).'.tpl.php';
				break;
		}
		if(DEBUG){
			die($matches['content']);
		}
		header('Content-type: text/json');
		echo json_encode(['title'=>$title,'content'=>$content]);
			
	}
}

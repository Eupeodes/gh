<?php

namespace view;

class Text{
	private $db;
	private $files = array('changelog'=>'md', 'settings'=>'tpl');
	
	public function __construct() {
		$this->db = \lib\Db::getInstance();
	}

	public function view($url){
		strtok($url, '/');
		$file = strtok('/');
		
		if(array_key_exists($file, $this->files)){
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
		} else {
			$output = \model\Page::get($file);
			$title = $output->title;
			$content = $output->content;
		}
		if(is_null($content)){
			\lib\Error::send(404, 'Page not found');
		}
		header('Content-type: text/json');
		echo json_encode(['title'=>$title,'content'=>$content]);
			
	}
}

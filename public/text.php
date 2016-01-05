<?php

ini_set('display_errors', 'on');

$input = filter_input_array(INPUT_GET);

$file = array_key_exists('file', $input) ? $file = $input['file'] : substr($input['url'],5);

$files = array('changelog', 'help', 'readme');

if(!in_array($file, $files)){
	\lib\Error::send(401, 'You are not permitted to view this file');
} else {
	$Parsedown = new \lib\external\parseDown\ParseDown();

	$text = $Parsedown->text(file_get_contents('../'.strtoupper($file).'.md'));
	preg_match('/^<h1?.*>(?P<title>.*)<\/h1>\n(?P<content>.*)$/ims', $text, $matches);
	header('Content-type: text/json');
	echo json_encode(['title'=>$matches['title'],'content'=>$matches['content']]);
}
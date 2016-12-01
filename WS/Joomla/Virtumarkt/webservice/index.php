<?php
ini_set('display_errors', 'On');
require 'rest.php';

define('JPATH_PLATFORM', dirname(dirname(__FILE__).'../'));


$xml_string = stripslashes($_POST['data']);
$xml = simplexml_load_string($xml_string);
unset($xml_string);

$resource = (string)$xml->resource;
$class = 'classes/'.basename($resource).'.php';
unset($xml);
if(file_exists($class)) {
	require $class;
	
	$out = new $resource;
	$out->response();
}
else {
	$out = new REST_app;
	$out->setError('Resource <'.$resource.'> unknown!');
	$out->response();
	
}
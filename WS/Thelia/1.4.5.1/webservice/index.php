<?php
/**
 * @copyright (c) 2013, Vaisonet
 * @author Vaisonet <support@vaisonet.com>
 * @ignore
 * @package Vaisonet_Webservice_Thelia_1.4.5.1
 * @version 5.5.0
 * 
 * Webservice de connexion à Thelia pour le connecteur Vaisonet http://www.vaisonet.com/
 * Tous droits réservés.
 * 
 */
ini_set('display_errors', 'On');
require 'rest.php';

$xml_string = $_POST['data'];
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
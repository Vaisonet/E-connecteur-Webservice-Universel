<?php
//@todo mettre sur la ligne suivante l'id de la commande à tester
// L'ID est l'indentifiant retourné par le §9 List des commandes

$id = '123';

require '../config.php';
require '../libs/functions.php';

$opt['data'] = '<?xml version="1.0"?>
			<connecteur>
				<resource>orders</resource>
				<url>'.$url.'</url>
				<key>'.$key.'</key>
				<id>'.$id.'</id>
			</connecteur>';
    
$opt['resource'] = 'orders';
$opt['url'] = $url;
$opt['key'] = $key;
$opt['id'] = $id;

$result = call_ws_post($opt);

validation($result, __FILE__);
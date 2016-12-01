<?php
//@todo mettre sur la ligne suivante les statuts de la commande à tester

$filtre = 'created';

require '../config.php';
require '../libs/functions.php';

$opt['data'] = '<?xml version="1.0"?>
			<connecteur>
				<resource>orders_list</resource>
				<url>'.$url.'</url>
				<key>'.$key.'</key>
				<filter>'.$filtre.'</filter>
			</connecteur>';
    
$opt['resource'] = 'orders_list';
$opt['url'] = $url;
$opt['key'] = $key;
$opt['filter'] = $filtre;

$result = call_ws_post($opt);

validation($result, __FILE__);
<?php
require '../config.php';
require '../libs/functions.php';

$opt['data'] = '<?xml version="1.0"?>
			<connecteur>
				<resource>products_list</resource>
				<url>'.$url.'</url>
				<key>'.$key.'</key>
				<limit>0,500</limit>
			</connecteur>';
    
$opt['resource'] = 'products_list';
$opt['url'] = $url;
$opt['key'] = $key;
$opt['limit'] = '0,500';

$result = call_ws_post($opt);

validation($result, __FILE__);
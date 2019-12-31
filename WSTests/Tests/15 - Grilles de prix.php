<?php
//Simule l'envoi d'une grille de prix non compressée ou compressée GZip
require '../config.php';
require '../libs/functions.php';

$opt['url'] = $url;
$opt['key'] = $key;
$opt['resource'] = 'add_grilles';
$opt['compression'] = 0;

//Le fichier contenant les grilles de prix de test
$opt['data'] = file_get_contents('../data/grilles_2.xml'); //Petit échantillon
//$opt['data'] = file_get_contents('../data/grilles_1.xml'); //Gros échantillon

//On compresse ou pas (à commenter dans ce cas les deux lignes ci-dessous)
$opt['data'] = gzdeflate($opt['data']);
$opt['compression'] = 3;

$result = call_ws_post($opt);
//validation($result, __FILE__); //@todo vérifier le XSD de validation du format de la réponse
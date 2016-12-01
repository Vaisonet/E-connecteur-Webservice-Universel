<?php
require '../config.php';
require '../libs/functions.php';

$opt['data'] = '<?xml version="1.0"?>
			<connecteur>
				<resource>update_product</resource>
				<url>'.$url.'</url>
				<key>'.$key.'</key>
				<reference>123456</reference>
				<EAN13>1234567891230</EAN13>
				<UPC></UPC>
				<largeur>10</largeur>
				<hauteur>20</hauteur>
				<profondeur>30</profondeur>
				<p_vente>40.54</p_vente>
				<nom>Mon produit éàù</nom>
				<meta_description>Ma méta description</meta_description>
				<meta_keywords>Mon méta mot clef</meta_keywords>
				<meta_title>Mon méta titre</meta_title>
				<desc_longue>Ma description longue bla bla éàù</desc_longue>
				<desc_courte>Ma description courte bla bla éàù</desc_courte>
				<tva>20.00</tva>
				<ecotaxe>0</ecotaxe>
				<stock>1540</stock>
				<poids>12.01</poids>
				<actif>1</actif>				
			</connecteur>';
    
$opt['resource'] = 'update_product';
$opt['url'] = $url;
$opt['key'] = $key;
$opt['reference'] = '123456';
$opt['EAN13'] = '1234567891230';
$opt['UPC'] = '';
$opt['largeur'] = '10';
$opt['hauteur'] = '20';
$opt['profondeur'] = '30';
$opt['p_vente'] = '40.54';
$opt['nom'] = 'Mon produit éàù';
$opt['meta_description'] = 'Ma méta description';
$opt['meta_keywords'] = 'Mon méta mot clef';
$opt['meta_title'] = 'Mon méta titre';
$opt['desc_longue'] = 'Ma description longue bla bla éàù';
$opt['desc_courte'] = 'Ma description courte bla bla éàù';
$opt['tva'] = '20.00';
$opt['ecotaxe'] = '0';
$opt['stock'] = '1540';
$opt['poids'] = '12.01';
$opt['actif'] = '1';

$result = call_ws_post($opt);

validation($result, __FILE__);
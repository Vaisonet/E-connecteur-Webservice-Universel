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

//$result = call_ws_post($opt);
$result = '<?xml version="1.0"?><connecteur>
    <order>
	<livraison>
	    <pays>FR</pays>
	    <company/>
	    <lastname>Mon super nom é</lastname>
	    <firstname>Mon super nom é</firstname>
	    <address1>Mon adresse</address1>
	    <address2/>
	    <postcode>84110</postcode>
	    <city>Vaison la Romaine</city>
	    <phone/>
	    <phone_mobile/>
	</livraison>
	<facturation>
	    <pays>FR</pays>
	    <company/>
	    <lastname>Mon super nom é</lastname>
	    <firstname>Mon super nom é</firstname>
	    <address1>Mon adresse</address1>
	    <address2/>
	    <postcode>84110</postcode>
	    <city>Vaison la Romaine</city>
	    <phone/>
	    <phone_mobile/>
	    <vat_number/>
	</facturation>
	<client>
	    <id>123</id>
 	    <lastname>Mon super nom é</lastname>
	    <firstname>Mon super nom é</firstname>
	    <email>contact@vaisonet.com</email>
	</client>
	<commande>
	    <date_add>21/12/1978</date_add>
 	    <payment/>
	    <total_discounts/>
    <total_discounts_tax_incl/>
    <total_discounts_tax_excl/>
    <total_paid>100.54</total_paid>
    <total_products>55.12</total_products>
    <total_products_wt>50.14</total_products_wt>
    <total_shipping>0</total_shipping>
    <carrier_tax_rate>0</carrier_tax_rate>
    <livreur>marsupilami</livreur>
	</commande>
	<produits>
		<product>
		    <product_reference>SJhlKhfJKLSF5df</product_reference>
		    <product_quantity>52</product_quantity>
		    <product_name>Mon super nom</product_name>
		    <product_price>41.01</product_price>
		    <tva_rate>20.00</tva_rate>
		    <ecotax/>
  		</product>
		<product>
		    <product_reference>SJhlKhfJKLSF5df</product_reference>
		    <product_quantity>52</product_quantity>
		    <product_name>Mon super nom</product_name>
		    <product_price>41.01</product_price>
		    <tva_rate>20.00</tva_rate>
		    <ecotax/>
  		</product>
		<product>
		    <product_reference>SJhlKhfJKLSF5df</product_reference>
		    <product_quantity>52</product_quantity>
		    <product_name>Mon super nom</product_name>
		    <product_price>41.01</product_price>
		    <tva_rate>20.00</tva_rate>
		    <ecotax/>
  		</product>
		<product>
		    <product_reference>SJhlKhfJKLSF5df</product_reference>
		    <product_quantity>52</product_quantity>
		    <product_name>Mon super nom</product_name>
		    <product_price>41.01</product_price>
		    <tva_rate>20.00</tva_rate>
		    <ecotax/>
  		</product>
		<product>
		    <product_reference>SJhlKhfJKLSF5df</product_reference>
		    <product_quantity>52</product_quantity>
		    <product_name>Mon super nom</product_name>
		    <product_price>41.01</product_price>
		    <tva_rate>20.00</tva_rate>
		    <ecotax/>
  		</product>
	</produits>
    </order>
</connecteur>
';

validation($result, __FILE__);
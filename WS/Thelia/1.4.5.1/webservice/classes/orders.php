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

class orders extends REST_app {
	var $request_type = array('POST');
	
	public function request() {
		
		
		if((int)$this->xml->id) {
			$commande = new Commande((int)$this->xml->id);
			$venteadr = new Venteadr($commande->adrlivr);
			$venteadr2 = new Venteadr($commande->adrfact);
			$client = new Client($commande->client);
			
			$payment_name = 'unknown';
			$modules = new Modules();
			$modules->charger_id($commande->paiement);
			if($modules->nom) {
				$payment_name = $modules->nom;
			}
			
			$total = $commande->total();
			$totalremise = $total - $commande->remise;
			$port = $commande->port;
			if($port<0) $port=0;
			
			$moduletransport = new Modules();
			$moduletransport->charger_id($commande->transport);
		
			$moduletransportdesc = new Modulesdesc();
			$moduletransportdesc->charger($moduletransport->nom);
			
			$order_arr = array(
				'reference' => $commande->ref,
				'date_add'=>$commande->date,
				'payment' => $payment_name,
				'total_discounts' => round($commande->remise, 2),
				'total_paid' => round($totalremise + $port, 2),
				'total_products' => $total,
				'total_shipping' => round($port, 2),
				'carrier_tax_rate' => 0,
				'livreur' => $moduletransportdesc->titre
			);
			
			$venteprod = new Venteprod();

			$query = "select * from $venteprod->table where commande='$commande->id'";
			$res = $venteprod->query($query);
			
			$products_arr = array();
			while($res && $row = $venteprod->fetch_object($res)) {
				
				$produit = new Produit();
				$produit->charger($row->ref);
				
				$products_arr['product'][] = array(
					'product_reference' => $row->ref,
					'product_quantity' => $row->quantite,
					'product_name' => $row->titre,
					'product_price' => $row->prixu / (1+$row->tva/100),
					'tva_rate' => $row->tva,
					'ecotax' => $produit->ecotaxe
				);
			}
			
			$client_arr = array(
				'id' => $client->id,
				'lastname' => $client->nom,
				'firstname' => $client->prenom,
				'email' => $client->email
			);
			
			$pays = new Pays($venteadr2->pays);
			
			$billing_arr = array(
				'pays' => $pays->isoalpha2,
				'company' => $venteadr2->entreprise,
				'lastname' => $venteadr2->prenom,
				'firstname' => $venteadr2->nom,
				'address1' => $venteadr2->adresse1,
				'address2' => $venteadr2->adresse2,
				'postcode' => $venteadr2->cpostal,
				'city' => $venteadr2->ville,
				'phone' => $client->telfixe,
				'phone_mobile' => $client->telport
			);
			
			$pays = new Pays($venteadr->pays);
			$delivery_arr = array(
				'pays' => $pays->isoalpha2,
				'company' => $venteadr->entreprise,
				'lastname' => $venteadr->prenom,
				'firstname' => $venteadr->nom,
				'address1' => $venteadr->adresse1,
				'address2' => $venteadr->adresse2,
				'postcode' => $venteadr->cpostal,
				'city' => $venteadr2->ville,
				'phone' => $client->telfixe,
				'phone_mobile' => $client->telport

			);
			
			$this->_out = array(
				'order' => array(
					'livraison' =>$delivery_arr,
					'facturation' => $billing_arr,
					'client' => $client_arr,
					'commande' => $order_arr, 
					'produits' => $products_arr
				)
			);
		}
		else {
			$this->setError('Order <id> is required');
		}
	}
}
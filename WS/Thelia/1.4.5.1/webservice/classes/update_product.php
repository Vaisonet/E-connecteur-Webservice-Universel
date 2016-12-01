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

class update_product extends REST_app {
	var $request_type = array('POST');
	
	public function request() {

		if($this->xml->reference) {
			$produit = new Produit();
			$produitdesc = new Produitdesc();
			

			
			$produit->charger($this->xml->reference);

			$res = $produitdesc->charger($produit->id, self::DEFAULT_LANGUAGE_ID);
			
			if(!$res){
				CacheBase::getCache()->reset_cache();
				$temp = new Produitdesc();
				$temp->produit=$produit->id;
				$temp->lang=self::DEFAULT_LANGUAGE_ID;
				$lastid = $temp->add();
				$produitdesc = new Produitdesc();
				$produitdesc->charger_id($lastid);
			}
			
			$price = (float)$this->xml->p_vente;
			$tax = (float)$this->xml->tva;
			$price = $price * (1 + $tax/100);
			$stock = (int)$this->xml->stock;
			$active = (int)$this->xml->actif;
			if(!$active) $active = 0;
			else $active = 1;
			
			$produit->datemodif = date("Y-m-d H:i:s");
			if($price) $produit->prix = $price;
        	if(isset($this->xml->ecotaxe)) $produit->ecotaxe = (float)$this->xml->ecotaxe * (1 + $tax/100);
			if(isset($this->xml->actif)) $produit->ligne = $active;
			if(isset($this->xml->poids)) $produit->poids = (float)$this->xml->poids;
			if(isset($this->xml->stock)) $produit->stock = (int)$this->xml->stock;
			if(isset($this->xml->tva)) $produit->tva = (float)$this->xml->tva;
			
			if(isset($this->xml->desc_courte)) $produitdesc->chapo = $this->xml->desc_courte;
			if(isset($this->xml->desc_longue)) $produitdesc->description = $this->xml->desc_longue;
			if(isset($this->xml->nom)) $produitdesc->titre = $this->xml->nom;
			
			$produitdesc->chapo = str_replace("\n", "<br />", $produitdesc->chapo);
			
			$produit->maj();
	        $produitdesc->maj();
			
			ActionsModules::instance()->appel_module("modprod", $produit);
			
			$this->_out['update_product'] = 'Product updated';
		}
		else {
			$this->setError('<reference> is required');
		}
	}
}
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

class add_product extends REST_app {
	var $request_type = array('POST');
	
	public function request() {

		if($this->xml->reference) {
			$produit = new Produit();
			
			
			
			$produit->charger($this->xml->reference);
			
			if($produit->id){
               $this->setError('Reference '.$this->xml->reference." already exists");
        	}
			else {
				$price = (float)$this->xml->p_vente;
				$tax = (float)$this->xml->tva;
				$price = $price * (1 + $tax/100);
				$stock = (int)$this->xml->stock;
				$active = (int)$this->xml->actif;
				if(!$active) $active = 0;
				else $active = 1;
				
				$produit->datemodif = date("Y-m-d H:i:s");
				$produit->ref = $this->xml->reference;
				if($price) $produit->prix = $price;
				if(isset($this->xml->ecotaxe)) $produit->ecotaxe = (float)$this->xml->ecotaxe * (1 + $tax/100);
				if(isset($active)) $produit->ligne = $active;
				if(isset($this->xml->poids)) $produit->poids = (float)$this->xml->poids;
				if(isset($this->xml->stock)) $produit->stock = (int)$this->xml->stock;
				if(isset($this->xml->tva)) $produit->tva = (float)$this->xml->tva;
				
				$lastid = $produit->add();
        		$produit->id = $lastid;
				
				$produitdesc = new Produitdesc();
				if(isset($this->xml->desc_courte)) $produitdesc->chapo = $this->xml->desc_courte;
				if(isset($this->xml->desc_longue)) $produitdesc->description = $this->xml->desc_longue;
				if(isset($this->xml->nom)) $produitdesc->titre = $this->xml->nom;
				
				$produitdesc->chapo = str_replace("\n", "<br />", $produitdesc->chapo);
				
				$produitdesc->produit = $lastid;
				$produitdesc->lang = self::DEFAULT_LANGUAGE_ID;
				
				$produitdesc->add();
				
				
				$rubrique = new Rubrique();
				$rubriquedesc = new Rubriquedesc();
				
				$query = "select c.id from $rubrique->table c JOIN $rubriquedesc->table d ON c.id=d.id WHERE d.lang='".self::DEFAULT_LANGUAGE_ID."' AND d.titre='".self::DEFAULT_CATEGORY_NAME."'";
        		$resul = mysql_query($query);
				$row = mysql_fetch_assoc($resul);
				if(!$row['id']) {
					// Create default category
					$cat_id = $this->add_default_category();
				}
				else $cat_id = $row['id'];
				
				$produit->rubrique   = $cat_id;
				$produit->maj();
				
				$produitdesc->reecrire();
				
				ActionsModules::instance()->appel_module("ajoutprod", $produit);
			}
			
			
			if($produit->id) {
				$this->_out['add_product'] = 'Product added';
				//$query = "INSERT INTO produit_erp_ref SET id='".$produit->id."', reference='".$produit->ref."'";
			}
			else $this->setError('Cannot add product');
		}
		else {
			$this->setError('<reference> is required');
		}
	}
	
	
	private function add_default_category() {
		$rubrique = new Rubrique();
        $rubrique->parent=0;
		$rubrique->ligne = 0;

        $lastid = $rubrique->add();

        $rubrique->charger($lastid);

        $rubrique->maj();
		
		$rubriquedesc = new Rubriquedesc();

        $rubriquedesc->rubrique = $lastid;
        $rubriquedesc->lang = self::DEFAULT_LANGUAGE_ID;
        $rubriquedesc->titre = self::DEFAULT_CATEGORY_NAME;
		
		$rubriquedesc->add();
		
		$rubriquedesc->reecrire();

        ActionsModules::instance()->appel_module("ajoutrub", $rubrique);
		
		return $lastid;
	}
}
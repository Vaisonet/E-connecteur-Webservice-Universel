<?php

class add_product extends REST_app {
	var $request_type = array('POST');
	
	public function request() {
		
		if($this->xml->reference) {
			
			
			$query = "SELECT virtuemart_product_id FROM ".$this->JConfig->dbprefix."virtuemart_products WHERE product_sku='".$this->esc($this->xml->reference)."'";

			$res = $this->dbh->query($query);
			$product = $res->fetch_assoc();
			
			
			if($product['virtuemart_product_id']){
               $this->setError('Reference '.$this->xml->reference." already exists");
        	}
			else {
				
				$query = "INSERT INTO ".$this->JConfig->dbprefix."virtuemart_products SET 
					virtuemart_vendor_id='".$this->vendorID."',
					product_sku='".$this->esc($this->xml->reference)."',";
				if(isset($this->xml->poids)) $query .= " product_weight='".(float)$this->xml->poids."', product_weight_uom='G', ";
				if(isset($this->xml->profondeur))	$query .= " product_length='".(float)$this->xml->profondeur."', ";
				if(isset($this->xml->largeur))	$query .= " product_width='".(float)$this->xml->largeur."', ";
				if(isset($this->xml->hauteur))	$query .= " product_height='".(float)$this->xml->hauteur."', ";
				$query .= " product_lwh_uom='CM', ";
				
				$active = (int)$this->xml->actif;
				if(!$active) $active = 0;
				else $active = 1;
				
				$query .= " published='".$active."', ";
				
				if(isset($this->xml->stock)) $query .= " product_in_stock='".(int)$this->xml->stock."', ";
				
				
				$query .= " created_on=NOW() ";
				$this->dbh->query($query);
				
				$product_id = $this->dbh->insert_id;
				
				$query = "SELECT * FROM ".$this->JConfig->dbprefix."virtuemart_currencies WHERE currency_code_3='".$this->defaultCurrency."'";
				$res = $this->dbh->query($query);
				$currency = $res->fetch_assoc();
				
				$tax_id = 0;
				if(isset($this->xml->tva)) {
					$query = "SELECT * FROM ".$this->JConfig->dbprefix."virtuemart_calcs WHERE calc_kind='Tax' AND calc_value='".(float)$this->xml->tva."' AND virtuemart_vendor_id='".$this->vendorID."' LIMIT 1";
					$res = $this->dbh->query($query);
					$tax = $res->fetch_assoc();
					if($tax['virtuemart_calc_id']) $tax_id = $tax['virtuemart_calc_id'];
				}
				
				
				$query = "INSERT INTO ".$this->JConfig->dbprefix."virtuemart_product_prices SET virtuemart_product_id='".$product_id."', product_price='".(float)$this->xml->p_vente."', product_tax_id='".$tax_id."', product_currency='".$currency['virtuemart_currency_id']."', created_on=NOW()";

				$this->dbh->query($query);
				
				
				$query = "INSERT INTO ".$this->JConfig->dbprefix."virtuemart_products_".$this->locale." SET 
					virtuemart_product_id='".$product_id."',
					product_s_desc='".$this->esc($this->xml->desc_courte)."', 
					product_desc='".$this->esc($this->xml->desc_longue)."',
					product_name='".$this->esc($this->xml->nom)."',
					metadesc='".$this->esc($this->xml->meta_description)."',
					metakey='".$this->esc($this->xml->meta_keywords)."',";
				if(isset($this->xml->meta_title) && !empty($this->xml->meta_title)) $query .= " customtitle='".$this->esc($this->xml->meta_title)."', ";
				$query .= " slug='".$this->slug($this->esc($this->xml->nom))."' ";
				
				$this->dbh->query($query);
				
			}
			
			
			if($product_id) {
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
	
	private function slug($str) {
		$str = trim(strtolower($str));
		$str = str_replace('-', ' ', $str);

		// Remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $str);

		// Trim dashes at beginning and end of alias
		$str = trim($str, '-');

		return $str;
	}
}
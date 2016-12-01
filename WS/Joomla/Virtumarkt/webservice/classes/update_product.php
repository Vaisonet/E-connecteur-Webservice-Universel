<?php

class update_product extends REST_app {
	var $request_type = array('POST');
	
	public function request() {

		if($this->xml->reference) {
			
			$query = "SELECT virtuemart_product_id FROM ".$this->JConfig->dbprefix."virtuemart_products WHERE product_sku='".$this->esc($this->xml->reference)."'";

			$res = $this->dbh->query($query);
			$product = $res->fetch_assoc();
			
			
			if(!$product['virtuemart_product_id']){
               $this->setError('Reference '.$this->xml->reference." do not exist.");
        	}
			else {
				$product_id = $product['virtuemart_product_id'];
				$query = "UPDATE ".$this->JConfig->dbprefix."virtuemart_products SET 
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
				
				
				$query .= " modified_on=NOW() WHERE virtuemart_product_id='".$product_id."' ";
				$this->dbh->query($query);
				
				
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
				
				
				$query = "UPDATE ".$this->JConfig->dbprefix."virtuemart_product_prices SET product_price='".(float)$this->xml->p_vente."', product_tax_id='".$tax_id."', product_currency='".$currency['virtuemart_currency_id']."', modified_on=NOW(), virtuemart_product_id='".$product_id."' LIMIT 1";

				$this->dbh->query($query);
				
				
				$query = "UPDATE ".$this->JConfig->dbprefix."virtuemart_products_".$this->locale." SET 
					
					product_s_desc='".$this->esc($this->xml->desc_courte)."', 
					product_desc='".$this->esc($this->xml->desc_longue)."',
					product_name='".$this->esc($this->xml->nom)."',
					metadesc='".$this->esc($this->xml->meta_description)."',
					metakey='".$this->esc($this->xml->meta_keywords)."',";
				if(isset($this->xml->meta_title) && !empty($this->xml->meta_title)) $query .= " customtitle='".$this->esc($this->xml->meta_title)."', ";
				$query = rtrim($query, ', ');
				$query .= " WHERE virtuemart_product_id='".$product_id."' ";
				
				$this->dbh->query($query);
				
			}
			
			$this->_out['update_product'] = 'Product updated';
		}
		else {
			$this->setError('<reference> is required');
		}
	}
}
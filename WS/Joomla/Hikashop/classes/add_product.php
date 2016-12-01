<?php

class add_product extends REST_app {
	var $request_type = array('POST');
	
	public function request() {
		
		if($this->xml->reference) {
			
			
			$query = "SELECT product_id as id FROM ".$this->JConfig->dbprefix."hikashop_product WHERE product_code='".$this->esc($this->xml->reference)."'";

			$res = $this->dbh->query($query);
			$product = $res->fetch_assoc();
			
			
			if($product['id']){
               $this->setError('Reference '.$this->xml->reference." already exists");
        	}
			else {
				
				
				
				
				$query = "INSERT INTO ".$this->JConfig->dbprefix."hikashop_product SET 
					product_name='".$this->esc($this->xml->nom)."',
					product_description='".$this->esc($this->xml->desc_longue)."',
					product_tax_id='".self::TAX_ID."',
					product_code='".$this->esc($this->xml->reference)."',
					product_type='main',
					product_keywords='".$this->esc($this->xml->meta_keywords)."',
					product_meta_description='".$this->esc($this->xml->meta_description)."',
					product_page_title='".$this->esc($this->xml->meta_title)."', ";
				
				if(isset($this->xml->poids)) $query .= " product_weight='".(float)$this->xml->poids."', product_weight_unit='g', ";
				if(isset($this->xml->profondeur))	$query .= " product_length='".(float)$this->xml->profondeur."', ";
				if(isset($this->xml->largeur))	$query .= " product_width='".(float)$this->xml->largeur."', ";
				if(isset($this->xml->hauteur))	$query .= " product_height='".(float)$this->xml->hauteur."', ";
				$query .= " product_dimension_unit='cm', ";
				
				$active = (int)$this->xml->actif;
				if(!$active) $active = 0;
				else $active = 1;
				
				$query .= "product_published='".$active."', ";
				
				if(isset($this->xml->stock)) $query .= " product_quantity='".(int)$this->xml->stock."', ";
				
				
				$query .= " product_created=UNIX_TIMESTAMP() ";
				
				
				$this->dbh->query($query);
				
				$product_id = $this->dbh->insert_id;
				if($product_id) {
					
					$query = "SELECT MAX(`ordering`) as m FROM ".$this->JConfig->dbprefix."hikashop_product_category WHERE `category_id`=1";
					$res = $this->dbh->query($query);
					$row = $res->fetch_assoc();
					$order = $row['m']+1;
					
					$query = "INSERT INTO ".$this->JConfig->dbprefix."hikashop_product_category SET category_id='1', product_id='".$product_id."', `ordering`='".$order."'";
					$this->dbh->query($query);
				
					$query = "INSERT INTO ".$this->JConfig->dbprefix."hikashop_price SET price_currency_id=1, price_product_id='".$product_id."', price_value='".(float)$this->xml->p_vente."', price_access='all'";
					$this->dbh->query($query);
					
					for($i=2;$i<=5;$i++) {
						$p_vente = 'p_vente'.$i;
						if(isset($this->xml->$p_vente)) {
							$pv_group = 'p_vente'.$i.'_group_id';
							if(isset($this->$pv_group)) {
								$query = "INSERT INTO ".$this->JConfig->dbprefix."hikashop_price SET price_currency_id=1, price_product_id='".$product_id."', price_value='".(float)$this->xml->$p_vente."', price_access=',".$this->$pv_group.",'";
								$this->dbh->query($query);
							}
							else {
								$this->setError('Group ID for p_vente'.$i.' not defined.');
							}
						}
					}
				}
				
				
			}
			
			
			if(isset($product_id) && $product_id) {
				$this->_out['add_product'] = 'Product added';
				//$query = "INSERT INTO produit_erp_ref SET id='".$produit->id."', reference='".$produit->ref."'";
			}
			else $this->setError('Cannot add product');
		}
		else {
			$this->setError('<reference> is required');
		}
	}
	
}
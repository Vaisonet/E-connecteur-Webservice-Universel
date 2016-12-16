<?php

class update_product extends REST_app {
	var $request_type = array('POST');
	
	
	public function request() {

		if($this->xml->reference) {
			
			$query = "SELECT product_id as id FROM ".$this->JConfig->dbprefix."hikashop_product WHERE product_code='".$this->esc($this->xml->reference)."'";

			$res = $this->dbh->query($query);
			$product = $res->fetch_assoc();
			
			
			if(!$product['id']){
               $this->setError('Reference '.$this->xml->reference." do not exist.");
        	}
			else {
				
				// Find tax id
				$tax_id = '';
				if($this->xml->tva) {
					$query = "SELECT tax_namekey FROM ".$this->JConfig->dbprefix."hikashop_tax WHERE tax_rate='".((float)$this->xml->tva/100)."'";
					
					$res = $this->dbh->query($query);
					$tax = $res->fetch_assoc();
				}
				
				
				
				if((float)$this->xml->tva > 0 && (!isset($tax['tax_namekey']) || empty($tax['tax_namekey'])) ) {
					$this->setError('Tax '.$this->xml->tva." does not exist. Please add it in Hikashop administration.");
				}
				else {
					
					$query = "SELECT category_id FROM ".$this->JConfig->dbprefix."hikashop_category WHERE category_type='tax' AND category_name='".$tax['tax_namekey']."'";
					$res = $this->dbh->query($query);
					$row = $res->fetch_assoc();
					$tax_id = $row['category_id'];
					
					$product_id = $product['id'];
					$query = "UPDATE ".$this->JConfig->dbprefix."hikashop_product SET
						
						
						product_code='".$this->esc($this->xml->reference)."',
						product_type='main',
						
						
						";
					
					if($tax_id) $query .= " product_tax_id='".$tax_id."', ";
					if(isset($this->xml->meta_keywords)) $query .= " product_keywords='".$this->esc($this->xml->meta_keywords)."', ";
					if(isset($this->xml->meta_title)) $query .= " product_page_title='".$this->esc($this->xml->meta_title)."',  ";
					if(isset($this->xml->meta_description)) $query .= " product_meta_description='".$this->esc($this->xml->meta_description)."', ";
					if(isset($this->xml->nom)) $query .= " product_name='".$this->esc($this->xml->nom)."', ";
					if(isset($this->xml->desc_longue)) $query .= " product_description='".$this->esc($this->xml->desc_longue)."', ";
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
					
					
					$query .= " product_modified=UNIX_TIMESTAMP() WHERE product_id ='".$product_id."' ";
					$this->dbh->query($query);
					
					if(isset($this->xml->categories_name_0) && !empty($this->xml->categories_name_0) && isset($this->xml->categories_name_1) && !empty($this->xml->categories_name_1)) {
						
						$category_id = $this->product_category();
						
						$query = "SELECT * FROM ".$this->JConfig->dbprefix."hikashop_product_category WHERE product_id='".$product_id."' AND category_id='".$category_id."'";
						$res = $this->dbh->query($query);
						$row = $res->fetch_assoc();
						if(!$row['product_category_id']) {
							// Category update
							$query = "DELETE FROM ".$this->JConfig->dbprefix."hikashop_product_category WHERE product_id='".$product_id."'";
							$this->dbh->query($query);
							
							$query = "SELECT MAX(`ordering`) as m FROM ".$this->JConfig->dbprefix."hikashop_product_category WHERE `category_id`=".$category_id;
							$res = $this->dbh->query($query);
							$row = $res->fetch_assoc();
							$order = $row['m']+1;
							
							$query = "INSERT INTO ".$this->JConfig->dbprefix."hikashop_product_category SET category_id='".$category_id."', product_id='".$product_id."', `ordering`='".$order."'";
							$this->dbh->query($query);
							
						}
					}
					
					
					
					
					// Process prices
					if(isset($this->xml->p_vente)) {
						$query = "SELECT price_id FROM ".$this->JConfig->dbprefix."hikashop_price WHERE price_product_id='".$product_id."' AND price_currency_id=1 AND price_min_quantity=0 AND price_access='all' ORDER BY price_value";
	
						$res = $this->dbh->query($query);
						$row = $res->fetch_assoc();
						if($row['price_id']) {
							$query = "UPDATE ".$this->JConfig->dbprefix."hikashop_price SET price_value='".(float)$this->xml->p_vente."' WHERE price_id='".$row['price_id']."'";
							$this->dbh->query($query);
						}
						else {
							$query = "INSERT INTO ".$this->JConfig->dbprefix."hikashop_price SET price_currency_id=1, price_product_id='".$product_id."', price_value='".(float)$this->xml->p_vente."', price_access='all'";
							$this->dbh->query($query);
						}
					}
					
					for($i=2;$i<=5;$i++) {
						$p_vente = 'p_vente'.$i;
	
						$pv_group = 'p_vente'.$i.'_group_id';
						if(isset($this->xml->$p_vente)) {
							
							
							if(isset($this->$pv_group)) {
								
								$query = "SELECT price_id FROM ".$this->JConfig->dbprefix."hikashop_price WHERE price_product_id='".$product_id."' AND price_currency_id=1 AND price_min_quantity=0 AND price_access=',".$this->$pv_group.",'  ORDER BY price_value";
								$res = $this->dbh->query($query);
								$row = $res->fetch_assoc();
	
								if($row['price_id']) {
									$query = "UPDATE ".$this->JConfig->dbprefix."hikashop_price SET price_value='".(float)$this->xml->$p_vente."' WHERE price_id='".$row['price_id']."'";
									$this->dbh->query($query);
								}
								else {
									$query = "INSERT INTO ".$this->JConfig->dbprefix."hikashop_price SET price_currency_id=1, price_product_id='".$product_id."', price_value='".(float)$this->xml->$p_vente."', price_access=',".$this->$pv_group.",'";
									$this->dbh->query($query);
								}
							}
							else {
								$this->setError('Group ID for p_vente'.$i.' not defined.');
							}
						}
						else {
							$query = "SELECT price_id FROM ".$this->JConfig->dbprefix."hikashop_price WHERE price_product_id='".$product_id."' AND price_currency_id=1 AND price_min_quantity=0 AND price_access=',".$this->$pv_group.",'  ORDER BY price_value";
							$res = $this->dbh->query($query);
							$row = $res->fetch_assoc();
							if($row['price_id']) {
								$query = "DELETE FROM ".$this->JConfig->dbprefix."hikashop_price WHERE price_id='".$row['price_id']."'";
								
								$this->dbh->query($query);
							}
						}
					}
				}
				
			}
			
			$this->_out['update_product'] = 'Product updated';
		}
		else {
			$this->setError('<reference> is required');
		}
	}
}
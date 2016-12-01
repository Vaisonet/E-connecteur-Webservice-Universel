<?php

class update_option extends REST_app {
	var $request_type = array('POST');
	
	public function request() {
		
		if($this->xml->reference) {
			
			
			$query = "SELECT product_id as id FROM ".$this->JConfig->dbprefix."hikashop_product WHERE product_code='".$this->esc($this->xml->reference)."'";

			$res = $this->dbh->query($query);
			$product = $res->fetch_assoc();
			
			
			
			if(!$product['id']){
               $this->setError('Reference '.$this->xml->reference." not found");
        	}
			else {
				
				$product_id = $product['id'];
				
				
				$query = "UPDATE ".$this->JConfig->dbprefix."hikashop_product SET 
					product_name='".$this->esc($this->xml->nom)."',
					product_description='".$this->esc($this->xml->desc_longue)."',";
				
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
				
				
				$query .= " product_modified=UNIX_TIMESTAMP() WHERE product_id='".$product_id."'";
				
				
				$this->dbh->query($query);
				
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
			
			
			if(isset($product_id) && $product_id) {
				$this->_out['add_product'] = 'Option updated';
				//$query = "INSERT INTO produit_erp_ref SET id='".$produit->id."', reference='".$produit->ref."'";
			}
			else $this->setError('Cannot update option');
		}
		else {
			$this->setError('<reference> is required');
		}
	}
}
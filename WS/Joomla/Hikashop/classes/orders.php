<?php

class orders extends REST_app {
	var $request_type = array('POST');
	
	public function request() {
		
		
		if($this->xml->id) {

			$query = "SELECT * FROM ".$this->JConfig->dbprefix."hikashop_order WHERE order_number='".$this->esc($this->xml->id)."'";
			$res = $this->dbh->query($query);
			$order = $res->fetch_assoc();
			
			if(!$order['order_id']) {
				$this->setError('Order ID '.$this->xml->id.' not found.');
				return;
			}
			
			/*
			$query = "SELECT payment_name FROM ".$this->JConfig->dbprefix."virtuemart_paymentmethods_".$this->locale." WHERE virtuemart_paymentmethod_id='".$order['virtuemart_paymentmethod_id']."'";
			$res = $this->dbh->query($query);
			$paymentmethod = $res->fetch_assoc();
			*/
			
			$query = "SELECT * FROM ".$this->JConfig->dbprefix."hikashop_order_product WHERE order_id='".$order['order_id']."'";
			$res = $this->dbh->query($query);
			$products = array();
			$total_products = 0;
			while($row = $res->fetch_assoc()) {
				$total_products += ($row['order_product_price']+$row['order_product_tax'])*$row['order_product_quantity'];
				$products[] = $row;
			}
			
			
			$ship_total = $order['order_shipping_price']+$order['order_shipping_tax'];
			$ship_tax = 0;
			// Hard code ship tax = 20%
			$ship_tax = 20;
			//if($order['order_shipment_tax']>0) $ship_tax = (($ship_total / $order['order_shipment']) - 1)*100;
			
			
			
			$order_arr = array(
				'reference' => $order['order_number'],
				'date_add'=>date('Y-m-d H:i:s', $order['order_created']),
				'payment' => $order['order_payment_method'],
				'total_discounts' => round($order['order_discount_price']+$order['order_discount_tax'], 2),
				'total_paid' => round($order['order_full_price'], 2),
				'total_products' => round($total_products,2),
				'total_shipping' => round($ship_total, 2),
				'carrier_tax_rate' => round($ship_tax, 1),
				'livreur' => $order['order_shipping_method']
			);
			
			
			$products_arr = array();
			foreach($products as $row) {
				
				$product_tax_rate = 0;
				if($row['order_product_tax']>0) {
					$product_tax_rate = round(($row['order_product_tax'] / $row['order_product_price'])*100 , 1);
				}
				if($product_tax_rate > 19.5 && $product_tax_rate < 20 ) $product_tax_rate = 20;
				if($product_tax_rate > 20 && $product_tax_rate < 20.2 ) $product_tax_rate = 20;
				$attr_str = '';
				$aid_str = '';
				if($row['order_product_options']) {
					
					$attr = unserialize($row['order_product_options']);
					
					if(is_array($attr)) {
						
						foreach($attr as $aid=>$at) {
							if(strlen($aid) > 5) {
								$attr_str .= $aid.$at;
							}
							
						}
					}
				}
				
				
				
				$products_arr['product'][] = array(
					'product_reference' => $row['order_product_code'],
					'product_quantity' => $row['order_product_quantity'],
					'product_name' => strip_tags($row['order_product_name']),
					'attribute' => $attr_str,
					'product_price' => round($row['order_product_price'],2),
					'tva_rate' => $product_tax_rate,
					'ecotax' => 0
				);
			}
			
			
			
			
			$query = "SELECT u.name, u.email
				FROM ".$this->JConfig->dbprefix."hikashop_user su JOIN ".$this->JConfig->dbprefix."users u ON su.user_cms_id=u.id
				WHERE su.user_id='".$order['order_user_id']."'";
			$res = $this->dbh->query($query);
			$client = $res->fetch_assoc();
			preg_match('#^([^\s]+)\s(.+)$#', $client['name'], $m);
			
			$query = "SELECT c.zone_code_2, a.address_company, a.address_firstname, a.address_lastname,
							a.address_street, a.address_street2, a.address_post_code, a.address_city, a.address_telephone, a.address_telephone2
					FROM ".$this->JConfig->dbprefix."hikashop_address a
					LEFT JOIN ".$this->JConfig->dbprefix."hikashop_zone s ON a.address_state=s.zone_namekey
					LEFT JOIN ".$this->JConfig->dbprefix."hikashop_zone c ON a.address_country=c.zone_namekey
					WHERE a.address_id='".$order['order_billing_address_id']."'
			";

			$res = $this->dbh->query($query);
			$billing = $res->fetch_assoc();
			
			$query = "SELECT c.zone_code_2, a.address_company, a.address_firstname, a.address_lastname,
							a.address_street, a.address_street2, a.address_post_code, a.address_city, a.address_telephone, a.address_telephone2
					FROM ".$this->JConfig->dbprefix."hikashop_address a
					LEFT JOIN ".$this->JConfig->dbprefix."hikashop_zone s ON a.address_state=s.zone_namekey
					LEFT JOIN ".$this->JConfig->dbprefix."hikashop_zone c ON a.address_country=c.zone_namekey
					WHERE a.address_id='".$order['order_shipping_address_id']."'
			";
			$res = $this->dbh->query($query);
			$shipping = $res->fetch_assoc();
			
						
			$client_arr = array(
				'id' => $order['order_user_id'],
				'lastname' => $m[2],
				'firstname' => $m[1],
				'email' => $client['email']
			);
			
			
			$billing_arr = array(
				'pays' => $billing['zone_code_2'],
				'company' => $billing['address_company'],
				'lastname' => $billing['address_lastname'],
				'firstname' => $billing['address_firstname'],
				'address1' => $billing['address_street'],
				'address2' => $billing['address_street2'],
				'postcode' => $billing['address_post_code'],
				'city' => $billing['address_city'],
				'phone' => $billing['address_telephone'],
				'phone_mobile' => $billing['address_telephone2']
			);
			
			$delivery_arr = array(
				'pays' => $shipping['zone_code_2'],
				'company' => $shipping['address_company'],
				'lastname' => $shipping['address_lastname'],
				'firstname' => $shipping['address_firstname'],
				'address1' => $shipping['address_street'],
				'address2' => $shipping['address_street2'],
				'postcode' => $shipping['address_post_code'],
				'city' => $shipping['address_city'],
				'phone' => $shipping['address_telephone'],
				'phone_mobile' => $shipping['address_telephone2']

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
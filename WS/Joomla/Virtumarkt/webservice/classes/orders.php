<?php

class orders extends REST_app {
	var $request_type = array('POST');
	
	public function request() {
		
		
		if((int)$this->xml->id) {

			$query = "SELECT * FROM ".$this->JConfig->dbprefix."virtuemart_orders WHERE virtuemart_order_id='".$this->esc($this->xml->id)."'";
			$res = $this->dbh->query($query);
			$order = $res->fetch_assoc();
			
			if(!$order['virtuemart_order_id']) {
				$this->setError('Order ID '.$this->xml->id.' not found.');
				return;
			}
			
			$query = "SELECT payment_name FROM ".$this->JConfig->dbprefix."virtuemart_paymentmethods_".$this->locale." WHERE virtuemart_paymentmethod_id='".$order['virtuemart_paymentmethod_id']."'";
			$res = $this->dbh->query($query);
			$paymentmethod = $res->fetch_assoc();
			
			$query = "SELECT * FROM ".$this->JConfig->dbprefix."virtuemart_order_items WHERE virtuemart_order_id='".$order['virtuemart_order_id']."'";
			$res = $this->dbh->query($query);
			$products = array();
			$total_products = 0;
			while($row = $res->fetch_assoc()) {
				$total_products += $row['product_final_price']*$row['product_quantity'];
				$products[] = $row;
			}
			
			$query = "SELECT shipment_name FROM ".$this->JConfig->dbprefix."virtuemart_shipmentmethods_".$this->locale;
			$res = $this->dbh->query($query);
			$shipmentmethod = $res->fetch_assoc();
			
			$ship_total = $order['order_shipment']+$order['order_shipment_tax'];
			$ship_tax = 0;
			// Hard code ship tax = 20%
			//On suppose le taux de TVA, car les arrondis Virtuemart sont faux
			if($order['order_shipment_tax']>0)
				$ship_tax = 20;
			//if($order['order_shipment_tax']>0) $ship_tax = (($ship_total / $order['order_shipment']) - 1)*100;
			
			
			
			$order_arr = array(
				'reference' => $order['virtuemart_order_id'],
				'date_add'=>$order['created_on'],
				'payment' => ($paymentmethod['payment_name']) ? $paymentmethod['payment_name'] : '',
				'total_discounts' => abs($order['coupon_discount']) / 1.2,
				'total_paid' => $order['order_total'],
				'total_products' => $total_products,
				'total_shipping' => $ship_total,
				'carrier_tax_rate' => round($ship_tax, 1),
				'livreur' => $shipmentmethod['shipment_name'],
                'dev1' => $order['coupon_discount'],
                'dev2' => $order['order_discount'],
			);
			
			
			$products_arr = array();
            $total_products_wt = 0;
			foreach($products as $row) {
				
				$product_tax_rate = 0;
                //Virtuemart ne stocke pas le taux de TVA et buggue sur le calcul des taux de TVA et des prix remisé.
				//On suppose le taux de TVA, car les arrondis Virtuemart sont faux
				if ($row['product_discountedPriceWithoutTax'] == 0)
				{
					//La TVA est a 0 car le prix de vente est nul.
					$product_tax_rate = 0;
				}
				else
				{
					$test = round(($row['product_tax'] / $row['product_discountedPriceWithoutTax'])*100 , 1);
					if($test > 4)
						$product_tax_rate = 5.5;
					if($test > 6)
						$product_tax_rate = 10;
					if($test > 18)
						$product_tax_rate = 20;
				}
					
				$attr_str = '';
				$aid_str = '';
				if($row['product_attribute']) {
					
					$attr = json_decode($row['product_attribute']);
					
					if(is_object($attr)) {
						
						foreach($attr as $aid=>$at) {
							$at = str_replace('</span><span','</span> - <span', $at);
							$attr_str .= strip_tags($at)." | ";
						}
					}
					$attr_str = rtrim($attr_str,' | ');
				}
				
				
				
				$products_arr['product'][] = array(
					'product_reference' => $row['order_item_sku'],
					'product_quantity' => $row['product_quantity'],
					'product_name' => $row['order_item_name'],
					'attribute' => $attr_str,
					'product_price' => $row['product_discountedPriceWithoutTax'],
					'tva_rate' => $product_tax_rate,
					'ecotax' => 0
				);
                $total_products_wt += $row['product_discountedPriceWithoutTax'] * $row['product_quantity'];
			}
            $order_arr['total_discounts'] = $order_arr['total_discounts'] * 100 / $total_products_wt;
			
			$query = "SELECT u.*, c.country_2_code FROM ".$this->JConfig->dbprefix."virtuemart_order_userinfos u 
				LEFT JOIN ".$this->JConfig->dbprefix."virtuemart_countries c ON u.virtuemart_country_id=c.virtuemart_country_id
				WHERE u.virtuemart_order_id='".$order['virtuemart_order_id']."' AND u.address_type='BT'";
			$res = $this->dbh->query($query);
			$billing = $res->fetch_assoc();
			
			$query = "SELECT u.*, c.country_2_code FROM ".$this->JConfig->dbprefix."virtuemart_order_userinfos u 
				LEFT JOIN ".$this->JConfig->dbprefix."virtuemart_countries c ON u.virtuemart_country_id=c.virtuemart_country_id

				WHERE u.virtuemart_order_id='".$order['virtuemart_order_id']."' AND u.address_type='ST'
				ORDER BY virtuemart_order_userinfo_id DESC LIMIT 1";

			$res = $this->dbh->query($query);
			$shipping = $res->fetch_assoc();
			
			if(!$shipping['virtuemart_order_userinfo_id']) $shipping = $billing;
						
			$client_arr = array(
				'id' => $billing['virtuemart_user_id'],
				'lastname' => $billing['last_name'],
				'firstname' => $billing['first_name'],
				'email' => $billing['email']
			);
			
			
			$billing_arr = array(
				'pays' => $billing['country_2_code'],
				'company' => $billing['company'],
				'lastname' => $billing['last_name'],
				'firstname' => $billing['first_name'],
				'address1' => $billing['address_1'],
				'address2' => $billing['address_2'],
				'postcode' => $billing['zip'],
				'city' => $billing['city'],
				'phone' => $billing['phone_1'],
				'phone_mobile' => $billing['phone_2']
			);
			
			$delivery_arr = array(
				'pays' => $shipping['country_2_code'],
				'company' => $shipping['company'],
				'lastname' => $shipping['last_name'],
				'firstname' => $shipping['first_name'],
				'address1' => $shipping['address_1'],
				'address2' => $shipping['address_2'],
				'postcode' => $shipping['zip'],
				'city' => $shipping['city'],
				'phone' => $shipping['phone_1'],
				'phone_mobile' => $shipping['phone_2']

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
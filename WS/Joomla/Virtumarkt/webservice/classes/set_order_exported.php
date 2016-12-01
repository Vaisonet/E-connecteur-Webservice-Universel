<?php

class set_order_exported extends REST_app {
	var $request_type = array('POST');
	
	public function request() {
		if((int)$this->xml->id) {
			
			$query = "SELECT virtuemart_order_id, order_status FROM ".$this->JConfig->dbprefix."virtuemart_orders WHERE virtuemart_order_id='".$this->esc($this->xml->id)."'";
			$res = $this->dbh->query($query);
			if($res->num_rows) {
				$order = $res->fetch_assoc();
				$order_id = $order['virtuemart_order_id'];
			}
			
			if(!isset($order_id) || !$order_id) {
				$this->setError('Order ID '.$this->xml->id.' not found.');
				return;
			}
			
			$query = "SELECT order_status_code,order_status_name FROM ".$this->JConfig->dbprefix."virtuemart_orderstates WHERE virtuemart_vendor_id='".$this->vendorID."'";

			$res = $this->dbh->query($query);
			$statuses = array();
			while($row = $res->fetch_assoc()) {
				$statuses[] = $row;
				if($row['order_status_code'] == $this->xml->statut_final) $status_found = 1;
			}
			
			if(isset($this->xml->statut_final)) {
				if(isset($status_found)) {
					$query = "UPDATE ".$this->JConfig->dbprefix."virtuemart_orders SET order_status='".$this->esc($this->xml->statut_final)."' 
								WHERE virtuemart_order_id='".$order_id."'";
					$this->dbh->query($query);
					$order['order_status'] = $this->xml->statut_final;
				}
				else {
					$statuses_str = '';
					foreach($statuses as $st) $statuses_str .= $st['order_status_code'].',';
					$this->setError('Invalid order status '.$this->xml->statut_final.'. Possible order statuses ['.rtrim($statuses_str,',').']');
				}
			}
			
			$this->_out = array(
				'orders_list'=>array(
					'id'=>$order_id,
					'current_state'=> $order['order_status']
				)				
			);
		}
		else $this->setError('Value for <id> required.');
	}
	
}
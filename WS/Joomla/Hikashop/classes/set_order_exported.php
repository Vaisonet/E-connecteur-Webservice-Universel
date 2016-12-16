<?php

class set_order_exported extends REST_app {
	var $request_type = array('POST');
	
	public function request() {
		if($this->xml->id) {
			
			$query = "SELECT order_id, order_status FROM ".$this->JConfig->dbprefix."hikashop_order WHERE order_number='".$this->esc($this->xml->id)."'";
			$res = $this->dbh->query($query);
			if($res->num_rows) {
				$order = $res->fetch_assoc();
				$order_id = $order['order_id'];
			}
			
			if(!isset($order_id) || !$order_id) {
				$this->setError('Order ID '.$this->xml->id.' not found.');
				return;
			}
			
			$query = "SELECT category_name as name,category_id as id FROM ".$this->JConfig->dbprefix."hikashop_category WHERE category_type='status'";

			$res = $this->dbh->query($query);
			$statuses = array();
			while($row = $res->fetch_assoc()) {
				$statuses[] = $row;
				if($row['name'] == $this->xml->statut_final) $status_found = 1;
			}
			
			if(isset($this->xml->statut_final)) {
				if(isset($status_found)) {
					$query = "UPDATE ".$this->JConfig->dbprefix."hikashop_order SET order_status='".$this->esc($this->xml->statut_final)."' 
								WHERE order_id='".$order_id."'";
					$this->dbh->query($query);
					$order['order_status'] = $this->xml->statut_final;
					
					// Add history record
					$query = "INSERT INTO ".$this->JConfig->dbprefix."hikashop_history SET history_order_id='".$order_id."', history_created=UNIX_TIMESTAMP(), history_ip='".$_SERVER['REMOTE_ADDR']."', history_new_status='".$this->esc($this->xml->statut_final)."' , history_notified=0, history_reason='Remote Call ERP', history_type='modification'";
					$this->dbh->query($query);
				}
				else {
					$statuses_str = '';
					foreach($statuses as $st) $statuses_str .= $st['name'].',';
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
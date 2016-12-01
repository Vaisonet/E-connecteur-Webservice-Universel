<?php

class orders_list extends REST_app {
	var $request_type = array('POST');
	
	public function request() {
		
		
		$filter = '';
		if(isset($this->xml->filter)) {
			$filter = " AND o.order_status='".$this->esc($this->xml->filter)."'";
		}
		
		$query = "SELECT o.virtuemart_order_id, o.order_status
			FROM ".$this->JConfig->dbprefix."virtuemart_orders o
			WHERE 1=1 ".$filter."
			ORDER BY o.virtuemart_order_id DESC";
		

		$res = $this->dbh->query($query);
		while($res && $row = $res->fetch_assoc()) {
			$this->_out[get_class($this)][] = array('id'=>$row['virtuemart_order_id'], 'current_state'=>$row['order_status']);
		}
		if(!isset($this->_out) || sizeof($this->_out)==0) {
			//$this->setError('No orders found');
			$this->_out[get_class($this)][] = array();
		}
	}
}
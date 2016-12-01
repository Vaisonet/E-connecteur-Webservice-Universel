<?php

class products_list extends REST_app {
	var $request_type = array('POST');
	
	public function request() {

		if(isset($this->xml->limit)) {
			$limit = explode(',',$this->xml->limit);

			if(sizeof($limit)==2)  {
				$start = $limit[0];
				$limit = $limit[1];
			}
			else $limit = $limit[0];
		}

		
		
		$query = "SELECT p.virtuemart_product_id as id, p.product_sku as reference FROM ".$this->JConfig->dbprefix."virtuemart_products p ORDER BY p.virtuemart_product_id DESC ";
		if(isset($limit)) {
			$query .= "LIMIT ";
			if(isset($start)) {
				$query .= $start.",";
			}
			$query .= $limit;
		}
		$res = $this->dbh->query($query);

		while($res && $row = $res->fetch_object()){
			$this->_out[get_class($this)][] = array('id'=>$row->id, 'reference'=>$row->reference);
		}
	}
}
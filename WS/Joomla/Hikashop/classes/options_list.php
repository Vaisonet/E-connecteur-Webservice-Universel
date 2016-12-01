<?php

class options_list extends REST_app {
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

		
		
		$query = "SELECT p.product_id as id, p.product_code as reference FROM ".$this->JConfig->dbprefix."hikashop_product p WHERE product_type='variant' ORDER BY p.product_id DESC ";
		if(isset($limit)) {
			$query .= "LIMIT ";
			if(isset($start)) {
				$query .= $start.",";
			}
			$query .= $limit;
		}
		$res = $this->dbh->query($query);

		while($res && $row = $res->fetch_object()){
			$this->_out[get_class($this)]['option'][] = array('id'=>$row->id, 'reference'=>$row->reference);
		}
		
		if(!isset($this->_out) || sizeof($this->_out)==0) {
			$this->_out[get_class($this)] = NULL;
		}
	}
}
<?php
/**
 * @copyright (c) 2013, Vaisonet
 * @author Vaisonet <support@vaisonet.com>
 * @ignore
 * @package Vaisonet_Webservice_Thelia_1.4.5.1
 * @version 5.5.0
 * 
 * Webservice de connexion à Thelia pour le connecteur Vaisonet http://www.vaisonet.com/
 * Tous droits réservés.
 * 
 */

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

		$produit = new Produit();
		$produitdesc = new Produitdesc();
		
		$query = "SELECT p.id, p.ref as reference FROM ".$produit->table." p ORDER BY p.id DESC ";
		if(isset($limit)) {
			$query .= "LIMIT ";
			if(isset($start)) {
				$query .= $start.",";
			}
			$query .= $limit;
		}
		
		$res = $produit->query($query);

		while($res && $row = $produit->fetch_object($res)){
			$this->_out[get_class($this)][] = array('id'=>$row->id, 'reference'=>$row->reference);
		}
	}
}
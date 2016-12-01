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

class orders_list extends REST_app {
	var $request_type = array('POST');
	
	public function request() {
		$commande = new Commande();
		//$statut = new Statut();
		//$statutdesc = new Statutdesc();
		
		$filter = '';
		if(isset($this->xml->filter)) {
			$filter = " AND o.statut='".(int)$this->xml->filter."'";
		}
		
		$query = "SELECT o.id, o.statut
			FROM ".$commande->table." o
			WHERE 1=1 ".$filter."
			ORDER BY o.id DESC";

		$res = $commande->query($query);
		while($res && $row = $commande->fetch_object($res)) {
			$this->_out[get_class($this)][] = array('id'=>$row->id, 'current_state'=>$row->statut);
		}
		if(!isset($this->_out) || sizeof($this->_out)==0) {
			$this->setError('No orders found');
		}
	}
}
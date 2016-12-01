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

class set_order_exported extends REST_app {
	var $request_type = array('POST');
	
	public function request() {
		if((int)$this->xml->id) {
			$commande = new Commande($this->xml->id);
			if(isset($this->xml->statut_final)) {
				$commande->statut = $this->xml->statut_final;
				$commande->maj();
			}
			$this->_out = array(
				'orders_list'=>array(
					'id'=>$commande->id,
					'current_state'=> $commande->statut
				)				
			);
		}
		else $this->setError('Value for <id> required.');
	}
	
}
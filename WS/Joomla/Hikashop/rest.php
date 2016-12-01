<?php

require __DIR__ . "/../configuration.php";

require 'auth.php';
include 'array2xml.php';
class REST_app {

	protected $_errors = array();
	protected $_out;
	protected $dbh, $JConfig;
	const DEFAULT_LANGUAGE_ID = '1';
	const DEFAULT_CATEGORY_NAME = 'ERP Add Products';
	var $xml;
	
	var $p_vente2_group_id = 12; // Manager
	var $p_vente3_group_id = 13; // Super user
	var $p_vente4_group_id = 14; // Validation client
	var $p_vente5_group_id = 15; // Guest
	
	const TAX_ID = 11; // Set this to the tax_id from hikashop_category table
	

	public function __construct() {
		$xml_string = stripslashes($_POST['data']);
		$this->xml = simplexml_load_string($xml_string);
		$this->checkRequest();
		$this->dbConnect();
		$this->auth();
		if(method_exists($this,'request')) $this->request();
	}

	public function dbConnect() {
		$this->JConfig = new JConfig();

		$this->dbh = new Mysqli($this->JConfig->host, $this->JConfig->user, $this->JConfig->password, $this->JConfig->db);

		if($this->dbh->connect_error) {
			 $this->setError('Database error: '.$this->dbh->connect_error);
			 $this->response();
		}

		$this->dbh->query('SET NAMES utf8');
	}

	public function setError($message) {

		$this->_errors[] = $message;

	}

	public function response($code = '200 OK') {
		header('Content-Type: text/xml');
		if(count($this->_errors)) {

			header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
			$xml = Array2XML::createXML('connecteur', array('error'=>$this->_errors));
			echo $xml->saveXML();
			exit;
		}
		elseif(count($this->_out)) {
			header($_SERVER['SERVER_PROTOCOL'].' '.$code);

			$xml = Array2XML::createXML('connecteur', $this->_out);
			echo $xml->saveXML();
			exit;
		}
		else {


			$this->setError('Input data not recognized.');

			header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
			$xml = Array2XML::createXML('connecteur', array('error'=>$this->_errors));
			echo $xml->saveXML();
			exit;
		}
	}

	protected function array2xml($a, $root_element = '<root />') {
		$xml = new SimpleXMLElement($root_element);
		array_walk_recursive($a, array ($xml, 'addChild'));
		return $xml->asXML();
	}

	protected function checkRequest() {
		if(isset($this->request_type)) {
			if(in_array($_SERVER['REQUEST_METHOD'], $this->request_type)) {
				return true;
			}
		}
		else return true;

		$this->setError('Request method not supported. Only {'.implode(',', $this->request_type).'} allowed.');
		return false;
	}

	protected function auth() {
		
			$pass = AUTH_KEY;
			if(!$pass) {
				$this->setError('Please add key in file webservice/.auth');
				$this->response();
			}

			if($this->xml->key != $pass) {
				$this->setError('Invalid authorization key!');
				$this->response();
			}

	}
	
	protected function esc($str) {
		return mysqli_real_escape_string($this->dbh, $str);
	}
}
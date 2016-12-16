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
	
	var $p_vente2_group_id = 6; // Manager
	var $p_vente3_group_id = 8; // Super user
	var $p_vente4_group_id = 10; // Validation client
	var $p_vente5_group_id = 9; // Guest
	
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
	
	public function create_hs_category($name, $parent_id) {
		
		// Saves the start time and memory usage.
		$startTime = microtime(1);
		$startMem  = memory_get_usage();
		


		
		/**
		 * Constant that is checked in included files to prevent direct access.
		 * define() is used in the installation folder rather than "const" to not error for PHP 5.2 and lower
		 */
		if(!defined('_JEXEC'))
			define('_JEXEC', 1);
		
		/*if (file_exists(dirname(dirname(__DIR__)) . '/includes/defines.php'))
		{
			include_once dirname(dirname(__DIR__)) . '/includes/defines.php';	
		}*/
		

		if (!defined('_JDEFINES'))
		{
			
			if(!defined('JPATH_BASE')) define('JPATH_BASE', dirname(__DIR__).'/administrator' );

			require_once JPATH_BASE . '/includes/defines.php';
			
		}
		
		if(!defined('JPATH_PLATFORM')) define('JPATH_PLATFORM',     JPATH_ROOT . DIRECTORY_SEPARATOR . 'libraries');
		if(!defined('JPATH_COMPONENT')) define('JPATH_COMPONENT', JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator/components');

		require_once JPATH_BASE . '/includes/framework.php';

		require_once JPATH_BASE . '/includes/helper.php';
		require_once JPATH_BASE . '/includes/toolbar.php';
		
		// Set profiler start time and memory usage and mark afterLoad in the profiler.
		JDEBUG ? $_PROFILER->setStart($startTime, $startMem)->mark('afterLoad') : null;
		// Instantiate the application.
		jimport('joomla.application.component.class');
		$app = JFactory::getApplication('administrator');
		
		
		require_once  JPATH_ADMINISTRATOR.'/components/com_hikashop/helpers/helper.php';
		require_once  JPATH_ADMINISTRATOR.'/components/com_hikashop/classes/category.php';
		
		
		$test = new hikashopCategoryClass();
		$element = new stdClass();
		$element->category_parent_id = $parent_id; // product category
		$element->category_name = $name;
		
		$config =& hikashop_config();
		if($config->get('alias_auto_fill', 1) && empty($element->category_alias)) {
			$test->addAlias($element);

			if($config->get('sef_remove_id', 0) && (int)$element->alias > 0)
				$element->alias = $config->get('alias_prefix', 'p') . $element->alias;

			$element->category_alias = $element->alias;
			unset($element->alias);
		}
		
		$test->save($element);
		
		// Rebuild categories tree
		$class = hikashop_get('class.category');
		$database = JFactory::getDBO();

		$query = 'SELECT category_left,category_right,category_depth,category_id,category_parent_id FROM #__hikashop_category ORDER BY category_left ASC';
		$database->setQuery($query);
		$root = null;
		$categories = $database->loadObjectList();
		$class->categories = array();
		foreach($categories as $cat){
			$class->categories[$cat->category_parent_id][]=$cat;
			if(empty($cat->category_parent_id)){
							$root = $cat;
			}
		}

		if(!empty($root)){
			$query = 'UPDATE `#__hikashop_category` SET category_parent_id = '.(int)$root->category_id.' WHERE category_parent_id = 0 AND category_id != '.(int)$root->category_id.'';
			$database->setQuery($query);
			$database->query();
		}

		$class->rebuildTree($root,0,1);
		
		
		return $element;
		
	}
	
	public function product_category() {
		
		$query = "SELECT category_id FROM ".$this->JConfig->dbprefix."hikashop_category WHERE category_type='product' AND category_parent_id='1' AND category_name='product category'";
		$res = $this->dbh->query($query);
		$row = $res->fetch_assoc();
		$product_category = $row['category_id'];
		if(!$product_category) {
			$this->setError('Unable to locate product category');
		}
		else {
			$query = "SELECT category_id FROM ".$this->JConfig->dbprefix."hikashop_category WHERE category_type='product' AND category_parent_id='".$product_category."' AND category_name='".$this->esc($this->xml->categories_name_0)."'";

			$res = $this->dbh->query($query);
			$row = $res->fetch_assoc();
			$cat_0 = $row['category_id'];
			if(!$cat_0) {
				$tmp = $this->create_hs_category((string)$this->xml->categories_name_0, $product_category);
				$cat_0 = $tmp->category_id;
			}
			$cat_id = $cat_0;
			
			if($this->xml->categories_name_1) {
				$query = "SELECT category_id FROM ".$this->JConfig->dbprefix."hikashop_category WHERE category_type='product' AND category_parent_id='".$cat_0."' AND category_name='".$this->esc($this->xml->categories_name_1)."'";
				$res = $this->dbh->query($query);
				$row = $res->fetch_assoc();
				$cat_1 = $row['category_id'];
				
				if(!$cat_1) {
					$tmp = $this->create_hs_category((string)$this->xml->categories_name_1, $cat_0);
					$cat_1 = $tmp->category_id;
				}
				$cat_id = $cat_1;
			}
			
			return $cat_id;
			
		}
	}
}
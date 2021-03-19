<?php
/*
 *	@author: qCazelles 
 */
//Link languages to a Product
require_once('_securityCheck.php');
class ProductLanguage extends SqlElement {
	
	// extends SqlElement, so has $id
	public $id;    // redefine $id to specify its visible place
	public $idProduct;
	public $scope;
	public $idLanguage;
	public $creationDate;
	public $idUser;
	public $idle;
	
	/** ==========================================================================
	 * Constructor
	 * @param $id the id of the object in the database (null if not stored yet)
	 * @return void
	 */
	function __construct($id = NULL, $withoutDependentObjects=false) {
		parent::__construct($id,$withoutDependentObjects);
	}
	
	
	/** ==========================================================================
	 * Destructor
	 * @return void
	 */
	function __destruct() {
		parent::__destruct();
	}
	
	
}
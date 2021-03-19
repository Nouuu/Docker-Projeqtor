<?php
/*
 *	@author: qCazelles 
 */
//Link Contexts to a Product
require_once('_securityCheck.php');
class ProductContext extends SqlElement {
	
	public $id;
	public $idProduct;
	public $scope;
	public $idContext;
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
?>
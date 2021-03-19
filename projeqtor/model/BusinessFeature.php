<?php

require_once('_securityCheck.php');
class BusinessFeature extends SqlElement {
	
	public $id;
	public $name;
	public $idProduct;
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
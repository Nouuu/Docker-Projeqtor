<?php
/*
 *	@author: qCazelles 
 */

require_once('_securityCheck.php');
class Language extends SqlElement {
	
	// extends SqlElement, so has $id
	public $_sec_Description;
	public $id;    // redefine $id to specify its visible place
	public $name;
	public $code;
	public $sortOrder=0;
	public $idle;
	//public $_sec_void;
	
	// Define the layout that will be used for lists
	private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="30%">${name}</th>
    <th field="sortOrder" width="5%">${sortOrderShort}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
	
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
	
	/** ==========================================================================
	 * Return the specific layout
	 * @return the layout
	 */
	protected function getStaticLayout() {
		return self::$_layout;
	}
	
	
}
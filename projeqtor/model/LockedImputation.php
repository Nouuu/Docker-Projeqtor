<?php
/*
 *	@author: qCazelles 
 */

require_once('_securityCheck.php');
class LockedImputation extends SqlElement {
	
	// extends SqlElement, so has $id
	public $_sec_Description;
	public $id;    // redefine $id to specify its visible place
	public $idProject;
	public $idResource;
	public $month;
	public $_isNameTranslatable = true;
	//public $_sec_void;
	
	private static $_databaseTableName = 'lockedImputation';
	private static $_databaseCriteria = array();
	/** ==========================================================================
	 * Constructor
	 * @param $id the id of the object in the database (null if not stored yet)
	 * @return void
	 */
	/** ========================================================================
	 * Return the specific databaseTableName
	 * @return the databaseTableName
	 */
	protected function getStaticDatabaseTableName() {
	  $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
	  return $paramDbPrefix . self::$_databaseTableName;
	}
	
	/** ========================================================================
	 * Return the specific database criteria
	 * @return the databaseTableName
	 */
	protected function getStaticDatabaseCriteria() {
	  return self::$_databaseCriteria;
	}
	
	/** ==========================================================================
	 * Construct
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
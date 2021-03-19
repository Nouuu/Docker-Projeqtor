<?php
/*
 *	@author: qCazelles 
 */

require_once('_securityCheck.php');
class UnitNotice extends UnitContract {


  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place
  public $name;
  public $sortOrder=0;
  public $idle;
  //public $_sec_void;
  
	// Define the layout that will be used for lists
  //private static $_databaseCriteria = array('scope'=>'UnitContract');
  
  private static $_databaseTableName = 'unitcontract';
  private static $_databaseCriteria = array();
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

// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  
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
}
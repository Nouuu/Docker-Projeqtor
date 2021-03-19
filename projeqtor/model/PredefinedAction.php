<?php 
/*
 * @author : qCazelles
 */
require_once('_securityCheck.php');
class PredefinedAction extends SqlElement {

	public $_sec_description;
	public $id;    // redefine $id to specify its visible place
	public $name;
	public $idActionType;
	public $idProject;
	public $creationDate;
	public $idUser;
	public $idPriority;
	public $idContact;
	public $isPrivate;
	public $description;
	public $_sec_treatment;
	public $idStatus;
	public $idResource;
	public $idle;
	
	public $initialDueDateDelay; //NOW INTS
	public $actualDueDateDelay;
	
	public $idEfficiency;
	public $result;
 
	private static $_fieldsAttributes=array("id"=>"",
			"name"=>"required",
			"reference"=>"hidden",
			"idle"=>"hidden"
	);  
  
	private static $_colCaptionTransposition = array('idResource'=> 'responsible',
                                                    'idContact' => 'requestor');
  
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

    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
  	return self::$_colCaptionTransposition;
  }
  
}
?>
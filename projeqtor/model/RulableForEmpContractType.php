<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

/** ============================================================================
 * 
 */ 
require_once('_securityCheck.php'); 
class RulableForEmpContractType extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
    public $id;    // redefine $id to specify its visible place 
    public $rulableItem;
    public $name;
    public $idle;

    public $_isNameTranslatable = true;
    
  public $_nbColMax=3;
  // Define the layout that will be used for lists
  private static $_layout='';

  private static $_fieldsAttributes=array(
                                            "rulableItem"             => "required, hidden",
                                         );  
  
  private static $_colCaptionTransposition = array();
  
  private static $_databaseColumnName = array();
  
//    private static $_databaseTableName = '';
    
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
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
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

  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    return $colScript;
  }
  
  /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    //$result.='<br/>' . i18n('ERROR');
        
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {

    //$spe_notifiableItem = RequestHandler::getValue('_spe_notifiableItem');    
    //$array_class = getUserVisibleObjectClassWithFieldDateType();
    //$this->notifiableItem = (isset($array_class[$spe_notifiableItem]))?$_spe_notifiableItem:'';
    $this->name = i18n($this->rulableItem);
    
    $result = parent::save();
    return $result;
    
  }

  /**
   * Overrides SqlElement::delete() function to add specific treatments
   * @return the return message of persistence/SqlElement#delete() method
   */
  public function delete() {
    $result=parent::delete();
    return $result;
  }
  
  
// =============================================================================================================
// MISCELANOUS FUNCTION
// =============================================================================================================

}
?>
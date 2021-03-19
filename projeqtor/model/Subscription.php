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

/* ============================================================================
 * Subscription is a way to follow an item (some email can be sent to subscribers)
 */  
require_once('_securityCheck.php');
class Subscription extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;
  public $idAffectable;
  public $refType;
  public $refId;
  public $idUser;
  public $creationDateTime;
  public $comment;
  public $isAutoSub;
  
  //public $_noHistory=true;
    
  private static $_fieldsAttributes=array("refType"=>"required", 
  		 "refId"=>"required"
  );
  
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
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
  * @see persistence/SqlElement#save()
  * @return the return message of persistence/SqlElement#save() method
  */
  public function save() {
    if($this->refType=='Product' or $this->refType=='Component'){
      adAutoSub($this);
    }
    $result = parent::save();
    return $result;
  }
  
  public function delete() {
    if($this->refType=='Product' or $this->refType=='Component'){
      deleteAutoSub($this);
    }
  	$result = parent::delete();
    return $result;
  }
  
}
?>
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
 * Note is an object that can be included in all objects, as comments.
 */ 
require_once('_securityCheck.php');
class Note extends SqlElement {

  public $id;
  public $idProject;
  public $refType;
  public $refId;
  public $idUser;
  public $creationDate;
  public $updateDate;
  public $note;
  public $idPrivacy;
  public $idTeam;
  public $fromEmail;
  public $idle;
  public $idNote;//id ParentNote
  public $replyLevel;//reply indention Level
    
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
// GET VALIDATION SCRIPT
// ============================================================================**********
 
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
  }
    
  public function save() {
    $class = $this->refType;
    $id = $this->refId;
    $obj = new $class($id);
    if ($class=='Project') {
    	$this->idProject=$obj->id;
    } else if (property_exists($class, 'idProject') ) {
    	$this->idProject=$obj->idProject;
    }
    if (property_exists($obj,'idle')) $this->idle=$obj->idle;
    $result = parent::save ();
    if ($this->idPrivacy != 3) {
      if ($obj and $obj->id and property_exists ( $class, 'lastUpdateDateTime' ) and !SqlElement::$_doNotSaveLastUpdateDateTime) {
        $obj->lastUpdateDateTime = date ( "Y-m-d H:i:s" );
        $resObj=$obj->saveForced();
      }
    }
    return $result;
  }
  
  public function deleteControl(){
    $result="";
    $cptNote = $this->countSqlElementsFromCriteria(array('idNote'=>$this->id));
    if($cptNote != 0){
      $result .= "<br/>" . i18n("errorDeleteParentNote");
    }
    if (!$result) {
      $result=parent::deleteControl();
    }
    return $result;
  }
  
  public function control(){
    $result="";
    if(! $this->id) { // New note
      $class = $this->refType;
      $id = $this->refId;
      if (property_exists($class, 'idle')){
        $obj = new $class($id);
        if ($obj->idle) {
          $result .= "<br/>" . i18n("errorAddOnClosedItem");
        }
      } 
    }
    if (!$result) {
      $result=parent::control();
    }
    return $result;
  }
  public function delete() {
    $result = parent::delete ();
    if ($this->idPrivacy != 3) {
      $class = $this->refType;
      $id = $this->refId;
      $obj = new $class( $id );
      if ($obj and $obj->id and property_exists ( $class, 'lastUpdateDateTime' ) and !SqlElement::$_doNotSaveLastUpdateDateTime) {
        $obj->lastUpdateDateTime = date ( "Y-m-d H:i:s" );
        $resObj=$obj->saveForced();
      }
    }
    return $result;
  }
}
?>
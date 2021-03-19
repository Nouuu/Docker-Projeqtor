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
 * Attachment is an object that can be included in all objects, 
 * to trace file uploads and link it to objects.
 */ 
require_once('_securityCheck.php'); 
class Attachment extends SqlElement {

  public $id;
  public $refType;
  public $refId;
  public $idUser;
  public $creationDate;
  public $fileName;
  public $description;
  public $subDirectory;
  public $mimeType; 
  public $fileSize;
  public $type;
  public $link;
  public $idPrivacy;
  public $idTeam;
    
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
  
  public function delete() {
   // begin Gautier add
    $result = parent::delete();
     
    if ($this->idPrivacy != 3) {
      $class = $this->refType;
      $id = $this->refId;
      $obj = new $class( $id );
  
      if ($obj and $obj->id and property_exists ( $class, 'lastUpdateDateTime' ) and !SqlElement::$_doNotSaveLastUpdateDateTime) {
        $obj->lastUpdateDateTime = date ( "Y-m-d H:i:s" );
        $resObj=$obj->saveForced();
      }
    }
    // End Gautier Add
    $paramPathSeparator = Parameter::getGlobalParameter ( 'paramPathSeparator' );
    $paramAttachmentDirectory = Parameter::getGlobalParameter ( 'paramAttachmentDirectory' );
    return parent::delete ();
    $subDirectory = str_replace ( '${attachmentDirectory}', $paramAttachmentDirectory, $this->subDirectory );
    if (! strpos ( $result, 'id="lastOperationStatus" value="OK"' )) {
      return $result;
    }
    enableCatchErrors ();
    if (file_exists ( $subDirectory . $paramPathSeparator . $this->fileName )) {
      unlink ( $subDirectory . $paramPathSeparator . $this->fileName );
    }
    if (file_exists ( $subDirectory )) {
      purgeFiles ( $subDirectory, null );
      rmdir ( $subDirectory );
    }
    disableCatchErrors ();
    return $result;
  }
  
  public function save() {
    $result = parent::save ();
    if ($this->idPrivacy != 3) {
      $class = $this->refType;
      $id = $this->refId;
      $obj = new $class( $id );
      if ($obj and $obj->id and property_exists( $class, 'lastUpdateDateTime' ) and !SqlElement::$_doNotSaveLastUpdateDateTime) { 
        $obj->lastUpdateDateTime = date ( "Y-m-d H:i:s" );
        $resObj=$obj->saveForced();
      }
    }
    return $result;
  }
   
  public function getFullPathFileName() {
  	$path = str_replace('${attachmentDirectory}', Parameter::getGlobalParameter('paramAttachmentDirectory'), $this->subDirectory);
  	$name = $this->fileName;
  	$file = $path . $name;
  	return $file;
  }
  
  public function isThumbable() {
    return isThumbable($this->fileName);
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
}
?>
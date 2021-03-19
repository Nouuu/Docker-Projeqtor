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
 * User is a resource that can connect to the application.
 */  
require_once('_securityCheck.php');
class Approver extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place
  public $refType;
  public $refId;
  public $idAffectable;
  public $approved;
  public $approvedDate;
  public $disapproved;
  public $disapprovedDate;
  public $disapprovedComment;
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

// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
 function save() {
   $result=parent::save();
   if ($this->refType=="Document") {
     // If add an approver to Document, then add it to current DocumentVersion
     $doc=new Document($this->refId);
     if ($doc->idDocumentVersion) {
       $crit=array('refType'=>'DocumentVersion','refId'=>$doc->idDocumentVersion,'idAffectable'=>$this->idAffectable);
       $app=SqlElement::getSingleSqlElementFromCriteria('Approver',$crit);
       if (!$app->id) {
         $app->save();
       }
     }
   }
   if ($this->refType=="DocumentVersion") {
     // On update check approvement : update document version status depending on approvement
     $vers=new DocumentVersion($this->refId);
     $vers->checkApproved();
   }
   return $result;
 }

  function control() {
    $result="";
    if (! $this->id) {
      $check=SqlElement::getSingleSqlElementFromCriteria('Approver',array('refType'=>$this->refType,'refId'=>$this->refId, 'idAffectable'=>$this->idAffectable));
      if ($check->id) {
        $result.='<br/>' . i18n('errorDuplicateApprover');
      }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }

  function delete() {
    $result=parent::delete();
    if ($this->refType=="Document") {
      // If delete an approver to Document, then delete it from current DocumentVersion
      $doc=new Document($this->refId);
      if ($doc->idDocumentVersion) {
        $crit=array('refType'=>'DocumentVersion','refId'=>$doc->idDocumentVersion,'idAffectable'=>$this->idAffectable);
        $app=SqlElement::getSingleSqlElementFromCriteria('Approver',$crit);
        if ($app->id) {
          $app->delete();
        }
      }
    }
    if ($this->refType=="DocumentVersion") {
      // On update check approvement : update document version status depending on approvement
      $vers=new DocumentVersion($this->refId);
      $vers->checkApproved();
    }
    return $result;
  }

}
?>
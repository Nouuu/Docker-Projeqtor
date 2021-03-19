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
 * Menu defines list of items to present to users.
 */ 
require_once('_securityCheck.php');
class ResourceSupport extends SqlElement {

  public $id;
  public $idResource;
  public $idSupport;
  public $rate;
  public $description;
  
  
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
  
  public function control(){
    $result="";
    if ($result=="") {
      $result='OK';
    }
    
    if($this->idResource == $this->idSupport){
    	$result=i18n('errorCannotSelfSupport');
    }
    
    $resInc = new ResourceIncompatible();
    $inc = $resInc->getSingleSqlElementFromCriteria('ResourceIncompatible', array('idResource'=>$this->idSupport, 'idIncompatible'=>$this->idResource));
    if($inc->id){
    	$result=i18n('errorAlreadyIncompatible');
    }
    $resSup = new ResourceSupport();
    $supp = $resSup->getSingleSqlElementFromCriteria('ResourceSupport', array('idResource'=>$this->idResource, 'idSupport'=>$this->idSupport));
    if($supp->id and $supp->id!=$this->id){
    	$result=i18n('errorDuplicate');
    }
    return $result;
  }
  
  public function manageSupportAssignment($ass) {
    if ($ass->refType!='Activity' and $ass->refType!='TestSession') return null;
    $asSup=SqlElement::getSingleSqlElementFromCriteria('Assignment', array('supportedAssignment'=>$ass->id,'idResource'=>$this->idSupport));
    if (!$asSup->id) {
      $asSup->idResource=$this->idSupport;
      $asSup->idProject=$ass->idProject;
      $asSup->refType=$ass->refType;
      $asSup->refId=$ass->refId;
      $asSup->idRole=$ass->idRole;
      $asSup->realWork=0;
      $asSup->supportedAssignment=$ass->id;
      $asSup->supportedResource=$ass->idResource;
      $asSup->hasSupport=0;
    }
    $asSup->rate=($this->rate*$ass->rate/100);
    $asSup->assignedWork=round($ass->assignedWork*$this->rate/100,5);
    $asSup->leftWork=round($ass->leftWork*$this->rate/100,5);
    $asSup->plannedWork=$asSup->realWork+$asSup->leftWork;
    $asSup->idle=$ass->idle;
    $asSup->save();
    return $asSup;
  }
  
  public function save() {
    $result=parent::save();
    $ass=new Assignment();
    $assList=$ass->getSqlElementsFromCriteria(array('idResource'=>$this->idResource));
    foreach ($assList as $ass) {
      $this->manageSupportAssignment($ass);
      if (getLastOperationStatus($result)=='OK' and !$ass->hasSupport) {
        $ass->hasSupport=1;
        $ass->simpleSave();
      }
    }
    
    return $result;
  }
  
  public function delete() {
    $result=parent::delete();
    $ass=new Assignment();
    $assList=$ass->getSqlElementsFromCriteria(array('supportedResource'=>$this->idResource,'idResource'=>$this->idSupport));
    foreach ($assList as $ass) {
      $ass->delete();
    }
    $cpt=$this->countSqlElementsFromCriteria(array('idResource'=>$this->idResource));
    if ($cpt==0) {
      $assList=$ass->getSqlElementsFromCriteria(array('idResource'=>$this->idResource));
      foreach ($assList as $ass) {
        $ass->hasSupport=0;
        $ass->simpleSave();
      }
    }
    return $result;
  }
}
?>
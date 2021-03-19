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
 * Habilitation defines right to the application for a menu and a profile.
 */ 
require_once('_securityCheck.php');
class Dependency extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $predecessorId;
  public $predecessorRefType;
  public $predecessorRefId;
  public $successorId;
  public $successorRefType;
  public $successorRefId;
  public $dependencyType;
  public $dependencyDelay;
  public $comment;
  
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
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  

 /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    // Must have write access to successor to create link
    $succClass=$this->successorRefType;
    if ($succClass and SqlElement::class_exists($succClass)) {
      $succ=new $succClass($this->successorRefId);
      $canUpdateSucc=(securityGetAccessRightYesNo('menu' . $succClass, 'update', $succ)=='YES');
      if (! $canUpdateSucc) {
        return '<br/>' . i18n('errorUpdateRights');
      }
    }
  	if ($this->id) return "OK";
    $result="";
    $this->predecessorRefId=intval($this->predecessorRefId);
    $this->successorRefId=intval($this->successorRefId);
    $old=$this->getOld();
    if (!$old->id) { // On creation, for pseudo PE, insert elements
      $pex=PlanningElementExtension::checkInsert($this->predecessorRefType,$this->predecessorRefId);
      if ($pex and $pex->id) {
        $this->predecessorId=$pex->getFakeId();
      }
      $pex=PlanningElementExtension::checkInsert($this->successorRefType,$this->successorRefId);
      if ($pex and $pex->id) {
        $this->successorId=$pex->getFakeId();
      }
    }
    // control duplicate
    $crit=array('successorRefType'=>$this->successorRefType, 'successorRefId'=>$this->successorRefId,
                'predecessorRefType'=>$this->predecessorRefType, 'predecessorRefId'=>$this->predecessorRefId);
    $list=$this->getSqlElementsFromCriteria($crit);
    if (count($list)>0) {
    	$result.='<br/>' . i18n('errorDuplicateDependency');
    }
    $prec=new PlanningElement();
    $precList=array();
    $precParentList=array();
    $precSonList=array();
    if ($this->predecessorId) {
      $prec=new PlanningElement($this->predecessorId);
      if (!$prec->id) $prec->id=$this->predecessorId;
      $precList=$prec->getPredecessorItemsArrayIncludingParents();
      $precParentList=$prec->getParentItemsArray();
      $precSonList=$prec->getSonItemsArray();
    }
    $succ=new PlanningElement();
    $succList=array();
    $succParentList=array();
    $succSonList=array();
    if ($this->successorId) {
      $succ=new PlanningElement($this->successorId);
      if (!$succ->id) $succ->id=$this->successorId;
      $succList=$succ->getSuccessorItemsArrayIncludingParents();
      $succParentList=$succ->getParentItemsArray();
      $succSonList=$succ->getSonItemsArray();
    }
    if ($this->predecessorId) { // Case PlanningElement Dependency
      if (array_key_exists('#' . $this->successorId,$precList)) {
        $result.='<br/>(1)' . i18n('errorDependencyLoop');
      }
      // cannot create dependency into parent hierarchy
	    if (array_key_exists('#' . $this->successorId,$precParentList)) {
	      $result.='<br/>(2)' . i18n('errorDependencyHierarchy');
	    }
	    foreach ($succParentList as $idSuccParent=>$succParent) {
  	    if (array_key_exists($idSuccParent,$precList)) {
          $result.='<br/>(3)' . i18n('errorDependencyLoop');
        }
	    }
	    foreach ($succSonList as $idSuccSon=>$succSon) {
	      if (array_key_exists($idSuccSon,$precList)) {
	        $result.='<br/>(4)' . i18n('errorDependencyLoop');
	      }
	    }
    } else {
    	$precList=$this->getPredecessorList();
    	$precParentList=array();
      if (array_key_exists($this->successorRefType . '#' . $this->successorRefId,$precList)) {
        $result.='<br/>(5)' . i18n('errorDependencyLoop');
      }
    }
    if ($this->successorId) { // Case PlanningElement Dependency
      $succ=new PlanningElement($this->successorId);    
      $succList=$succ->getSuccessorItemsArrayIncludingParents();
      $succParentList=$succ->getParentItemsArray();
      if (array_key_exists('#' .$this->predecessorId,$succList)) {
        $result.='<br/>(6)' . i18n('errorDependencyLoop');
      }
      // cannot create dependency into parent hierarchy
	    if (array_key_exists('#' .$this->predecessorId,$succParentList)) {
	      $result.='<br/>(7)' . i18n('errorDependencyHierarchy');
	    }
	    foreach ($precParentList as $idPrecParent=>$precParent) {
	      if (array_key_exists($idPrecParent,$succList)) {
	        $result.='<br/>(8)' . i18n('errorDependencyLoop');
	      }
	    }
	    foreach ($precSonList as $idPrecSon=>$precSon) {
	      if (array_key_exists($idPrecSon,$succList)) {
	        $result.='<br/>(9)' . i18n('errorDependencyLoop');
	      }
	    }
    } else {
    	$succList=array();
    	$succParentList=array();
      if (array_key_exists($this->predecessorRefType . '#' . $this->predecessorRefId,$succList)) {
        $result.='<br/>(10)' . i18n('errorDependencyLoop');
      }
    } 
    if ($this->predecessorRefType==$this->successorRefType and $this->predecessorRefId==$this->successorRefId) {
      $result.='<br/>(11)' . i18n('errorDependencyLoop');
    }
    if($this->predecessorRefType=='PeriodicMeeting' or $this->successorRefType=='PeriodicMeeting'){
    	$result.='<br/>' . i18n('errorDependencyPeriodicMeeting');
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    } 
    return $result;
  }
  
  private function getPredecessorList() {
  	$crit=array('successorRefType'=>$this->predecessorRefType, 'successorRefId'=>$this->predecessorRefId);
  	$list=$this->getSqlElementsFromCriteria($crit, false, null, null, true);
  	$result=array();
  	foreach ($list as $obj) {
  		$result[$obj->predecessorRefType.'#'.$obj->predecessorRefId]=$obj;  
      if ($obj->id!=$this->id) {		
  	    $result=array_merge_preserve_keys($result,$obj->getPredecessorList());
      }
  	}
  	return $result;
  }
  
  private function getSuccessorList() {
    $crit=array('predecessorRefType'=>$this->successorRefType, 'predeccessorRefId'=>$this->succecessorRefId);
    $list=$this->getSqlElementsFromCriteria($crit, false, null, null, true);
    $result=array();
    foreach ($list as $obj) {
      $result[$obj->successorRefType.'#'.$obj->successorRefId]=$obj;  
      if ($obj->id!=$this->id) {    
        $result=array_merge_preserve_keys($result,$obj->getSuccessorList());
      }
    }
    return $result;    
  }
  public function save() {
    $old=$this->getOld();
    $result=parent::save();
    if ($this->predecessorRefType=='Term' or $this->successorRefType=='Term'
     or $this->predecessorRefType=='TestCase' or $this->successorRefType=='TestCase'
     or $this->predecessorRefType=='Requirement' or $this->successorRefType=='Requirement') {
      return $result;
    }
    if (!$old->id or $this->dependencyType!=$old->dependencyType or $this->dependencyDelay!=$old->dependencyDelay ) {
      $peP=new PlanningElement($this->predecessorId);
      Project::setNeedReplan($peP->idProject);
      $peS=new PlanningElement($this->successorId);
      if ($peS->idProject!=$peP->idProject) {
        Project::setNeedReplan($peS->idProject);
      }
    }
    return $result;
  }
  public function delete() {
    $result=parent::delete();
    $peP=new PlanningElement($this->predecessorId);
    Project::setNeedReplan($peP->idProject);
    $peS=new PlanningElement($this->successorId);
    if ($this->predecessorRefType=='Term' or $this->successorRefType=='Term'
     or $this->predecessorRefType=='TestCase' or $this->successorRefType=='TestCase'
     or $this->predecessorRefType=='Requirement' or $this->successorRefType=='Requirement') {
      return $result;
    }
    if ($peS->idProject!=$peP->idProject) {
      Project::setNeedReplan($peS->idProject);
    }
    PlanningElementExtension::checkDelete($this->predecessorRefType,$this->predecessorRefId);
    PlanningElementExtension::checkDelete($this->successorRefType,$this->successorRefId);
    
    return $result;
  }
  
  public function deleteControl() {
    // Must have write access to successor to remove link
    $result="";
    $succClass=$this->successorRefType;
    if ($succClass and SqlElement::class_exists($succClass)) {
      $succ=new $succClass($this->successorRefId);
      $canUpdateSucc=(securityGetAccessRightYesNo('menu' . $succClass, 'update', $succ)=='YES');
      if (! $canUpdateSucc) {
        $result.='<br/>' . i18n('errorUpdateRights');
      }
    }
    if (! $result) {
      $result=parent::deleteControl();
    }
    return $result;
  }
  
}
?>
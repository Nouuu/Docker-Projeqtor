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
class Baseline extends SqlElement {

  public $id;
  public $idProject;
  public $baselineNumber;
  public $name;
  public $baselineDate;
  public $idUser;
  public $creationDateTime;
  public $idPrivacy;
  public $idTeam;
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
// GET VALIDATION SCRIPT
// ============================================================================**********
    
  public function saveWithPlanning() {
    global $saveBaselineInProgress;
    $saveBaselineInProgress=true;
    // Remove existing for same date : only one baseline a day
    $crit=array('idProject'=>$this->idProject,'baselineDate'=>$this->baselineDate);
    $list=$this->getSqlElementsFromCriteria($crit);
    foreach ($list as $base) {
      $base->deleteWithPlanning();
    }
    $result = parent::save();
    $this->copyItem('PlanningElement');
    $this->copyItem('PlannedWork');
    return $result;
  }
  
  public function deleteWithPlanning() {
    $clause='idBaseline='.Sql::fmtId($this->id);
    $pwb=new PlannedWorkBaseline();
    $pwb->purge($clause);
    $peb=new PlanningElementBaseline();
    $peb->purge($clause);
    $result=parent::delete();
    return $result;
  }
  
  public function copyItem($itemFrom) {
    global $saveBaselineInProgress;
    if ($itemFrom=='PlanningElement' and RequestHandler::getBoolean('isGlobalPlanning')) {
      $objFrom=new GlobalPlanningElement();
    } else {
      $objFrom=new $itemFrom();
    }
    $tableFrom=$objFrom->getDatabaseTableName();
    $itemTo=$itemFrom.'Baseline';
    $objTo=new $itemTo();
    $tableTo=$objTo->getDatabaseTableName();
    $colList="";
    foreach ($objFrom as $fld=>$val) {
      if (substr($fld,0,1)=='_' or $fld=='id') continue;
      $col=$objFrom->getDatabaseColumnName($fld);
      if ($col) {
        $colList.="$col, ";
      }
    }
    $idBaseline=$this->id;
    $proj=new Project($this->idProject,true);
    $query="INSERT INTO $tableTo ($colList idBaseline)\n"
        ."SELECT $colList $idBaseline FROM $tableFrom as $itemFrom \n"
        ." where idProject in ".transformListIntoInClause($proj->getRecursiveSubProjectsFlatList(true, true));
    $res=SqlDirectElement::execute($query);
    if ($itemFrom=='PlannedWork') { // Also include existing real work
      $objFrom=new Work();
      $tableFrom=$objFrom->getDatabaseTableName();
      $colListWork=str_replace(array('surbookedWork','surbooked'), array('0','0'), $colList);
      $query="INSERT INTO $tableTo ($colList idBaseline, isRealWork)\n"
      ."SELECT $colListWork $idBaseline, 1 FROM $tableFrom \n"
      ." where idProject in ".transformListIntoInClause($proj->getRecursiveSubProjectsFlatList(true, true));
      $res=SqlDirectElement::execute($query);
    }
  }
  
}
?>
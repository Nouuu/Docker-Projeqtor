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
class TenderEvaluationCriteria extends SqlElement {

  public $id;
  public $idCallForTender;
  public $criteriaName;
  public $criteriaMaxValue;
  public $criteriaCoef;
    
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

  function save() {
    $result=parent::save();
    $this->updateCallForTender();
    $this->updateTenders();
    return $result;
  }
  function delete() {
    $result=parent::delete();
    $this->updateCallForTender();
    $this->removeEvaluations();
    $this->updateTenders();
    return $result;
  }
  function updateCallForTender() {
    $cft=new CallForTender($this->idCallForTender);
    $cft->updateEvaluationMaxValue(true);
  }
  function removeEvaluations() {
    $te=new TenderEvaluation();
    $te->purge('idTenderEvaluationCriteria='.Sql::fmtId($this->id));
  }
  function updateTenders() {
    $tender=new Tender();
    $list=$tender->getSqlElementsFromCriteria(array('idCallForTender'=>$this->idCallForTender));
    foreach($list as $tender) {
      $tender->save();
    }
  }
    
}
?>
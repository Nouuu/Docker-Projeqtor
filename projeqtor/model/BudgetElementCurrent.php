<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2016 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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
 * Budget Element is an object included in all objects that can be budgeted.
 */ 
require_once('_securityCheck.php');
class BudgetElementCurrent extends BudgetElement {

  public $id;
  public $refType;
  public $refId;
  public $year;
  public $refName;
  public $_tab_5_5_smallLabel = array('validated','assigned','real','left','reassessed',
      'work','cost','expense','reserveAmountShort','totalCost');
  public $validatedWork;
  public $assignedWork;
  public $realWork;
  public $leftWork;
  public $plannedWork;
  public $validatedCost;
  public $assignedCost;
  public $realCost;
  public $leftCost;
  public $plannedCost;
  public $expenseValidatedAmount;
  public $expenseAssignedAmount;
  public $expenseRealAmount;
  public $expenseLeftAmount;
  public $expensePlannedAmount;
  public $_void_res_11;
  public $_void_res_12;
  public $_void_res_13;
  public $reserveAmount;
  public $_void_res_15;
  public $totalValidatedCost;
  public $totalAssignedCost;
  public $totalRealCost;
  public $totalLeftCost;
  public $totalPlannedCost;
  
  public $topId;
  public $topRefType;
  public $topRefId;
  public $elementary;
  
  public $budgetWork;
  public $budgetCost;
  public $expenseBudgetAmount;
  public $totalBudgetCost;
  
  public $idle;
  public $idleDateTime;
  
  private static $_fieldsAttributes=array(
      "id"=>"hidden",
      "year"=>"hidden",
      "budgetWork"=>"hidden",
      "budgetCost"=>"hidden",
      "refType"=>"hidden",
      "refId"=>"hidden",
      "refName"=>"hidden",
      "progress"=>"display,noImport",
      "topId"=>"hidden",
      "topRefType"=>"hidden",
      "topRefId"=>"hidden",
      "idle"=>"hidden",
// ADD BY Marc TABARY - 2017-03-13 - PERIODIC YEAR BUDGET ELEMENT      
      "idleDateTime"=>"hidden",
// END ADD BY Marc TABARY - 2017-03-13 - PERIODIC YEAR BUDGET ELEMENT      
      "validatedWork"=>"readonly,noImport",
      "assignedWork"=>"readonly,noImport",
      "realWork"=>"readonly,noImport",
      "leftWork"=>"readonly,noImport",
      "plannedWork"=>"readonly,noImport",
      "validatedCost"=>"readonly,noImport",
      "assignedCost"=>"readonly,noImport",
      "realCost"=>"readonly,noImport",
      "leftCost"=>"readonly,noImport",
      "plannedCost"=>"readonly,noImport",
      "expenseAssignedAmount"=>"readonly,noImport",
      "expensePlannedAmount"=>"readonly,noImport",
      "expenseRealAmount"=>"readonly,noImport",
      "expenseLeftAmount"=>"readonly,noImport",
      "expenseValidatedAmount"=>"readonly,noImport",
      "reserveAmount"=>"readonly,noImport",
      "totalValidatedCost"=>"readonly,noImport",
      "totalAssignedCost"=>"readonly,noImport",
      "totalRealCost"=>"readonly,noImport",
      "totalLeftCost"=>"readonly,noImport",
      "totalPlannedCost"=>"readonly,noImport",
      "done"=>"readonly,noImport",
      "cancelled"=>"readonly,noImport",
      "totalPlannedCost"=>"readonly,noImport",
      "budgetWork"=>"hidden,noImport",
      "budgetCost"=>"hidden,noImport",
      "expenseBudgetAmount"=>"hidden,noImport",
      "totalBudgetCost"=>"hidden,noImport",
      "elementary"=>"hidden"
  
  );
  
  private static $_databaseCriteria = array('year'=>'0');
  
  /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    $this->year=null;
    parent::__construct($id,$withoutDependentObjects);
  }
  
  /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
  
}
?>
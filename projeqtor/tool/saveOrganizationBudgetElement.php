<?php
// ADD BY Marc TABARY - 2017-03-13 - PERIODIC YEAR BUDGET ELEMENT

/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2016 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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

/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/saveOrganizationBudgetElement.php');

if (! array_key_exists('AddChangeBudgetElementAction',$_REQUEST)) {
  throwError('action parameter not found in REQUEST');
}
$action = $_REQUEST['AddChangeBudgetElementAction'];

if (! array_key_exists('AddChangeBudgetElementRefId',$_REQUEST)) {
  throwError('refId parameter not found in REQUEST');
}
$refId = $_REQUEST['AddChangeBudgetElementRefId'];

if (! array_key_exists('AddChangeBudgetElementId',$_REQUEST)) {
  throwError('id parameter not found in REQUEST');
}
$id = $_REQUEST['AddChangeBudgetElementId'];    

if (! array_key_exists('AddChangeBudgetElementYear',$_REQUEST)) {
  throwError('year parameter not found in REQUEST');
}
$year = $_REQUEST['AddChangeBudgetElementYear'];

if (! array_key_exists('AddChangeBudgetElementScope',$_REQUEST)) {
  throwError('scope parameter not found in REQUEST');
}
$scope = $_REQUEST['AddChangeBudgetElementScope'];

if (! array_key_exists('AddChangeBudgetElementBudgetWork',$_REQUEST)) {
  throwError('budgetWork parameter not found in REQUEST');
}
$budgetWork = $_REQUEST['AddChangeBudgetElementBudgetWork'];
$budgetWork=Work::convertWork($budgetWork);
//$budgetWork = str_replace(' ','',str_replace(',', '.', $budgetWork));

if (! array_key_exists('AddChangeBudgetElementBudgetCost',$_REQUEST)) {
  throwError('budgetCost parameter not found in REQUEST');
}
$budgetCost = $_REQUEST['AddChangeBudgetElementBudgetCost'];
$budgetCost = str_replace(' ','',str_replace(',', '.', $budgetCost));

if (! array_key_exists('AddChangeBudgetElementBudgetExpenseAmount',$_REQUEST)) {
  throwError('budgetExpenseAmount parameter not found in REQUEST');
}
$budgetExpenseAmount = $_REQUEST['AddChangeBudgetElementBudgetExpenseAmount'];
$budgetExpenseAmount = str_replace(' ','',str_replace(',', '.', $budgetExpenseAmount));
Sql::beginTransaction();
$result = '';
if($action=='ADD') {
    // In fact, ADD consist to clone an existing BudgetElement of organization
    // and after cloning, change year, budgetWork, budgetCost, budgetExpenseAmount
    // With this way, not need to recalculed elementary, topId, etc.
    $bE = new BudgetElement();
    $crit=array('refId'=>$refId,
                'refType'=>$scope,
                'idle'=>'0');
    $bEList = $bE->getSqlElementsFromCriteria($crit,true);
    if (count($bEList)==0) {
        throwError('No Budget Element to clone!');
        return;
    }
    $bE = clone($bEList[0]);
    $bE->id=null;
    $bE->year = $year;
    $bE->budgetWork = $budgetWork;
    $bE->budgetCost = $budgetCost;
    $bE->expenseBudgetAmount = $budgetExpenseAmount;
    $bE->totalBudgetCost = $budgetCost + $budgetExpenseAmount;
    $result = $bE->simpleSave();
    // To calculate synthesis
    $orga = new Organization($refId,false,$bE);
    $orga->updateBudgetElementSynthesis($bE);
} else {
    $bE=new BudgetElement($id,true);
    if ($bE->budgetWork!=$budgetWork or $bE->budgetCost!=$budgetCost or $bE->expenseBudgetAmount!=$budgetExpenseAmount) {
        $bE->budgetWork = $budgetWork;
        $bE->budgetCost = $budgetCost;
        $bE->expenseBudgetAmount = $budgetExpenseAmount;
        $bE->totalBudgetCost = floatval($budgetCost) + floatval($budgetExpenseAmount);
        $result = $bE->simpleSave();
    }
}
//gautier #4360
if(getLastOperationStatus($result)=='OK'){
  if(!isset($orga)){
    $orga =  new Organization($bE->refId,true);
  }
  $orga->lastUpdateDateTime = date( "Y-m-d H:i:s" );
  $test = $result;
  $result = $orga->saveForced();
  $result .= $test;
}
$_REQUEST['objectClass']='Organization';
$_REQUEST['objectId']=$refId;
$_REQUEST['OrganizationBudgetPeriod']=$year;
//include '../view/objectDetail.php';
displayLastOperationStatus($result);
// END ADD BY Marc TABARY - 2017-02-13 - PERIODIC YEAR BUDGET ELEMENT
?>
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

/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

$expenseDetailId=null;
if (array_key_exists('expenseDetailId',$_REQUEST)) {
  $expenseDetailId=$_REQUEST['expenseDetailId'];
}
$expenseDetailId=trim($expenseDetailId);
if ($expenseDetailId=='') {
  $expenseDetailId=null;
}

// Get the assignment info
if (! array_key_exists('idExpense',$_REQUEST)) {
  throwError('idExpense parameter not found in REQUEST');
}
$idExpense=$_REQUEST['idExpense'];


$expenseDetailName=null;
if (array_key_exists('expenseDetailName',$_REQUEST)) {
  $expenseDetailName=$_REQUEST['expenseDetailName'];
}
$expenseDetailReference=null;
if (array_key_exists('expenseDetailReference',$_REQUEST)) {
  $expenseDetailReference=$_REQUEST['expenseDetailReference'];
}

$expenseDetailDate=null;
if (array_key_exists('expenseDetailDate',$_REQUEST)) {
  $expenseDetailDate=$_REQUEST['expenseDetailDate'];
}

$expenseDetailType=null;
if (array_key_exists('expenseDetailType',$_REQUEST)) {
  $expenseDetailType=$_REQUEST['expenseDetailType'];
}

$expenseDetailAmount=null;
if (array_key_exists('expenseDetailAmount',$_REQUEST)) {
  $expenseDetailAmount=$_REQUEST['expenseDetailAmount'];
}

$expenseDetailValue01=null;
$expenseDetailValue02=null;
$expenseDetailValue03=null;
$expenseDetailUnit01=null;
$expenseDetailUnit02=null;
$expenseDetailUnit03=null;
if (array_key_exists('expenseDetailValue01',$_REQUEST)) {
  $expenseDetailValue01=$_REQUEST['expenseDetailValue01'];
}
if (array_key_exists('expenseDetailValue02',$_REQUEST)) {
  $expenseDetailValue02=$_REQUEST['expenseDetailValue02'];
}
if (array_key_exists('expenseDetailValue03',$_REQUEST)) {
  $expenseDetailValue03=$_REQUEST['expenseDetailValue03'];
}
if (array_key_exists('expenseDetailUnit01',$_REQUEST)) {
  $expenseDetailUnit01=$_REQUEST['expenseDetailUnit01'];
}
if (array_key_exists('expenseDetailUnit02',$_REQUEST)) {
  $expenseDetailUnit02=$_REQUEST['expenseDetailUnit02'];
}
if (array_key_exists('expenseDetailUnit03',$_REQUEST)) {
  $expenseDetailUnit03=$_REQUEST['expenseDetailUnit03'];
}

Sql::beginTransaction();
// get the modifications (from request)
$expenseDetail=new ExpenseDetail($expenseDetailId);

$expenseDetail->idExpense=$idExpense; 
$expenseDetail->idExpenseDetailType=$expenseDetailType; 
$expenseDetail->name=$expenseDetailName;
$expenseDetail->externalReference=$expenseDetailReference;
//$expenseDetail->description;
$expenseDetail->expenseDate=$expenseDetailDate; 
$expenseDetail->amount=$expenseDetailAmount;
$expenseDetail->value01=$expenseDetailValue01;
$expenseDetail->value02=$expenseDetailValue02;
$expenseDetail->value03=$expenseDetailValue03;
$expenseDetail->unit01=$expenseDetailUnit01;
$expenseDetail->unit02=$expenseDetailUnit02;
$expenseDetail->unit03=$expenseDetailUnit03;

$expense=new Expense($idExpense);
$expenseDetail->idProject=$expense->idProject; 
$expenseDetail->idle=$expense->idle;

$result=$expenseDetail->save();

// Message of correct saving
displayLastOperationStatus($result);
?>
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
scriptLog('   ->/tool/saveProductProject.php');
// Get the info
if (! array_key_exists('productProjectId',$_REQUEST)) {
  throwError('productProjectId parameter not found in REQUEST');
}
$id=($_REQUEST['productProjectId']);

if (! array_key_exists('productProjectProject',$_REQUEST)) {
  throwError('productProjectProject parameter not found in REQUEST');
}
$project=($_REQUEST['productProjectProject']);

if (! array_key_exists('productProjectProduct',$_REQUEST)) {
  throwError('productProjectProduct parameter not found in REQUEST');
}
$product=($_REQUEST['productProjectProduct']);

if (! array_key_exists('productProjectStartDate',$_REQUEST)) {
  throwError('productProjectStartDate parameter not found in REQUEST');
}
$startDate=($_REQUEST['productProjectStartDate']);

if (! array_key_exists('productProjectEndDate',$_REQUEST)) {
  throwError('productProjectEndDate parameter not found in REQUEST');
}
$endDate=($_REQUEST['productProjectEndDate']);

$idle=0;
if (array_key_exists('productProjectIdle',$_REQUEST)) {
  $idle=1;
}
Sql::beginTransaction();
$productProject=new ProductProject($id);

$productProject->idProject=$project;
$productProject->idProduct=$product;
$productProject->idle=$idle;
$productProject->startDate=$startDate;
$productProject->endDate=$endDate;

$result=$productProject->save();

// Message of correct saving
displayLastOperationStatus($result);
?>
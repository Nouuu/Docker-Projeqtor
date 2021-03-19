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
scriptLog('   ->/tool/saveResourceCost.php');
$id=null;
if (array_key_exists('resourceCostId',$_REQUEST)) {
  $id=trim($_REQUEST['resourceCostId']);
}
if ($id=='') {
  $id=null;
}

// Get the assignment info
if (! array_key_exists('resourceCostIdResource',$_REQUEST)) {
  throwError('resourceCostIdResource parameter not found in REQUEST');
}
$idResource=$_REQUEST['resourceCostIdResource'];



$idRole=null;
if (array_key_exists('resourceCostIdRole',$_REQUEST)) {
  $idRole=$_REQUEST['resourceCostIdRole'];
}

if (! array_key_exists('resourceCostValue',$_REQUEST)) {
  throwError('resourceCostValue parameter not found in REQUEST');
}
$value=$_REQUEST['resourceCostValue'];

$startDate=null;
if (array_key_exists('resourceCostStartDate',$_REQUEST)) {
  $startDate=trim($_REQUEST['resourceCostStartDate']);
}
if ($startDate=='') {
  $startDate=null;
}

Sql::beginTransaction();
// get the modifications (from request)
$rc=new ResourceCost($id);

$rc->id=$id;
$rc->idResource=$idResource;
if ($idRole) {
  $rc->idRole=$idRole;
}
$rc->cost=$value;
if ($startDate) {
  $rc->startDate=$startDate;
}
$result=$rc->save();

$rcb = new ResourceCost($id);

// Message of correct saving
displayLastOperationStatus($result);
?>
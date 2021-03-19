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
scriptLog('   ->/tool/closeUncloseOrganizationBudgetElement.php');

if (! array_key_exists('refId',$_REQUEST)) {
  throwError('refId parameter not found in REQUEST');
}
$refId = $_REQUEST['refId'];

if (! array_key_exists('budgetElementId',$_REQUEST)) {
  throwError('budgetElementId parameter not found in REQUEST');
}
$id = $_REQUEST['budgetElementId'];    

if (! array_key_exists('year',$_REQUEST)) {
  throwError('year parameter not found in REQUEST');
}
$year = $_REQUEST['year'];    

if (! array_key_exists('idle',$_REQUEST)) {
  throwError('idle parameter not found in REQUEST');
}
$idle = $_REQUEST['idle'];    


$bE = new BudgetElement($id);
$bE->idle= ($idle=='1'?0:1);
$bE->idleDateTime = ($idle=='0'?date('Y-m-d H:i:s'):null);
$result= $bE->simpleSave();
// To calculate synthesis
if($bE->idle==0) {
    $orga = new Organization($refId,false);
    $orga->updateBudgetElementSynthesis($bE);
}

$_REQUEST['objectClass']='Organization';
$_REQUEST['objectId']=$refId;
$_REQUEST['OrganizationBudgetPeriod']=$year;
include '../view/objectDetail.php';

// END ADD BY Marc TABARY - 2017-02-13 - PERIODIC YEAR BUDGET ELEMENT
?>
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
// Get the link info
if (! array_key_exists('dependencyRefType',$_REQUEST)) {
  throwError('dependencyRefType parameter not found in REQUEST');
}
$dependencyRefType=$_REQUEST['dependencyRefType'];

if (! array_key_exists('dependencyRefId',$_REQUEST)) {
  throwError('dependencyRefId parameter not found in REQUEST');
}
$dependencyRefId=$_REQUEST['dependencyRefId'];

if (! array_key_exists('dependencyType',$_REQUEST)) {
  throwError('dependencyType parameter not found in REQUEST');
}
$dependencyType=$_REQUEST['dependencyType'];

if (! array_key_exists('dependencyRefTypeDep',$_REQUEST)) {
  throwError('dependencyRefTypeDep parameter not found in REQUEST');
}
//$dependencyRefTypeDep=SqlList::getNameFromId('Dependable', $_REQUEST['dependencyRefTypeDep']);
$dependencyRefTypeDepObj=New Dependable($_REQUEST['dependencyRefTypeDep']);
$dependencyRefTypeDep=$dependencyRefTypeDepObj->name;
if (! array_key_exists('dependencyRefIdDep',$_REQUEST)) {
  if (! array_key_exists('dependencyId',$_REQUEST)) {
    throwError('dependencyRefIdDep parameter not found in REQUEST');
  }
  //$dependencyRefIdDep=null; // Keep not defined to raise an error...
} else {
  $dependencyRefIdDep=$_REQUEST['dependencyRefIdDep'];
}

$dependencyDelay=0;
if (array_key_exists('dependencyDelay',$_REQUEST)) {
  $dependencyDelay=$_REQUEST['dependencyDelay'];
}
$dependencyId=null;
if (array_key_exists('dependencyId',$_REQUEST)) {
  $dependencyId=$_REQUEST['dependencyId'];
}
$typeOfDependency=RequestHandler::getValue('typeOfDependency');
// KEVIN TICKET #2038 
$dependencyComment=$_REQUEST['dependencyComment'];

Sql::beginTransaction();
if ($dependencyId) { // Edit Mode
	$dep=new Dependency($dependencyId);
	$dep->dependencyDelay=$dependencyDelay;
	$dep->comment=$dependencyComment;
	$dep->dependencyType=$typeOfDependency;
	$result=$dep->save();
} else { // Add Mode
  $arrayDependencyRefIdDep=array();
  if (is_array($dependencyRefIdDep)) {
    $arrayDependencyRefIdDep=$dependencyRefIdDep;
  } else {
    $arrayDependencyRefIdDep[]=$dependencyRefIdDep;
  }
	$result="";
	foreach ($arrayDependencyRefIdDep as $dependencyRefIdDep) {
		if ($dependencyType=="Successor") {
		  $critPredecessor=array("refType"=>$dependencyRefType,"refId"=>$dependencyRefId);
		  $critSuccessor=array("refType"=>$dependencyRefTypeDep,"refId"=>$dependencyRefIdDep);
		} else if ($dependencyType=="Predecessor") {  
		  $critSuccessor=array("refType"=>$dependencyRefType,"refId"=>$dependencyRefId);
		  $critPredecessor=array("refType"=>$dependencyRefTypeDep,"refId"=>$dependencyRefIdDep);  
		} else {
		  throwError('unknown dependency type : \'' . $dependencyType . '\'');
		}
	  $successor=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',$critSuccessor);
	  $predecessor=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',$critPredecessor);;
		$dep=new Dependency($dependencyId);
		$dep->successorId=$successor->id;
		$dep->successorRefType=$successor->refType;
		$dep->successorRefId=$successor->refId;
		$dep->predecessorId=$predecessor->id;
		$dep->predecessorRefType=$predecessor->refType;
		$dep->predecessorRefId=$predecessor->refId;
		$dep->comment=$dependencyComment;
		$dep->dependencyType=$typeOfDependency;
		//$dep->dependencyDelay=0;
		$dep->dependencyDelay=$dependencyDelay;
	  $res=$dep->save();
	  if (!$result) {
	    $result=$res;
	  } else if (stripos($res,'id="lastOperationStatus" value="OK"')>0 ) {
	    if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	      $deb=stripos($res,'#');
	      $fin=stripos($res,' ',$deb);
	      $resId=substr($res,$deb, $fin-$deb);
	      $deb=stripos($result,'#');
	      $fin=stripos($result,' ',$deb);
	      $result=substr($result, 0, $fin).','.$resId.substr($result,$fin);
	    } else {
	      $result=$res;
	    } 
	  }
	  $tmpStatus=getLastOperationStatus ($result);
	  if ($tmpStatus=='OK' and $successor->idPlanningMode!=23) {
	    if ($predecessor->plannedEndDate) {
	      if ($predecessor->refType=='Milestone') {
	        $successor->plannedStartDate=$predecessor->plannedEndDate;
	      } else {
	        $successor->plannedStartDate=addWorkDaysToDate($predecessor->plannedEndDate, 2);
	      } 
	      if (!$successor->fixPlanning) $successor->save();
	    }
	  }
	}
}
// Message of correct saving
displayLastOperationStatus($result);
?>
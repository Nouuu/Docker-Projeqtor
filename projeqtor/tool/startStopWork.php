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
 * Save the current object : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 * The old values are fetched in $currentObject of SESSION
 * Only changed values are saved. 
 * This way, 2 users updating the same object don't mess.
 */

require_once "../tool/projeqtor.php";

// Get the object class from request
if (! array_key_exists('objectClassName',$_REQUEST)) {
  throwError('className parameter not found in REQUEST');
}
$className=$_REQUEST['objectClassName'];

// Get the object from session(last status before change)
$obj=SqlElement::getCurrentObject(null,null,true,false);

if (! is_object($obj)) {
  throwError('last saved object is not a real object');
}
// compare expected class with object class
if ($className!=get_class($obj)) {
  throwError('last save object (' . get_class($obj) . ') is not of the expected class (' . $className . ').');
}

Sql::beginTransaction();
// get the modifications (from request)
$newObj=new $className();
$newObj->fillFromRequest();


$crit=array('periodValue'=>getWeekNumberFromDate(date('Y-m-d')),
    'idResource'=>getCurrentUserId());
$obj=SqlElement::getSingleSqlElementFromCriteria('WorkPeriod', $crit);
if ($obj->validated=='1' or $obj->submitted=='1') {
  $result=i18n('errorWeekValidated');
  $result.='<input type="hidden" id="lastSaveId" value="' . htmlEncode($newObj->id) .'" /><input type="hidden" id="lastOperation" value="update" /><input type="hidden" id="lastOperationStatus" value="INVALID" />';
  displayLastOperationStatus($result);
  exit;
}
$result=$newObj->save();

$action="";
if (! stripos($result,'id="lastOperationStatus" value="ERROR"')>0
   and ! stripos($result,'id="lastOperationStatus" value="INVALID"')>0
   and isset($newObj->WorkElement)) {
  $action=$_REQUEST['action'];
  if ($action=='start') {
    $resultStartStop=$newObj->WorkElement->start();
  } else {
    $resultStartStop=$newObj->WorkElement->stop();
  }
  $status = getLastOperationStatus ( $result );
  if  ($status !="ERROR" and $status!="INVALID") {
    $result='<input type="hidden" id="lastSaveId" value="' . htmlEncode($newObj->id) .'" /><input type="hidden" id="lastOperation" value="update" /><input type="hidden" id="lastOperationStatus" value="OK" />';
  } else {
    $result=$resultStartStop;
  }
}
// Message of correct saving
displayLastOperationStatus(formatResult($result,$action));

function formatResult($result, $action) {
  if ($action=='start') {
	  return i18n('workStarted') . $result;
  } else if ($action=='stop') {
    return i18n('workStopped')  . $result;
  } else {
    return $result;
  }
}	
?>
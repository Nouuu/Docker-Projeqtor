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
if (! array_key_exists('approverRefType',$_REQUEST)) {
  throwError('approverRefType parameter not found in REQUEST');
}
$refType=$_REQUEST['approverRefType'];
Security::checkValidClass($refType);

if (! array_key_exists('approverRefId',$_REQUEST)) {
  throwError('approverRefId parameter not found in REQUEST');
}
$refId=$_REQUEST['approverRefId'];
Security::checkValidId($refId);

if (! array_key_exists('approverId',$_REQUEST)) {
  throwError('approverId parameter not found in REQUEST');
}
$approverId=$_REQUEST['approverId'];
Security::checkValidId($approverId);

$linkId=null;

$arrayId=array();
if (is_array($approverId)) {
	$arrayId=$approverId;
} else {
	$arrayId[]=$approverId;
}
Sql::beginTransaction();
$result="";
// get the modifications (from request)
foreach ($arrayId as $approverId) {
	$approver=new Approver();
  $approver->refId=$refId;
  $approver->refType=$refType;
  $approver->idAffectable=$approverId;
  $res=$approver->save();
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
}

// Message of correct saving
displayLastOperationStatus($result);
?>
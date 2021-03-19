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

// Get the otherClient info
if (! array_key_exists('otherClientRefType',$_REQUEST)) {
  throwError('otherClientRefType parameter not found in REQUEST');
}
$refType=$_REQUEST['otherClientRefType'];
Security::checkValidClass($refType);

if (! array_key_exists('otherClientRefId',$_REQUEST)) {
  throwError('otherClientRefId parameter not found in REQUEST');
}
$refId=$_REQUEST['otherClientRefId'];
if (! array_key_exists('otherClientIdClient',$_REQUEST)) {
  throwError('otherClientIdClient parameter not found in REQUEST');
}
$clientId=$_REQUEST['otherClientIdClient'];
$comment="";
if (array_key_exists('otherClientComment',$_REQUEST)) {
    $comment=$_REQUEST['otherClientComment'];
}
$user=getSessionUser();
$arrayId=array();
if (is_array($clientId)) {
	$arrayId=$clientId;
} else {
	$arrayId[]=$clientId;
}
sort($arrayId,SORT_NUMERIC);
Sql::beginTransaction();
$result="";
// get the modifications (from request)
$obj=new $refType($refId);
$objClientFld='idClient';
$updatedMain=false;
foreach ($arrayId as $idClient) {
	$crit=array('refType'=>$refType, 'refId'=>$refId, 'idClient'=>$idClient);
	$otherClient=SqlElement::getSingleSqlElementFromCriteria('OtherClient', $crit);
	if (! $obj->$objClientFld) {
		$obj->$objClientFld=$idClient;
		$result=$obj->save();
		$updatedMain=true;
		if ($otherClient and $otherClient->id) {
			$otherClient->delete();
		}
	} else {
		if ((! $otherClient or ! $otherClient->id) and $idClient!=$obj->$objClientFld) {
			$otherClient=new OtherClient();
			$otherClient->refType=$refType;
			$otherClient->refId=$refId;
			$otherClient->idClient=$idClient;
		  $otherClient->comment=$comment;
		  $otherClient->idUser=$user->id;
		  $otherClient->creationDate=date("Y-m-d H:i:s"); 
		  $res=$otherClient->save();
		  if (!$result) {
		    $result=$res;
		  } else if (stripos($res,'id="lastOperationStatus" value="OK"')>0 and !$updatedMain) {
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
	}
}
if (!$result) {
	$result = i18n('messageNoChange');
	$result .= '<input type="hidden" id="lastSaveId" value="' . $refId . '" />';
	$result .= '<input type="hidden" id="lastOperation" value="update" />';
  $result .= '<input type="hidden" id="lastOperationStatus" value="NO_CHANGE" />';
}
// Message of correct saving
displayLastOperationStatus($result);
?>
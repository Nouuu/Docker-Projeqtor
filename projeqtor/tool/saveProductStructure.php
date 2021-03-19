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
if (! array_key_exists('productStructureObjectClass',$_REQUEST)) {
  throwError('productStructureObjectClass parameter not found in REQUEST');
}
$objectClass=$_REQUEST['productStructureObjectClass'];
Security::checkValidClass($objectClass);

if (! array_key_exists('productStructureObjectId',$_REQUEST)) {
  throwError('productStructureObjectId parameter not found in REQUEST');
}
$objectId=$_REQUEST['productStructureObjectId'];
Security::checkValidId($objectId);

if (! array_key_exists('productStructureListClass',$_REQUEST)) {
  throwError('productStructureListClass parameter not found in REQUEST');
}

$listClass=$_REQUEST['productStructureListClass'];
Security::checkValidClass($listClass);

if (! array_key_exists('productStructureWay',$_REQUEST)) {
  throwError('productStructureWay parameter not found in REQUEST');
}
$way=$_REQUEST['productStructureWay'];
Security::checkValidAlphanumeric($way);

if (! array_key_exists('productStructureListId',$_REQUEST)) {
  throwError('productStructureListId parameter not found in REQUEST');
}
$listId=$_REQUEST['productStructureListId'];

$comment="";
if (array_key_exists('productStructureComment',$_REQUEST)) {
    $comment=$_REQUEST['productStructureComment'];
}

$strId=null;
if (array_key_exists('productStructureId',$_REQUEST)) {
	$strId=$_REQUEST['productStructureId'];
}

$arrayId=array();
if (is_array($listId)) {
	$arrayId=$listId;
} else {
	$arrayId[]=$listId;
}
Sql::beginTransaction();
$result="";
// get the modifications (from request)
foreach ($arrayId as $id) {
	$str=new ProductStructure($strId);
	if ($way=='composition') {
	  $str->idProduct=$objectId;
	  $str->idComponent=$id;
	} else if ($way=='structure') {
	  $str->idProduct=$id;
	  $str->idComponent=$objectId;
	} else {
	  throwError("way '$way' is not an expected value");
	}
  $str->comment=$comment;
  $str->idUser=$user->id;
  $str->creationDate=date("Y-m-d");
  $res=$str->save();
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
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
if (! array_key_exists('productVersionStructureObjectClass',$_REQUEST)) {
  throwError('productVersionStructureObjectClass parameter not found in REQUEST');
}
$objectClass=$_REQUEST['productVersionStructureObjectClass'];
Security::checkValidClass($objectClass);

if (! array_key_exists('productVersionStructureObjectId',$_REQUEST)) {
  throwError('productVersionStructureObjectId parameter not found in REQUEST');
}
$objectId=$_REQUEST['productVersionStructureObjectId'];
Security::checkValidId($objectId);

if (! array_key_exists('productVersionStructureListClass',$_REQUEST)) {
  throwError('productVersionStructureListClass parameter not found in REQUEST');
}
$listClass=$_REQUEST['productVersionStructureListClass'];
Security::checkValidClass($listClass);

if (! array_key_exists('productVersionStructureWay',$_REQUEST)) {
  throwError('productVersionStructureWay parameter not found in REQUEST');
}
$way=$_REQUEST['productVersionStructureWay'];
Security::checkValidAlphanumeric($way);

if (! array_key_exists('productVersionStructureListId',$_REQUEST)) {
  throwError('productVersionStructureListId parameter not found in REQUEST');
}
$listId=$_REQUEST['productVersionStructureListId'];

$comment="";
if (array_key_exists('productVersionStructureComment',$_REQUEST)) {
    $comment=$_REQUEST['productVersionStructureComment'];
}
$strId=null;
if (array_key_exists('productVersionStructureId',$_REQUEST)) {
	$strId=$_REQUEST['productVersionStructureId'];
}

$arrayId=array();
if (is_array($listId)) {
	$arrayId=$listId;
} else {
	$arrayId[]=$listId;
}
Sql::beginTransaction();
global $doNotUpdateAllVersionProject;
$doNotUpdateAllVersionProject=true;
$result="";
// get the modifications (from request)
foreach ($arrayId as $id) {
	$str=new ProductVersionStructure($strId);
	if ($way=='composition') {
	  $str->idProductVersion=$objectId;
	  $str->idComponentVersion=$id;
	} else if ($way=='structure') {
	  $str->idProductVersion=$id;
	  $str->idComponentVersion=$objectId;
	} else {
	  throwError("way '$way' is not an expected value");
	}
	$prod=new ProductOrComponent($str->idProductVersion);
	$doNotUpdateAllVersionProject=($prod->scope=='Product')?false:true;// If link is between component versions, do not update all version
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
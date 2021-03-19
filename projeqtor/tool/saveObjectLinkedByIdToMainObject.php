<?php
// ADD BY Marc TABARY - 2017-02-23 - ADD OBJECTS LINKED BY ID TO MAIN OBJET

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

if (! array_key_exists('linkedObjectId',$_REQUEST)) {
  throwError('linkedObjectId parameter not found in REQUEST');
}

if (! array_key_exists('mainObjectClass',$_REQUEST)) {
  throwError('mainObjectClass parameter not found in REQUEST');
}

if (! array_key_exists('idInstanceOfMainClass',$_REQUEST)) {
  throwError('idInstanceOfMainClass parameter not found in REQUEST');
}

if (! array_key_exists('linkObjectClassName',$_REQUEST)) {
  throwError('linkObjectClassName parameter not found in REQUEST');
}

$linkedObjectId=$_REQUEST['linkedObjectId'];
// CHANGE BY Marc TABARY - 2017-03-31 - ADD MULTIPLE OBJECTS LINKED BY ID
if (is_array($linkedObjectId)) $listLinkedObjectId = $linkedObjectId;
else $listLinkedObjectId = explode(',',$linkedObjectId);
foreach($listLinkedObjectId as $theId) {
    Security::checkValidId($theId);
}
// END CHANGE BY Marc TABARY - 2017-03-31 - ADD MULTIPLE OBJECTS LINKED BY ID

$idMainObjectClass = 'id'.$_REQUEST['mainObjectClass'];
$idInstanceOfMainClass=$_REQUEST['idInstanceOfMainClass'];
$linkObjectClassName=$_REQUEST['linkObjectClassName'];

Sql::beginTransaction();
$result="";
// get the modifications (from request)
// CHANGE BY Marc TABARY - 2017-03-31 - ADD MULTIPLE OBJECTS LINKED BY ID
foreach ($listLinkedObjectId as $linkedObjId) {
// END CHANGE BY Marc TABARY - 2017-03-31 - ADD MULTIPLE OBJECTS LINKED BY ID
  $obj=new $linkObjectClassName($linkedObjId);
  if ($linkObjectClassName=='Resource' and !$obj->id) $obj=new ResourceTeam($linkedObjId);
  $obj->$idMainObjectClass=$idInstanceOfMainClass;
  $res=$obj->save();
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
// END ADD BY Marc TABARY - 2017-02-23 - ADD OBJECTS LINKED BY ID TO MAIN OBJET

?>
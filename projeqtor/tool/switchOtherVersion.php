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
 * Delete the current object : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

$id=null;
if (array_key_exists('otherVersionId',$_REQUEST)) {
  $id=$_REQUEST['otherVersionId'];
}
$id=trim($id);
if ($id=='') {
  $id=null;
} 
if ($id==null) {
  throwError('linkId parameter not found in REQUEST');
}
Sql::beginTransaction();
$vers=new OtherVersion($id);
$refType=$vers->refType;
$refId=$vers->refId;
$scope=$vers->scope;
$fld='id'.$vers->scope;
$fldArray='_Other'.$vers->scope;
$obj=new $refType($refId);
$mainVers=$obj->$fld;
$otherVers=$vers->idVersion;
// save new main
$obj->$fld=$otherVers;
$result=$obj->save();
// save new other
if ($mainVers) {
  $vers=new OtherVersion();
  $vers->refType=$refType;
  $vers->refId=$refId;
  $vers->scope=$scope;
  $vers->creationDate=date('Y-m-d H:i:s');
  $user=getSessionUser();
  $vers->idUser=$user->id;
  $vers->idVersion=$mainVers;
  $res=$vers->save();
}
// Message of correct saving
displayLastOperationStatus($result);
?>
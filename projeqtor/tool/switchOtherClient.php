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
if (array_key_exists('otherClientId',$_REQUEST)) {
  $id=$_REQUEST['otherClientId'];
}
$id=trim($id);
if ($id=='') {
  $id=null;
} 
if ($id==null) {
  throwError('linkId parameter not found in REQUEST');
}
Sql::beginTransaction();
$cli=new OtherClient($id);
$refType=$cli->refType;
$refId=$cli->refId;
$fld='idClient';
$fldArray='_OtherClient';
$obj=new $refType($refId);
$mainClient=$obj->$fld;
$otherClient=$cli->idClient;
// save new main
$obj->$fld=$otherClient;
$result=$obj->save();
$cli->delete();
// save new other
if ($mainClient) {
  $cli=new OtherClient();
  $cli->refType=$refType;
  $cli->refId=$refId;
  $cli->creationDate=date('Y-m-d H:i:s');
  $user=getSessionUser();
  $cli->idUser=$user->id;
  $cli->idClient=$mainClient;
  $res=$cli->save();
}
// Message of correct saving
displayLastOperationStatus($result);
?>
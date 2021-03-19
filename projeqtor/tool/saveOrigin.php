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
if (! array_key_exists('originOriginType',$_REQUEST)) {
  throwError('originOriginType parameter not found in REQUEST');
}
$originOriginTypeObj=New Originable($_REQUEST['originOriginType']);
$originOriginType=$originOriginTypeObj->name;

if (! array_key_exists('originOriginId',$_REQUEST)) {
  throwError('originOriginId parameter not found in REQUEST');
}
$originOriginId=$_REQUEST['originOriginId'];

if (! array_key_exists('originRefType',$_REQUEST)) {
  throwError('originRefType parameter not found in REQUEST');
}
$originRefType=$_REQUEST['originRefType'];
if (! array_key_exists('originRefId',$_REQUEST)) {
  throwError('originRefId parameter not found in REQUEST');
}
$originRefId=$_REQUEST['originRefId'];

$originId=null;

Sql::beginTransaction();
// get the modifications (from request)
$critArray=array('refType'=>$originRefType,'refId'=>$originRefId);
$origin=SqlElement::getSingleSqlElementFromCriteria('Origin', $critArray);

$origin->originId=$originOriginId;
$origin->originType=$originOriginType;
$origin->refId=$originRefId;
$origin->refType=$originRefType;

$result=$origin->save();

// Message of correct saving
displayLastOperationStatus($result);
?>
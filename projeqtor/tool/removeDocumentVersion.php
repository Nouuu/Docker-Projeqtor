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
 * Delete the current documentVersion : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

$documentVersionId=null;
if (array_key_exists('documentVersionId',$_REQUEST)) {
  $documentVersionId=$_REQUEST['documentVersionId']; // validated to be numeric value in SqlElement base constructor.
}
$documentVersionId=trim($documentVersionId);
if ($documentVersionId=='') {
  $documentVersionId=null;
} 
if ($documentVersionId==null) {
  throwError('documentVersionId parameter not found in REQUEST');
}
Sql::beginTransaction();
$obj=new DocumentVersion($documentVersionId);
$file=$obj->getUploadFileName();
if (file_exists($file)) {
  unlink($file);
}
$result=$obj->delete();

// Message of correct saving
displayLastOperationStatus($result);
?>
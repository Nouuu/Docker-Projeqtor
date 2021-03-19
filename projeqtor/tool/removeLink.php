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

$linkId=null;
if (array_key_exists('linkId',$_REQUEST)) {
  $linkId=$_REQUEST['linkId']; // validated to be numeric value in SqlElement base constructor.
}
$linkId=trim($linkId);
if ($linkId=='') {
  $linkId=null;
} 
if ($linkId==null) {
  throwError('linkId parameter not found in REQUEST');
}
Sql::beginTransaction();
$obj=new Link($linkId);
$result=$obj->delete();
$mailResult1=null;
$mailResult2=null;
if (getLastOperationStatus($result)=='OK') {
  $elt1=new $obj->ref1Type($obj->ref1Id);
  $mailResult1=$elt1->sendMailIfMailable(false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,true);
  $elt2=new $obj->ref2Type($obj->ref2Id);
  $mailResult2=$elt2->sendMailIfMailable(false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,true);
}
if ($mailResult1 or $mailResult2) {
  $pos=strpos($result,'<input type="hidden"');
  if ($pos) {
    $result=substr($result, 0,$pos).' - ' . Mail::getResultMessage( ($mailResult1=='TEMP')?$mailResult1:$mailResult2 ).substr($result, $pos);
  }
}
// Message of correct saving
displayLastOperationStatus($result);
?>
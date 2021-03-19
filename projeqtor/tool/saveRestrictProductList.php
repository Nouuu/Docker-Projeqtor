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

/**
 * ===========================================================================
 * Save a checklistdefinition line : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";

if (!array_key_exists('idProfile', $_REQUEST)) {
  throwError('idProfile parameter not found in REQUEST');
}
$idProfile=trim($_REQUEST['idProfile']);
$valueCopy = RequestHandler::getValue("dialogRestrictListCheckProfileId_".$idProfile);

Sql::beginTransaction();
$result="";
//those strings in the following array are the names of the 4 columns in the table restrictlist
$arrStatus = array("showAll", "showStarted", "showDelivered", "showInService");
$element=SqlElement::getSingleSqlElementFromCriteria("restrictlist", array('idProfile'=>$idProfile));
if (!$element or !$element->id) {
  $element->idProfile=$idProfile;
}
foreach($arrStatus as $status) {
  if($valueCopy == $status){
    $element->$status=1;
  } else {
    $element->$status=0;
  }
}
$resultLine=$element->save();
if (!$result or stripos($resultLine, 'id="lastOperationStatus" value="ERROR"')>0) {
  $result=$resultLine;
}
if (!stripos($result, 'id="lastOperationStatus" value="ERROR"')>0) {
  $result=i18n('Profile').' #'.$idProfile.' '.i18n('resultUpdated');
  $result.='<input type="hidden" id="lastSaveId" value="'.htmlEncode($idProfile).'" />';
  $result.='<input type="hidden" id="lastOperation" value="update" />';
  $result.='<input type="hidden" id="lastOperationStatus" value="OK" />';
}
// Message of correct saving
displayLastOperationStatus($result);
?>
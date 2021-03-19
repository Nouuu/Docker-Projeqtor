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
 * Save a checklistdefinition line : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";
if (! array_key_exists('checklistDefinitionId',$_REQUEST)) {
	throwError('checklistDefinitionId parameter not found in REQUEST');
}
$checklistDefinitionId=trim($_REQUEST['checklistDefinitionId']); // validated to be numeric value in SqlElement base constructor
if (! array_key_exists('checklistId',$_REQUEST)) {
	throwError('checklistId parameter not found in REQUEST');
}
$checklistId=trim($_REQUEST['checklistId']); // validated to be numeric value in SqlElement base constructor
if (! array_key_exists('checklistObjectClass',$_REQUEST)) {
	throwError('checklistObjectClass parameter not found in REQUEST');
}
$checklistObjectClass=$_REQUEST['checklistObjectClass'];
Security::checkValidClass($checklistObjectClass);

if (! array_key_exists('checklistObjectId',$_REQUEST)) {
	throwError('checklistObjectId parameter not found in REQUEST');
}
$checklistObjectId=trim($_REQUEST['checklistObjectId']);
Security::checkValidId($checklistObjectId);

if (! array_key_exists('checklistComment',$_REQUEST)) {
	throwError('checklistCommentd parameter not found in REQUEST');
}
$comment=trim($_REQUEST["checklistComment"]);


$checklistDefinition=new ChecklistDefinition($checklistDefinitionId);
$checklist=new Checklist($checklistId);
$cl=new ChecklistLine();
$linesTmp=$cl->getSqlElementsFromCriteria(array('idChecklist'=>$checklist->id));
$linesVal=array();
foreach ($linesTmp as $line) {
	$linesVal[$line->idChecklistDefinitionLine]=$line;
}
Sql::beginTransaction();
$checklist->refType=$checklistObjectClass;
$checklist->refId=$checklistObjectId;
$checklist->idChecklistDefinition=$checklistDefinitionId;
$changed=false;
if ($checklist->comment!=trim($comment)) {
  $changed=true;
}
$checklist->comment=$comment;
$result=$checklist->save();
if ($changed) {
  $status=getLastOperationStatus($result);
} else {
  $status='NO_CHANGE';
}

if ( ! stripos($result,'id="lastOperationStatus" value="ERROR"')>0) {
  foreach($checklistDefinition->_ChecklistDefinitionLine as $line) {
		if (isset($linesVal[$line->id])) {
			$valLine=$linesVal[$line->id];
		} else {
			$valLine=new ChecklistLine();
		}
		$valLine->idChecklist=$checklist->id;
		$valLine->idChecklistDefinitionLine=$line->id;
		//$valLine->checkTime=date('Y-m-d H:i:s');		
		$checkedCpt=0;
		$checkedCptChanged=0;
		for ($i=1; $i<=5; $i++) {
			$checkName="check_".$line->id."_".$i;
			$valueName="value0".$i;
			if (isset($_REQUEST[$checkName])) {
				$checkedCpt++;
				if (! $valLine->$valueName) {
				  $checkedCptChanged++;
					$valLine->idUser=getSessionUser()->id;
				  $valLine->checkTime=date('Y-m-d H:i:s');
				}
				$valLine->$valueName=1;
			} else {
				if ($valLine->$valueName) {
				  $checkedCptChanged++;
				}
				$valLine->$valueName=0;
			}
		}
		$cmtName='checklistLineComment_'.$line->id;
		if (isset($_REQUEST[$cmtName])) {
			$cmt=trim($_REQUEST[$cmtName]);
			if ($valLine->comment!=$cmt) {
			  $checkedCptChanged++;
			}
			$valLine->comment=$cmt;
			if ($cmt) $checkedCpt+=1;
		}	
	  $resultLine="";
		if ($checkedCpt==0) {
			if ($valLine->id) {
				$resultLine=$valLine->delete();
			}
		} else if ($checkedCptChanged) {
			$resultLine=$valLine->save();
		}
		if ($resultLine) {
  		$statusLine=getLastOperationStatus ( $resultLine );
  		if ($statusLine=="NO_CHANGE") {
  		  // Nothing
  		} else if ($statusLine=="ERROR") {
  		 	$result=$resultLine;
  		 	$status=$statusLine;
  	  } else if ($status=='NO_CHANGE') { // Explicitly, $statusLine=="OK"
  	    $result=$resultLine;
  	    $status=$statusLine;
  	  }
		}
  }
}
if ($status=="OK") {
  $result=i18n('Checklist') . ' ' . i18n('resultUpdated').' ('.i18n($checklistObjectClass).' #'.$checklistObjectId.')';
  $result .= '<input type="hidden" id="lastSaveId" value="' . $checklistObjectId . '" />';
  $result .= '<input type="hidden" id="lastOperation" value="update" />';
  $result .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
  $result .= '<input type="hidden" id="checklistUpdated" value="true" />';
}
// Message of correct saving
if (! isset($included) ) {
  displayLastOperationStatus($result);
} else {
  if ($status == "OK" or $status=="NO_CHANGE" or $status=="INCOMPLETE") {
    Sql::commitTransaction ();
  } else {
    Sql::rollbackTransaction ();
  }
}
?>
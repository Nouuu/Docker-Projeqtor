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
if (! array_key_exists('workflowId',$_REQUEST)) {
	throwError('workflowId parameter not found in REQUEST');
}
$workflowId=trim($_REQUEST['workflowId']);

$statusList=SqlList::getList('Status');
Sql::beginTransaction();
$result="";
foreach($statusList as $idStatus=>$status) {
	$critArray=array('scope'=>'workflow', 'objectClass'=>'workflow#'.$workflowId, 'idUser'=>$idStatus);
	$cs=SqlElement::getSingleSqlElementFromCriteria("ColumnSelector", $critArray);
	if ($cs and $cs->id) {
		// OK
	} else {
		$cs->scope='workflow';
		$cs->objectClass='workflow#'.$workflowId;
		$cs->idUser=$idStatus;
		$cs->field=$status;
		$cs->attribute=$status;
		$cs->name='workflow#'.$workflowId.' status#'.$idStatus;
	}
	if (array_key_exists('dialogWorkflowParameterCheckStatusId_'.$idStatus,$_REQUEST) ) {
		$cs->hidden=0;
	} else {
		$cs->hidden=1;
	}
	$resultLine=$cs->save();
	if (! $result or stripos($resultLine,'id="lastOperationStatus" value="ERROR"')>0) {
	 	$result=$resultLine;
	}
}

if (! stripos($result,'id="lastOperationStatus" value="ERROR"')>0) {
  $result=i18n('Workflow') . ' #'. $workflowId . ' ' . i18n('resultUpdated');
  $result .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode($workflowId) . '" />';
  $result .= '<input type="hidden" id="lastOperation" value="update" />';
  $result .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
}
// Message of correct saving
displayLastOperationStatus($result);
?>
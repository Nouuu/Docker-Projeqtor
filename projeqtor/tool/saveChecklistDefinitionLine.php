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
// Get the bill line info
$lineId=null;
if (array_key_exists('checklistDefinitionLineId',$_REQUEST)) {
  $lineId=$_REQUEST['checklistDefinitionLineId']; // validated to be numeric value in SqlElement base constructor
}
$checklistDefinitionId=null;
if (array_key_exists('checklistDefinitionId',$_REQUEST)) {
	$checklistDefinitionId=$_REQUEST['checklistDefinitionId']; // validated to be numeric value in SqlElement base constructor
}
$lineName=null;
if (array_key_exists('dialogChecklistDefinitionLineName',$_REQUEST)) {
	$lineName=$_REQUEST['dialogChecklistDefinitionLineName'];
}
$lineTitle=null;
if (array_key_exists('dialogChecklistDefinitionLineTitle',$_REQUEST)) {
	$lineTitle=$_REQUEST['dialogChecklistDefinitionLineTitle'];
}
$sortOrder=0;
if (array_key_exists('dialogChecklistDefinitionLineSortOrder',$_REQUEST)) {
	$sortOrder=$_REQUEST['dialogChecklistDefinitionLineSortOrder'];
	Security::checkValidNumeric($sortOrder);
}
$required=(RequestHandler::isCodeSet('dialogChecklistDefinitionLineRequired') && RequestHandler::getBoolean('dialogChecklistDefinitionLineRequired')==1)?1:0;
$checkNames=array();
$checkTitles=array();
for ($i=1;$i<=5;$i++) {
	if (array_key_exists('dialogChecklistDefinitionLineChoice_'.$i,$_REQUEST)) {
		$checkName=$_REQUEST['dialogChecklistDefinitionLineChoice_'.$i];
		if (trim($checkName)) {
			$checkNames[]=$checkName;
			if (array_key_exists('dialogChecklistDefinitionLineTitle_'.$i,$_REQUEST)) {		
				$checkTitles[]=$_REQUEST['dialogChecklistDefinitionLineTitle_'.$i];;
			} else {
				$checkTitles[]=null;
			}
		}
	}
}
$exclusive=0;
if (array_key_exists('dialogChecklistDefinitionLineExclusive',$_REQUEST)) {
	$exclusive=1;
}

Sql::beginTransaction();
$line=new ChecklistDefinitionLine($lineId);
$line->idChecklistDefinition=$checklistDefinitionId;
$line->name=$lineName;
$line->title=$lineTitle;
$line->sortOrder=$sortOrder;
for ($i=1;$i<=5;$i++) {
	$check='check0'.$i;
	$title='title0'.$i;
  if (isset($checkNames[$i-1])) {
  	$line->$check=$checkNames[$i-1];
  	if (isset($checkTitles[$i-1])) {
  		$line->$title=$checkTitles[$i-1];
  	} else {
  		$line->$title=null;
  	}
  } else {
  	$line->$check=null;
  	$line->$title=null;
  }
}
$line->exclusive=$exclusive;
$line->required=$required;
$result=$line->save();

// Message of correct saving
displayLastOperationStatus($result);
?>
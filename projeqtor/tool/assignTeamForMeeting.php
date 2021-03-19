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
 * Add assignements in current object : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";
$nbAff = 0;
// need security
if (! array_key_exists('assignmentRefId',$_REQUEST)) {
  throwError('assignmentRefId parameter not found in REQUEST');
 }
$assignmentRefId=$_REQUEST['assignmentRefId'];
Security::checkValidId($assignmentRefId);// validated to be id value in SqlElement base constructor.

// need control
if (! array_key_exists('assignmentRefType',$_REQUEST)) {
  throwError('assignmentRefType parameter not found in REQUEST');
}
$assignmentRefType = $_REQUEST['assignmentRefType'];
Security::checkValidClass($assignmentRefType);

$meet = new $assignmentRefType($assignmentRefId);
//$crit = array('idProject'=> $meet->idProject,'idle'=>'0');
$aff = new Affectation();
//Flo #4020
if($assignmentRefType == 'PeriodicMeeting'){
  $meetDate=$meet->periodicityEndDate;
}else{
  $meetDate=$meet->meetingDate;
}
if (!$meetDate) $meetDate=date('Y-m-d');
$critWhere="idle=0 AND idProject= $meet->idProject AND ( endDate >= '$meetDate' OR endDate IS NULL ) ";
$list=$aff->getSqlElementsFromCriteria(null,false,$critWhere);
//end flo
$canUpdate=securityGetAccessRightYesNo('menuMeeting', 'update', $meet) == "YES";
if (!$canUpdate) {
  $list=array(); // Empty list for reader only (no update right)
}

$hoursPerDay=Parameter::getGlobalParameter('dayTime');
if ($meet->meetingEndTime and $meet->meetingStartTime) {
  $hourMeeting = (strtotime($meet->meetingEndTime)-strtotime($meet->meetingStartTime))/3600;
} else {
  $hourMeeting = 0;
}
// Message error
$result=i18n('Assignment') . ' ' . i18n('resultInserted') . ' : 0';
$result .= '<input type="hidden" id="lastSaveId" value="" />';
$result .= '<input type="hidden" id="lastOperation" value="insert" />';
$result .= '<input type="hidden" id="lastOperationStatus" value="NO_CHANGE" />';
Sql::beginTransaction();
foreach ($list as $affRes) {
    //Flo #4020
    $res= new Resource($affRes->idResource,true);
    if($res->idle){
        continue;
    }
    //end Flo
    $crt=array('idResource'=>$affRes->idResource, 'refType'=>$assignmentRefType, 'refId'=>$assignmentRefId);
    $ass = new Assignment();
    $assCpt=$ass->countSqlElementsFromCriteria($crt);
    if ($assCpt>0) continue;
    $ass->idResource= $affRes->idResource;
    $ass->refId = $assignmentRefId;
    $ass->refType = $assignmentRefType;
    $ass->idProject = $affRes->idProject;
    if($hourMeeting){
      $ass->assignedWork = $hourMeeting/$hoursPerDay;
    }
    //$ass->idRole=(isset($costArray[$affRes->idRole]))?$affRes->idRole:$defaultRole;
    $ass->realWork = 0;
    $ass->leftWork = $ass->assignedWork;
    $ass->plannedWork =  $ass->leftWork  + $ass->realWork  ;
    $ass->notPlannedWork =0;
    $ass->rate=100;
    $ass->dailyCost=(isset($costArray[$ass->idRole]))?$costArray[$ass->idRole]:0;//$defaultCost;
    $ass->assignedCost = ( $ass->dailyCost * $ass->assignedWork );
    $ass->realCost=0;
    $ass->leftCost = $ass->assignedCost;
    $ass->plannedCost =($ass->realCost + $ass->leftCost) ;
    $ass->idle= 0;
    $result=$ass->save();
    if (getLastOperationStatus($result)!='OK') {
      break;
    }else{
        $nbAff++;
     }
}
// Message insert
if ($nbAff) {
  $result='<b>' . i18n('sectionAttendees') . ' ' . i18n('resultInserted') . ' : ' . $nbAff . '</b>';
  $result .= '<input type="hidden" id="lastSaveId" value="" />';
  $result .= '<input type="hidden" id="lastOperation" value="insert" />';
  $result .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
}

// Message of correct saving
displayLastOperationStatus($result);
?>

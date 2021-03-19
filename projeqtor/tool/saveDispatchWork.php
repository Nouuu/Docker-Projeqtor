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
// Get the note info
if (! array_key_exists('dispatchWorkObjectClass',$_REQUEST)) {
  throwError('dispatchWorkObjectClass parameter not found in REQUEST');
}
$refType=$_REQUEST['dispatchWorkObjectClass'];
Security::checkValidClass($refType);

if (! array_key_exists('dispatchWorkObjectId',$_REQUEST)) {
  throwError('dispatchWorkObjectId parameter not found in REQUEST');
}
$refId=$_REQUEST['dispatchWorkObjectId'];

if (! array_key_exists("dispatchWorkTotal",$_REQUEST) ) {
  throwError('dispatchWorkTotal parameter not found in REQUEST');
}
$total=Work::convertImputation($_REQUEST['dispatchWorkTotal']);

if (! array_key_exists('dispatchWorkDate',$_REQUEST)) {
  throwError('dispatchWorkDate parameter not found in REQUEST');
}
$dateList=$_REQUEST['dispatchWorkDate'];

if (! array_key_exists('dispatchWorkResource',$_REQUEST)) {
  throwError('dispatchWorkResource parameter not found in REQUEST');
}
$resourceList=$_REQUEST['dispatchWorkResource'];

if (! array_key_exists('dispatchWorkValue',$_REQUEST)) {
  throwError('dispatchWorkValue parameter not found in REQUEST');
}
$valueList=$_REQUEST['dispatchWorkValue'];

if (! array_key_exists('dispatchWorkId',$_REQUEST)) {
  throwError('dispatchWorkId parameter not found in REQUEST');
}
$workIdList=$_REQUEST['dispatchWorkId'];

if (! array_key_exists('dispatchWorkElementId',$_REQUEST)) {
  throwError('dispatchWorkElementId parameter not found in REQUEST');
}
$weId=$_REQUEST['dispatchWorkElementId'];
$we=new WorkElement($weId);
$obj=new $refType($refId);

Sql::beginTransaction();
$saveDispatchMode=true;
$error=false;
$result=i18n("messageNoChange").' '.i18n("colRealWork").'<input type="hidden" id="lastSaveId" value="" /><input type="hidden" id="lastOperation" value="update" /><input type="hidden" id="lastOperationStatus" value="NO_CHANGE" />';
if ($we->realWork!=$total) {
  $we->realWork=$total;
  $resultWe=$we->save(true);
  $status = getLastOperationStatus ( $resultWe );
  if ($status=='OK') {
    $result=cleanResult($resultWe);
  } else if ($status=='ERROR') {
    $result=$resultWe;
    $error=true;
  }
  $result=cleanResult($resultWe);
}
$arrayResourceDate=array();
foreach ($dateList as $idx=>$date) {
  if ($error) break;
  if ( (trim($date) and isset($resourceList[$idx]) and trim($resourceList[$idx])) or (isset($workIdList[$idx]) and $workIdList[$idx]) ) {
    $id=(isset($workIdList[$idx]))?$workIdList[$idx]:null;
    $work=new Work($id);
    $oldWork=new Work($id);;
    if (trim($date)) $work->setDates($date);
    if (isset($resourceList[$idx]) and trim($resourceList[$idx])) $work->idResource=$resourceList[$idx];
    $work->idProject=$obj->idProject;
    if (! $work->refType) {
      if (property_exists($refType, 'idActivity') and $obj->idActivity) {
        $work->refType='Activity';
        $work->refId=$obj->idActivity;
      } else {
        $work->refType=$refType;
        $work->refId=$refId;
      }
    }
    $newWork=Work::convertImputation($valueList[$idx]);
    $diff=$newWork-$work->work;
    $work->work=$newWork;
    $work->idWorkElement=$weId;
    $work->dailyCost=null; // set to null to force refresh 
    $work->cost=null;
//     $resWork=$work->save();
//     $status = getLastOperationStatus ( $resWork );
//     if ($status=='ERROR' or $status=='INVALID') {
//       $result=$resWork;
//       $error=true;
//       break;
//     }
    if ($work->idResource != $oldWork->idResource) {
      $oldAss=WorkElement::updateAssignment($oldWork, $oldWork->work*(-1));
      $diff=$newWork;
    }
    $ass=WorkElement::updateAssignment($work, $diff);
    $work->idAssignment=($ass)?$ass->id:null;
    $resWork="";
    if($oldWork->work!=$work->work or $work->idResource!=$oldWork->idResource or $oldWork->day!=$work->day 
    or $oldWork->refType!=$work->refType or $oldWork->refId!=$work->refId) {
      if ($work->work==0) {
        if ($work->id) {
          $resWork=$work->delete();
        }
      } else {
        $resWork=$work->save();
        $arrayResourceDate[$work->idResource.'#'.$work->workDate]=$work->id;
      }
    }
    
    if ($resWork) {
      $status = getLastOperationStatus ( $resWork );
      if ($status=='OK') {
        $result=cleanResult($resWork);
      } else if ($status=='ERROR' or $status=='INVALID') {
        $result=$resWork;
        $error=true;
        break;
      }
    }
    if ($ass) $ass->saveWithRefresh(); // required to update cost
  }
}
ProjectPlanningElement::updateSynthesis('Project',$we->idProject);
// Message of correct saving
displayLastOperationStatus($result);

function cleanResult($result) {
  return i18n('messageImputationSaved').substr($result,strpos($result,'<input'));
}
?>
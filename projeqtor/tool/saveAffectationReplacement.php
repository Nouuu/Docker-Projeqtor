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
scriptLog('   ->/tool/saveAffectationReplacement.php');
// Get the info
if (! array_key_exists('replaceAffectationIdAffectation',$_REQUEST)) {
  throwError('replaceAffectationIdAffectation parameter not found in REQUEST');
}
$id=($_REQUEST['replaceAffectationIdAffectation']); // validated to be numeric value in SqlElement base constructor.
$aff=new Affectation($id);

if (! array_key_exists('replaceAffectationResource',$_REQUEST) ) {
  throwError('replaceAffectationResource parameter not found in REQUEST');
}
$resource=($_REQUEST['replaceAffectationResource']); // escaped before used in DB queries

if (! array_key_exists('replaceAffectationProfile',$_REQUEST) ) {
  throwError('replaceAffectationProfile parameter not found in REQUEST');
}
$profile=($_REQUEST['replaceAffectationProfile']); // escaped before used in DB queries

if (! array_key_exists('replaceAffectationRate',$_REQUEST)) {
  throwError('replaceAffectationRate parameter not found in REQUEST');
}
$rate=($_REQUEST['replaceAffectationRate']);
Security::checkValidNumeric($rate);

$startDate="";
if (array_key_exists('replaceAffectationStartDate',$_REQUEST)) {
	$startDate=trim($_REQUEST['replaceAffectationStartDate']);;
}
Security::checkValidDateTime($startDate);

$endDate="";
if (array_key_exists('replaceAffectationEndDate',$_REQUEST)) {
	$endDate=trim($_REQUEST['replaceAffectationEndDate']);;
}
Security::checkValidDateTime($endDate);

$idle=0;

$resObj=new ResourceAll($resource);
$rc=new ResourceCost();
$crit=array('idResource'=>$resource,'endDate'=>null);
$rcList=$rc->getSqlElementsFromCriteria($crit,null,null,'id asc');
$costArray=array();
$defaultRole=$resObj->idRole;
$defaultCost=0;
if (count($rcList)>0) {
  foreach ($rcList as $rc) {
    $costArray[$rc->idRole]=$rc->cost;
    if ($rc->idRole==$defaultRole) {
      $defaultCost=$rc->cost;
    }
    $last=$rc->idRole;
  }
  if (!$defaultCost) {
    $defaultRole=$last;
    $defaultCost=$costArray[$defaultRole];
  }
}

Sql::beginTransaction();

// Save new affectation
$newAff=new Affectation();
$newAff->idProject=$aff->idProject;
$newAff->idResource=$resource;
$newAff->idle=$idle;
$newAff->rate=$rate;
$newAff->startDate=$startDate;
$newAff->endDate=$endDate;
$newAff->idProfile=$profile;
$result=$newAff->save();

$oldRes=new ResourceAll($aff->idResource);
// save old affectation
if ($startDate) {
  $endTst=addWorkDaysToDate($startDate, -1);
  if ($endTst>=$aff->startDate and !$aff->endDate) $aff->endDate=$endTst;
  if ($aff->endDate and $aff->endDate<date('Y-m-d')) {
    $aff->idle=1;
  }
} else {
  $aff->idle=1;
}
$aff->save();

$proj=new Project($newAff->idProject);
$listProj=getListToChange($proj,$aff->idResource);
$crit='idProject in '.transformListIntoInClause($listProj).' and idResource='.$aff->idResource;


// Change the assignments
$ass=new Assignment();
$assList=$ass->getSqlElementsFromCriteria(null,null,$crit);
$pw=new PlannedWork();
foreach ($assList as $ass) {
  $needNew=true;
  $where='idAssignment='.$ass->id;
  $left=0;
  $assigned=0;
  if (! $startDate) {
    $left=$ass->leftWork;
    $assigned=$ass->assignedWork;
    if ($ass->realWork==0) {
      $needNew=false;
    }
  } else {
    $where.=" and workDate>='$startDate'";
    $left=$pw->sumSqlElementsFromCriteria('work', null, $where);
    if ($ass->realWork==0 and $left==$ass->leftWork) {
      $needNew=false;
    }
  }
  if ($left>0 or $assigned>=0) {
    if ($needNew) {
      $newAss=new Assignment();
      $newAss->idProject=$ass->idProject;
      $newAss->refType=$ass->refType;
      $newAss->refId=$ass->refId;
      $newAss->assignedWork=$left;
      $newAss->leftWork=$left;
      $ass->assignedWork-=$left;
      if ($ass->assignedWork<0) $ass->assignedWork=0;
      $ass->leftWork-=$left;
      if ($ass->leftWork<0) $ass->leftWork=0;
    } else {
      $newAss=$ass;
    } 
    $newAss->idResource=$resource;;
    $newAss->plannedWork=$newAss->realWork+$newAss->leftWork;
    $newAss->notPlannedWork=0;
    $newAss->rate=$rate;
    if ($resObj->isResourceTeam and !$oldRes->isResourceTeam) { // deduct capacity from rate
      $newAss->capacity=round($oldRes->capacity*$ass->rate/100,2);
    }
    $newAss->isResourceTeam=$resObj->isResourceTeam;
    $newAss->idRole=(isset($costArray[$ass->idRole]))?$ass->idRole:$defaultRole;
    $newAss->dailyCost=(isset($costArray[$ass->idRole]))?$costArray[$ass->idRole]:$defaultCost;
    $newAss->newDailyCost=$newAss->dailyCost;
    $newAss->assignedCost=$newAss->assignedWork*$newAss->dailyCost;
    $newAss->leftCost=$newAss->leftWork*$newAss->dailyCost;
    $newAss->plannedCost=$newAss->plannedWork*$newAss->dailyCost;
    $newAss->save();
    if ($needNew) $ass->save();
    $pw->purge($where);
  }
}

// Message of correct saving
displayLastOperationStatus($result);

function getListTochange($proj,$idRes) {
  $result=array();
  $result[$proj->id]=$proj->name;
  $subProjList=$proj->getSubProjects(true,true);
  foreach ($subProjList as $subProj) {
    $aff=new Affectation();
    $countAff=$aff->countSqlElementsFromCriteria(array('idProject'=>$subProj->id,'idResource'=>$idRes));
    if ($countAff==0) {
      $result=array_merge_preserve_keys($result,getListTochange($subProj,$idRes));
    }
  }
  return $result;
}

?>
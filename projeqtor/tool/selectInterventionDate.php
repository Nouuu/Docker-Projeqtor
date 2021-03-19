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
$date=RequestHandler::getDatetime('date');
$resource=RequestHandler::getId('resource');
$period=RequestHandler::getValue('period');
if ($period!='AM' and $period!='PM' and $period!='AMX' and $period!='PMX') {
  traceLog("saveInterventionDate.php incorrect period '$period'");
  exit;
}
$idMode=RequestHandler::getValue('idMode');
if ($idMode and ! is_numeric($idMode)) {
  traceLog("saveInterventionDate.php incorrect idMode '$idMode'");
  exit;
}
$letterMode=RequestHandler::getValue('letterMode');
if ($letterMode!='' and strlen($letterMode)>3) {
  traceLog("saveInterventionDate.php incorrect letterMode '$letterMode'");
  exit;
}
$refType=RequestHandler::getClass('refType');
$refId=RequestHandler::getId('refId');
$resObj=new Resource($resource);
$manageCapacity=PlannedWorkManual::getManageCapacity($resObj);
$halfDayDuration=0.5;
if ($manageCapacity=='DURATION') {
  $capacity=$resObj->getCapacityPeriod($date);
  $halfDayDuration=$capacity/2;
}
$allDay=false;
if (substr($period,-1)=="X") {
  $allDay=true;
  $period=substr($period,0,2);
  $periodx=($period=='AM')?'PM':'AM';
}
$resourceIncompatible = new ResourceIncompatible();
$critArray=array('idResource'=>$resource);
$incompatibleResourceList=$resourceIncompatible->getSqlElementsFromCriteria($critArray, false);
$lstIncompatible=array();
foreach ($incompatibleResourceList as $inc) {
  $lstIncompatible[$inc->idIncompatible]=$inc->idIncompatible;
}
$critIncompatible="idResource in ".transformListIntoInClause($lstIncompatible)." and workDate='$date'";
$critIncompatible.=" and refType is not null and refId is not null";
//$critIncompatible.=" and refType='$refType' and refId=$refId";
$pwm=SqlElement::getSingleSqlElementFromCriteria('PlannedWorkManual', array('workDate'=>$date,'idResource'=>$resource,'period'=>$period));
if ($allDay) $pwmx=SqlElement::getSingleSqlElementFromCriteria('PlannedWorkManual', array('workDate'=>$date,'idResource'=>$resource,'period'=>$periodx));
if($pwm->id and $pwm->refType and $pwm->refId){
  $lstProjectVisible = $user->getVisibleProjects();
  $project =new Project($pwm->idProject,true);
  $profile=getSessionUser()->getProfile($project);
  $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$profile, 'scope'=>'assignmentEdit'));
  if($habil->rightAccess!=1 or !array_key_exists($pwm->idProject, $lstProjectVisible)){
    echo '{"error":"'.i18n('errorUpdateRights').'"}';
    exit;
  }
}
if($allDay and $pwmx->id and $pwmx->refType and $pwmx->refId){
  $lstProjectVisible = $user->getVisibleProjects();
  $project =new Project($pwmx->idProject,true);
  $profile=getSessionUser()->getProfile($project);
  $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$profile, 'scope'=>'assignmentEdit'));
  if($habil->rightAccess!=1 or !array_key_exists($pwmx->idProject, $lstProjectVisible)){
    echo '{"error":"'.i18n('errorUpdateRights').'"}';
    exit;
  } 
}
if ($refType and $refId and (!$pwm->id or $pwm->refType!=$refType or $pwm->refId!=$refId) and count($lstIncompatible)>0) {
  $nb=$pwm->countSqlElementsFromCriteria(null,"$critIncompatible and period='$period'");
  if ($nb>0) {
    echo '{"error":"'.i18n('errorIncompatibleAlreadyPlanned').'"}';
    exit;
  }
}
if ($allDay and $refType and $refId and (!$pwmx->id or $pwmx->refType!=$refType or $pwmx->refId!=$refId) and count($lstIncompatible)>0) {
  $nb=$pwm->countSqlElementsFromCriteria(null,"$critIncompatible and period='$periodx'");
  if ($nb>0) {
    echo '{"error":"'.i18n('errorIncompatibleAlreadyPlanned').'"}';
    exit;
  }
}
Sql::beginTransaction();
//gautier habilation

if (!$pwm->id) {
  $pwm->setDates($date);
  $pwm->idResource=$resource;
  $pwm->period=$period;
}
if ($allDay and !$pwmx->id) {
  $pwmx->setDates($date);
  $pwmx->idResource=$resource;
  $pwmx->period=$periodx;
}
if ($refType and $refId and $idMode) {
  if ($pwm->refType==$refType and $pwm->refId==$refId and $pwm->idInterventionMode==$idMode) {
    $pwm->refType=null;
    $pwm->refId=null;
    $pwm->work=null;
    $pwm->idInterventionMode=null;
    if ($allDay) {
      $pwmx->refType=null;
      $pwmx->refId=null;
      $pwmx->work=null;
      $pwmx->idInterventionMode=null;
    }
  } else {
    $pwm->refType=$refType;
    $pwm->refId=$refId;
    $pwm->work=$halfDayDuration;
    $pwm->idInterventionMode=$idMode;
    if ($allDay) {
      $pwmx->refType=$refType;
      $pwmx->refId=$refId;
      $pwmx->work=$halfDayDuration;
      $pwmx->idInterventionMode=$idMode;
    }
  }
} else if ($idMode) {
  if ($pwm->idInterventionMode==$idMode) {
    $pwm->idInterventionMode=null;
    if ($allDay) {
      $pwmx->idInterventionMode=null;
    }
  } else {
    $pwm->idInterventionMode=$idMode;
    if ($allDay) {
      $pwmx->idInterventionMode=$idMode;
    }
  }
} else if ($refType and $refId) {
  if ($pwm->refType==$refType and $pwm->refId==$refId) {
    $pwm->refType=null;
    $pwm->refId=null;
    $pwm->work=null;
    if ($allDay) {
      $pwmx->refType=null;
      $pwmx->refId=null;
      $pwmx->work=null;
    }
  } else {
    $pwm->refType=$refType;
    $pwm->refId=$refId;
    $pwm->work=$halfDayDuration;
    if ($allDay) {
      $pwmx->refType=$refType;
      $pwmx->refId=$refId;
      $pwmx->work=$halfDayDuration;
    }
  }
}
$pwm->inputUser=getCurrentUserId();
$pwm->inputDateTime=date('Y-m-d H:i:s');
if ($allDay) {
  $pwmx->inputUser=getCurrentUserId();
  $pwmx->inputDateTime=date('Y-m-d H:i:s');
}
if (!$pwm->idInterventionMode and !$pwm->refType and !$pwm->refId) {
  $result=$pwm->delete();
  if ($allDay) {
    $pwmx->delete();
  }
} else {
  $result=$pwm->save();
  if ($allDay) {
    $pwmx->save();
  }
}
// Message of correct saving
ob_start();
displayLastOperationStatus($result);
ob_clean();
$ass=SqlElement::getSingleSqlElementFromCriteria('Assignment', array('refType'=>$refType,'refId'=>$refId,'idResource'=>$resource));
$assigned=($ass->assignedWork)?$ass->assignedWork:0;
$real=($ass->realWork)?$ass->realWork:0;
$left=($ass->leftWork)?$ass->leftWork:0;
echo '{"assigned":'.$assigned.',"real":'.$real.',"left":'.$left.'}';
?>
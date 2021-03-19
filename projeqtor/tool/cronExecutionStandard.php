<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2018 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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

require_once "../tool/projeqtor.php";
$operation=RequestHandler::getValue('operation');
if ($operation=='saveDefinition') {
  cronSaveDefinition();
} else if ($operation=='activate') {
  cronActivate();
}

function cronPlanningDifferential(){
  $user=new User();//getSessionUser();
  $user->idProfile=1; // Admin
  $user->resetAllVisibleProjects();
  setSessionUser($user);
  SqlList::cleanAllLists();
  $startDatePlan=cronPlanningStartDate(Parameter::getGlobalParameter("automaticPlanningDifferentialDate"));
  $arrayProj=array();
  $pe=new PlanningElement();
  $lst=$pe->getSqlElementsFromCriteria(array('refType'=>'Project','needReplan'=>1));
  foreach ($lst as $pe) {
    $arrayProj[]=$pe->refId;
  }
  $mode=i18n("paramAutomaticPlanningDifferential");
  $mode=str_replace(array("<b>","</b>"),array("",""),$mode);
  traceLog(i18n("sectionAutomaticPlanning").' : '.$mode." - ".i18n('colStart')." - ".i18n('projects').' : ' .((count($arrayProj))?implode(',',$arrayProj):i18n('paramNone')));
  if (count($arrayProj)>0) {
    //Sql::beginTransaction(); #3601 : management of transaction in now included in PlannedWork::plan()
    $result=PlannedWork::plan($arrayProj, $startDatePlan);
    $status = getLastOperationStatus ( $result );
    //if ($status == "OK" or $status=="NO_CHANGE" or $status=="INCOMPLETE") {
    //  Sql::commitTransaction ();
    //} else {
    //  Sql::rollbackTransaction ();
    //}
    if ($status == "OK") $resultStatus=i18n("planningResultOK");
    else if ($status == "NO_CHANGE" or $status == "INCOMPLETE") $resultStatus=i18n("planningResultNoChange");
    else $resultStatus=i18n("planningResultError");
    traceLog(i18n("sectionAutomaticPlanning").' : '.$mode." - ".i18n('colEnd')." - ". i18n("colResult"). " = $resultStatus");
  } else {
    $status='NO_CHANGE';
  }
}

function cronPlanningComplete(){
  $user=new User();//getSessionUser();
  $user->idProfile=1; // Admin
  $user->resetAllVisibleProjects();
  setSessionUser($user);
  SqlList::cleanAllLists();
  $startDatePlan=cronPlanningStartDate(Parameter::getGlobalParameter("automaticPlanningCompleteDate"));
  $enterPlannedAsReal=Parameter::getGlobalParameter('automaticFeedingOfTheReal');
  if ($enterPlannedAsReal=='YES') {
    PlannedWork::enterPlannedWorkAsReal(null, $startDatePlan);  
  }
  
  $mode=i18n("paramAutomaticPlanningComplete");
  $mode=str_replace(array("<b>","</b>"),array("",""),$mode);
  traceLog(i18n("sectionAutomaticPlanning").' : '.$mode." - ".i18n('colStart')." - ".i18n('projects').' : '.i18n('all'));
  //Sql::beginTransaction(); #3601 : management of transaction in now included in PlannedWork::plan()
  $result=PlannedWork::plan(array(' '), $startDatePlan);
  $status = getLastOperationStatus ( $result );
  //if ($status == "OK" or $status=="NO_CHANGE" or $status=="INCOMPLETE") {
  //  Sql::commitTransaction ();
  //} else {
  //  Sql::rollbackTransaction ();
  //}
  if ($status == "OK") $resultStatus=i18n("planningResultOK");
  else if ($status == "NO_CHANGE" or $status == "INCOMPLETE") $resultStatus=i18n("planningResultNoChange");
  else $resultStatus=i18n("planningResultError");
  traceLog(i18n("sectionAutomaticPlanning").' : '.$mode." - ".i18n('colEnd')." - ". i18n("colResult"). " = $resultStatus");
}

function cronPlanningStartDate($param) {
  if ($param=="W") {
    return date('Y-m-d',firstDayofWeek()); // Call with no parameter will return first day of current week
  } else if ($param=="M") {
    return date('Y-m').'-01';
  } else if (substr($param,0,1)=='J') {
    $day=substr($param,1);
    if ( is_numeric($day) and $day!=0) {
      return addDaysToDate(date('Y-m-d'), $day);
    } else {
      return date('Y-m-d');
    }
  } else {
    return date('Y-m-d',firstDayofWeek());
  }
}

function cronSaveDefinition() {
  $minutes=RequestHandler::getValue('cronDefinitonMinutes');
  $hours=RequestHandler::getValue('cronDefinitonHours');
  $dayOfMonth=RequestHandler::getValue('cronDefinitonDayOfMonth');
  $month=RequestHandler::getValue('cronDefinitonMonth');
  $dayOfWeek=RequestHandler::getValue('cronDefinitonDayOfWeek');
  
  $scope=RequestHandler::getValue('cronExecutionScope');
  $cronExecution=CronExecution::getObjectFromScope($scope);
  
  $cronStr=$minutes.' '.$hours.' '.$dayOfMonth.' '.$month.' '.$dayOfWeek;
  $cronExecution->idle=1; // Desactivate after save (will force reactivate and then CRON relaunch
  
  if (! $cronExecution->fileExecuted) {
    if (substr($scope,0,15)=='imputationAlert') {
      $cronExecution->fileExecuted="../tool/generateImputationAlert.php";
    } else {
      $cronExecution->fileExecuted="../tool/cronExecutionStandard.php";
    }
  }
  if (! $cronExecution->fonctionName) $cronExecution->fonctionName="cron". ucfirst($scope);
  $cronExecution->cron=$cronStr;
  $cronExecution->nextTime=null;
  $result=$cronExecution->save();
}

function cronActivate() {
  $scope=RequestHandler::getValue('cronExecutionScope');
  $cronExecution=CronExecution::getObjectFromScope($scope);
  $cronExecution->idle=($cronExecution->idle==0)?1:0;
  $cronExecution->nextTime=null;
  $result=$cronExecution->save();
}

function dataCloningCheckRequest(){
  $dataCloning = new DataCloning();
  $dataCloningList = $dataCloning->getSqlElementsFromCriteria(array('idle'=>'0'));
  foreach ($dataCloningList as $data){
    if(!$data->isActive and !$data->isRequestedDelete and !$data->codeError){
      $data->createDataCloning($data->id);
    }
    if($data->isRequestedDelete){
      $data->deleteDataCloning($data->id);
    }
  }
}

//florent
function archiveHistory(){
  $hist=new History();
  $histArch= new HistoryArchive();
  $timeToArchive=Parameter::getGlobalParameter('cronArchiveTime');
  $startArchive=Parameter::getGlobalParameter('cronArchivePlannedDate');
  $tableHist=$hist->getDatabaseTableName();
  $tableHistArch=$histArch->getDatabaseTableName();
  $archivDate = date('Y-m-d', strtotime("-".$timeToArchive." day"));
  $colList="";
  foreach ($hist as $fld=>$val) {
    if (substr($fld,0,1)=='_' or $fld=='id') continue;
    $col=$hist->getDatabaseColumnName($fld);
    if ($col) {
      $colList.="$col, ";
    }
  }
  $colList=substr($colList,0,-2);
  $requestIns="INSERT INTO $tableHistArch ($colList)\n"
      ."SELECT $colList FROM $tableHist WHERE operationDate < '".$archivDate."'"; //   INSERT INTO `archivehistory` (`idHistory`,`refType`,`refId`,`operation`,`colName`,`oldValue`,`newValue`,`operationDate`,`isWorkHistory`,`idUser`) 
  $clauseDel="operationDate < '".$archivDate."'";
  SqlDirectElement::execute($requestIns);
  $res=Sql::$lastQueryNbRows;
  if($res > 0){
    $hist->purge($clauseDel);
  }
}
?>
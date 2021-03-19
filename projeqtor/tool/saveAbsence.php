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

/** ============================================================================
 * Save real work allocation.
 */

require_once "../tool/projeqtor.php";

//parameter
$actId = RequestHandler::getId('actId');
$idProject = RequestHandler::getId('idProject');
$assId = RequestHandler::getId('assId');
$workVal = RequestHandler::getNumeric('workVal');
$day = RequestHandler::getValue('day');
$workDay = RequestHandler::getValue('workDay');
$month = RequestHandler::getValue('month');
$year = RequestHandler::getYear('year');
$week = RequestHandler::getValue('week');
$userId = RequestHandler::getId('userId');
$editWork = false;
$res= new Resource($userId,true);
$etp= round($res->capacity,2);
//open transaction bdd

$sumVal=0;
$plannedWorkM= new PlannedWorkManual();
$where=array("workDate"=>$workDay, 'idResource'=>$userId);
$asWork=$plannedWorkM->getSqlElementsFromCriteria($where);
if(!empty($asWork)){
  foreach ($asWork as $id=>$work){
    $sumVal+=$work->work;
  }
}


  Sql::beginTransaction();
  $result = "";
  if($sumVal!=0 and $sumVal+$workVal>$res->capacity){
    $result = 'warningPlanned';
    echo $result;
  }else{
    if ($workVal == 0){
        $work = new Work();
        $where = "refType = 'Activity' and refId =".$actId." and idResource =".$userId." and idProject=".$idProject." and idAssignment =".$assId." and day='".$day."'";
        $listWork = $work->getSqlElementsFromCriteria(null,false,$where);
        
        //delete work
        foreach ($listWork as $isWork){
          $isWork->deleteWork();
        }
    }else {
      $work = new Work();
      $where = " idProject in " . Project::getAdminitrativeProjectList() ;
      $where .= " and refType = 'Activity' and idResource =".$userId." and day='".$day."'";
      $listWork = $work->getSqlElementsFromCriteria(null,false,$where);
      $unitAbs = Parameter::getGlobalParameter('imputationUnit');
      if($unitAbs != 'days'){
      	$maxHour = Parameter::getGlobalParameter('dayTime');
      	$somWork = $workVal/$maxHour;
      }else{
        $somWork = $workVal;
      }
      foreach ($listWork as $isWork){
        if($isWork->refId == $actId and $somWork <= $etp){
          if($unitAbs != 'days'){
            $somWork = $workVal/$maxHour;
            $isWork->work = $somWork;
          }else{
            $isWork->work = $workVal;
          }
          $editWork = true;
          $isWork->saveWork();
          $somWork += $workVal;
        } else {
          $somWork += $isWork->work;
        }
      }
      if(!$editWork){
        if($somWork <= $etp){
          //put parameter in work object
          $work->refType = 'Activity';
          $work->refId = $actId;
          $work->setDates($workDay);
          $work->idResource = $userId;
          if($unitAbs != 'days'){
          	$workVal = $workVal/$maxHour;
          }
          $work->work = $workVal;
          $work->idProject = $idProject;
          $work->idAssignment = $assId;
          //save work
          $work->saveWork();
        }else {
          $result = 'warning';
          echo $result;
          //dojo.byId('warningDiv').style.display = 'block';
        }
      }
    }
  }
  // commit work
  Sql::commitTransaction();
  
?>
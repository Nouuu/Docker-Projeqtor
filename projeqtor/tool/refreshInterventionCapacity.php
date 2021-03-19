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
scriptLog('   ->/tool/saveResourceCapacity.php');

$color = "#ffffff";

$date= RequestHandler::getValue('date');
$refId= RequestHandler::getId('refId');
$period= trim(RequestHandler::getValue('period'));
if (isOffDay($date)) {
  $color="#d0d0d0";
}
if(!trim($refId))$refId=0;

if(strlen($period)=="3"){
 $colorAM = $color;
 $colorPM = $color;
 $month = substr($date, 0,-3);
 $month = str_replace ( '-' , "" , $month);
 $where= ' workdate=\''.$date.'\' and refType = \'Activity\' and refId='.$refId;
 $obj=new PlannedWorkManual();
 $listOfDayByEtp=$obj->countGroupedSqlElementsFromCriteria(null,array('workdate','period'), $where);
 $myEtp = SqlElement::getSingleSqlElementFromCriteria("InterventionCapacity", array('refType'=>'Activity','refId'=>$refId,'month'=>$month));
 $etp = $myEtp->fte;
 if($etp){
   if(isset($listOfDayByEtp[$date.'|AM'])){
     $value = $listOfDayByEtp[$date.'|AM'];
     if($value == $etp)$colorAM = "#50BB50";
     if($value > $etp)$colorAM = "#BB5050";
   }
   if(isset($listOfDayByEtp[$date.'|PM'])){
     $value = $listOfDayByEtp[$date.'|PM'];
     if($value == $etp)$colorPM = "#50BB50";
     if($value > $etp)$colorPM = "#BB5050";
   }
 }
 $color = $colorAM.$colorPM;
}else{
  $month = substr($date, 0,-3);
  $month = str_replace ( '-' , "" , $month);
  $where= ' workdate=\''.$date.'\' and refType = \'Activity\' and period = \''.$period.'\' and refId='.$refId;
  $obj=new PlannedWorkManual();
  $listOfDayByEtp=$obj->countGroupedSqlElementsFromCriteria(null,array('workdate'), $where);
  $myEtp = SqlElement::getSingleSqlElementFromCriteria("InterventionCapacity", array('refType'=>'Activity','refId'=>$refId,'month'=>$month));
  $etp = $myEtp->fte;
  if($etp){
    if(isset($listOfDayByEtp[$date])){
      $value = $listOfDayByEtp[$date];
      if($value == $etp)$color = "#50BB50";
      if($value > $etp)$color = "#BB5050";
    }
  }
}

echo json_encode($color);

?>
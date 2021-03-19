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
 * Delete the current object : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

$versionProjectId=null;
if (array_key_exists('affectationId',$_REQUEST)) {
  $affectationId=$_REQUEST['affectationId'];
}
$affectationId=trim($affectationId);
if ($affectationId=='') {
  $affectationId=null;
} 
if ($affectationId==null) {
  throwError('affectationId parameter not found in REQUEST');
}

Sql::beginTransaction();
$obj=new Affectation($affectationId);
//Gautier #3849
$autoAffectationPool=Parameter::getGlobalParameter('autoAffectationPool');
if($autoAffectationPool=="IMPLICIT"){
  $res = new ResourceAll($obj->idResourceSelect,true);
  if($res->isResourceTeam){
    $affPool = new Affectation();
    $listAffPool = $affPool->getSqlElementsFromCriteria(array('idResourceTeam'=>$res->id,'idProject'=>$obj->idProject));
    $start=($obj->startDate)?$obj->startDate:Affectation::$minAffectationDate;
    $end=($obj->endDate)?$obj->endDate:Affectation::$maxAffectationDate;
    foreach ($listAffPool as $valAffPool){
      $startPool=($valAffPool->startDate)?$valAffPool->startDate:Affectation::$minAffectationDate;
      $endPool=($valAffPool->endDate)?$valAffPool->endDate:Affectation::$maxAffectationDate;
      if($start <= $startPool and $end >= $endPool){
          $result=$valAffPool->delete();
      }
    }
  }
}
$result=$obj->delete();
// Message of correct saving
displayLastOperationStatus($result);
?>
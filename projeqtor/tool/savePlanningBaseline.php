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
 * Run planning
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/savePlanningBaseline.php');
if (! array_key_exists('idProjectPlanBaseline',$_REQUEST)) {
  throwError('idProjectPlanBaseline parameter not found in REQUEST');
}
$idProject=RequestHandler::getId('idProjectPlanBaseline',true); // validated to be id

$name=RequestHandler::getValue('namePlanBaseline',true); // validated during insertion

$date=RequestHandler::getDatetime('datePlanBaseline',true); // validated to be date 

$privacy=RequestHandler::getNumeric('planBaselinePrivacy',true); // validated to be numeric 

$id=RequestHandler::getId('idBaselinePlanBaseline',false);
$mode=($id)?'edit':'add'; 

projeqtor_set_time_limit(600);
Sql::beginTransaction();

$baseline=new Baseline($id);
if ($id) {
  $baseline->name=$name;
  $baseline->idPrivacy=$privacy;
  $result=$baseline->save();
} else {
  $baseline->idProject=$idProject;
  $baseline->baselineNumber=null;
  $baseline->name=$name;
  $baseline->baselineDate=$date;
  $baseline->idUser=getSessionUser()->id;
  $baseline->creationDateTime=date('Y-m-d H:i:s');
  $baseline->idPrivacy=$privacy;
  $res=new Resource(getSessionUser()->id);
  $baseline->idTeam=$res->idTeam;
  $result=$baseline->saveWithPlanning();
}
$result.= '<input type="hidden" id="lastPlanStatus" value="OK" />';
// return $result;
// Message of correct saving
displayLastOperationStatus($result);

if ($mode='add') {
  // Once long treatment has been done (and after commit), define Number
  $maxList=$baseline->getSqlElementsFromCriteria(array('idProject'=>$idProject),false,null,'baselineNumber desc');
  $baseline->baselineNumber=(count($maxList)==0)?1:reset($maxList)->baselineNumber+1;
  $baseline->save();
}


?>
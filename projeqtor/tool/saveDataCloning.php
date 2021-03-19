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
$requestedDate = date('Y-m-d H:i:s');
$requestedDeletedDate = date('Y-m-d H:i:s');
$user = RequestHandler::getValue('dataCloningUser');
$name = RequestHandler::getValue('dataCloningName');
$idDataCloning = RequestHandler::getId('idDataCloning');
$idDataCloningParent = RequestHandler::getId('idDataCloningParent');
$status = RequestHandler::getValue('status');
$codeError = RequestHandler::getValue('codeError');
$result='';

//open transaction bdd
Sql::beginTransaction();

if($idDataCloning){
  $dataCloning = new DataCloning($idDataCloning, true);
  if($status == 'remove'){
    if($dataCloning->isActive){
      $dataCloning->requestedDeletedDate = $requestedDeletedDate;
      $dataCloning->isRequestedDelete = 1;
      $result=$dataCloning->save();
    }else{
      $result=$dataCloning->delete();
    }
  }else if($codeError){
    if(substr($codeError, 0, 22) == 'dataCloningErrorDelete'){
      //$dataCloning->codeError = null;
      $dataCloning->requestedDeletedDate = $requestedDeletedDate;
      $dataCloning->isRequestedDelete = 1;
    }else{
      $dataCloning->requestedDate = $requestedDate;
      $dataCloning->codeError = null;
      $dataCloning->isActive=0;
    }
    $result=$dataCloning->save();
  }else{
    $dataCloning->requestedDeletedDate = null;
    $dataCloning->isRequestedDelete = 0;
    $result=$dataCloning->save();
  }
}else{
  $dataCloning = new DataCloning();
  $activeUser = getSessionUser();
  
  $date = date('Y-m-d');
  $addDate =  addDaysToDate(date('Y-m-d'), 1);
  $wherePerDay = "requestedDate > '$date' and requestedDate < '$addDate' ";
  $dataCloningRequestorCountPerDay = $dataCloning->countSqlElementsFromCriteria(null, $wherePerDay);
  $dataCloningRequestorTotalPerDay = Parameter::getGlobalParameter('dataCloningPerDay');
  
  if($dataCloningRequestorTotalPerDay-$dataCloningRequestorCountPerDay > 0){
    $res = new Affectable($user);
    $date = date('Y-m-d');
    $addDate =  addDaysToDate(date('Y-m-d'), 1);
    $wherePerDay = "idResource = $user and idle = 0 ";
    $dataCloningCountPerDay = $dataCloning->countSqlElementsFromCriteria(null, $wherePerDay);
    $dataCloningPerDay = Parameter::getGlobalParameter('dataCloningPerDay');
    $dataCloningTotalObj=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array("scope"=>"dataCloningTotal", "idProfile"=>$res->idProfile));
    $dataCloningTotal = $dataCloningTotalObj->rightAccess;
    $dataCloningCountTotal = $dataCloning->countSqlElementsFromCriteria(array("idle"=>"0", "idResource"=>$user));

    if($dataCloningTotal-$dataCloningCountTotal > 0){
    	$cronExecution = SqlElement::getSingleSqlElementFromCriteria('CronExecution', array('fonctionName'=>'dataCloningCheckRequest'));
    	if($idDataCloningParent){
    		$dataCloning->idOrigin = $idDataCloningParent;
    	}
    	$dataCloning->versionCode = $version;
    	$dataCloning->idResource = $user;
    	$dataCloning->idRequestor = $activeUser->id;
    	$dataCloning->requestedDate = $requestedDate;
    	$dataCloning->name = $name;
    	$dataCloning->plannedDate = $cronExecution->nextTime;
    	$result=$dataCloning->save();
    }else{
    	$result='<b>Contrôles invalides.</b><br/><br/>'.i18n('errorAddDataCloning').'<input type="hidden" id="lastOperationStatus" value="INVALID" /><input type="hidden" id="lastSaveId" value="" /><input type="hidden" id="lastOperation" value="control" />';
    }
  }else{
  	$result='<b>Contrôles invalides.</b><br/><br/>'.i18n('errorAddDataCloningPerDay').'<input type="hidden" id="lastOperationStatus" value="INVALID" /><input type="hidden" id="lastSaveId" value="" /><input type="hidden" id="lastOperation" value="control" />';
  }
}
displayLastOperationStatus($result);
?>
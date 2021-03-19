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
 * Save imputation validation.
 */

require_once "../tool/projeqtor.php";

//parameter
$buttonAction = RequestHandler::getValue('buttonAction');
//open transaction bdd
Sql::beginTransaction();

if($buttonAction != 'validateSelection'){
  $idWorkPeriod = RequestHandler::getValue('idWorkPeriod');
  $workPeriod = WorkPeriod::getWorkPeriod($idWorkPeriod); //new WorkPeriod($idWorkPeriod, true);
  switch ($buttonAction){
  	case 'cancelSubmit' :
  	  $workPeriod->submitted = 0;
  	  $workPeriod->submittedDate = null;
  	  break;
  	case 'cancelValidation' :
  	  $workPeriod->validated = 0;
  	  $workPeriod->validatedDate = null;
  	  $workPeriod->idLocker = null;
  	  break;
  	case 'validateWork' :
  	  $workPeriod->validated = 1;
  	  $workPeriod->validatedDate = date('Y-m-d H:i:s');
  	  $workPeriod->idLocker = getCurrentUserId();
  	  break;
  	default:
  	  break;
  }
  $res=$workPeriod->save();
}
if($buttonAction == 'validateSelection'){
  $idWorkPeriod = RequestHandler::getValue('idWorkPeriod');
  $arrayId = explode(',', $idWorkPeriod);
  foreach ($arrayId as $id){
    $workPeriod = WorkPeriod::getWorkPeriod($id);
    if($workPeriod->validated == 0){
      $workPeriod->validated = 1;
      $workPeriod->validatedDate = date('Y-m-d H:i:s');
      $workPeriod->idLocker = getCurrentUserId();
      $res=$workPeriod->save();
    }
  }
}

// commit workPeriod
Sql::commitTransaction();

?>
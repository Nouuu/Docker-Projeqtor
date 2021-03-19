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

$idResource= RequestHandler::getId('idResource');
$resourceTest = new Resource($idResource);
$capacity = RequestHandler::getValue('resourceSurbooking');
$start = RequestHandler::getValue('resourceSurbookingStartDate');
$end = RequestHandler::getValue('resourceSurbookingEndDate');
$description = RequestHandler::getValue('resourceSurbookingDescription');
$idle = RequestHandler::getBoolean('resourceSurbookingIdle');
if($idle){
  $idle = 1;
}else{
  $idle = 0;
}
$mode = RequestHandler::getValue('mode');
Sql::beginTransaction();
$result = "";

if($mode == 'edit'){
  $idResourceSurbooking = RequestHandler::getId('idResourceSurbooking');
  $resourceSurbooking = new ResourceSurbooking($idResourceSurbooking);
  $resourceSurbooking->idResource = $idResource;
  $resourceSurbooking->capacity = $capacity;
  $resourceSurbooking->description = nl2brForPlainText($description);
  $resourceSurbooking->idle = $idle;
  $resourceSurbooking->startDate = $start;
  $resourceSurbooking->endDate = $end;
  $res=$resourceSurbooking->save();
}else{
  $resourceSurbooking = new ResourceSurbooking();
  $resourceSurbooking->idResource = $idResource;
  $resourceSurbooking->capacity = $capacity;
  $resourceSurbooking->description = nl2brForPlainText($description);
  $resourceSurbooking->idle = $idle;
  $resourceSurbooking->startDate = $start;
  $resourceSurbooking->endDate = $end;
  $res=$resourceSurbooking->save();
}
if($result == ""){
  $result = getLastOperationStatus($res);
}
if ($result == "OK") {
	if(sessionTableValueExist('surbookingPeriod', $idResource)){
		unsetSessionTable('surbookingPeriod', $idResource);
	}
}

// Message of correct saving
displayLastOperationStatus($res);

?>
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
 * 
 */
require_once "../tool/projeqtor.php";

$objectClass=null;
if (isset($_REQUEST['objectClassName'])) {
  $objectClass=$_REQUEST['objectClassName'];
}
$objectId=null;
if (isset($_REQUEST['id'])) {
  $objectId=$_REQUEST['id'];
} else if (isset($_REQUEST['id_detail'])) {
  $objectId=$_REQUEST['id_detail'];
}
if ($objectClass===null or $objectId===null) {
  throwError('className and/or id not found in REQUEST ('.$objectClass.'/'.$objectId.')');
}

$obj=new $objectClass($objectId);

$type=null;
$typeName='id'.$objectClass.'Type';
if ($objectClass=='PeriodicMeeting') $typeName='idMeetingType';
if (isset($_REQUEST[$typeName])) {
	$type=$_REQUEST[$typeName]; // Note: validated as numeric in base SqlElement constructor
}
$status=null;
if (isset($_REQUEST['idStatus'])) {
  $status=$_REQUEST['idStatus']; // Note: validated as numeric in base SqlElement constructor
}
$planningMode=null;
$pmName=$objectClass.'PlanningElement_id'.$objectClass.'PlanningMode';
if (isset($_REQUEST[$pmName])) {
  $planningMode=$_REQUEST[$pmName]; // Note: validated as numeric in base SqlElement constructor
}

$result=$obj->getExtraRequiredFields($type,$status,$planningMode,null);
$peName=$objectClass.'PlanningElement';
if (property_exists($obj, $peName)) {
  $pe=$obj->$peName;
  $resultPe=$pe->getExtraRequiredFields($type,$status,$planningMode,null);
  foreach ($resultPe as $key=>$val) {
    $result[$peName.'_'.$key]=$val;
  }
}
if (property_exists($obj, 'WorkElement') and $objectClass!='TicketSimple') {
  $we=$obj->WorkElement;
  $resultWe=$we->getExtraRequiredFields($type,$status,$planningMode,null);
  foreach ($resultWe as $key=>$val) {
    $result['WorkElement_'.$key]=$val;
  }
}


$arrayDefault=array('description'=>'optional', 'result'=>'optional', 'idResource'=>'optional', 'idResolution'=>'optional',
   $peName.'_validatedStartDate'=>'optional', $peName.'_validatedEndDate'=>'optional', $peName.'_validatedDuration'=>'optional');
foreach ($arrayDefault as $key=>$val) {
  if (property_exists($obj,$key) and $obj->isAttributeSetToField($key,'required')) {
    $arrayDefault[$key]='required';
  }
}
$result=array_merge($arrayDefault,$result);

/*
// BABYNUS : add management of extra required fields (defined through plugin)

$user=getSessionUser();
$profile=$user->getPrfile(obj);
$extraResult=$obj->getExtraRequiredFields($type,$status,null,$profile);
$peName=$objectClass.'PlanningElement';
if (property_exists($obj, $peName)) {
  $pe=$obj->$peName;
  $resultPe=$pe->getExtraHiddenFields($type,$status,$profile);
  $result=array_merge($result,$resultPe);
}
// BABYNUS : End*/
echo json_encode($result);
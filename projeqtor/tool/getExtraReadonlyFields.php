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
if (isset($_REQUEST['objectClass'])) {
  $objectClass=$_REQUEST['objectClass'];
}
Security::checkValidClass($objectClass);
$type=null;
if (isset($_REQUEST['type'])) {
  $type=$_REQUEST['type'];
}
Security::checkValidId($type);
$status=null;
if (isset($_REQUEST['status'])) {
  $status=$_REQUEST['status'];
}
Security::checkValidId($status);
$profile=null;
if (isset($_REQUEST['profile'])) {
  $profile=$_REQUEST['profile'];
}
Security::checkValidId($profile);

$peName=$objectClass.'PlanningElement';

$obj=new $objectClass();
$result=$obj->getExtraReadonlyFields($type,$status,$profile);

$peName=$objectClass.'PlanningElement';
if (property_exists($obj, $peName)) {
  $pe=$obj->$peName;
  $resultPe=$pe->getExtraReadonlyFields($type,$status,$profile);
  $result=array_merge($result,$resultPe);
}
if (property_exists($obj, 'WorkElement') and $objectClass!='TicketSimple') {
  $we=$obj->WorkElement;
  $resultWe=$we->getExtraReadonlyFields($type,$status,$profile);
  $result=array_merge($result,$resultWe);
}
echo json_encode($result);
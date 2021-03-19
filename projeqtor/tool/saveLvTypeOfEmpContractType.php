<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Eliott LEGRAND (from Salto Consulting - 2018) 
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
/**
 * Save a leaveTypeOfEmploymentContractType object from the form sent by dynamicDialogOfEmpContractType.php
 */
// ELIOTT - LEAVE SYSTEM
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/saveLvTypeOfEmpContractType.php');

if (! array_key_exists('idLvTypeOfContractType',$_REQUEST)) {
  throwError('idLvTypeOfContractType parameter not found in REQUEST');
}
$idLvTypeOfContractType = $_REQUEST['idLvTypeOfContractType'];

if (! array_key_exists('idEmploymentContractType',$_REQUEST)) {
  throwError('idEmploymentContractType parameter not found in REQUEST');
}
$idEmploymentContractType = $_REQUEST['idEmploymentContractType'];

if (! array_key_exists('rightIdLeaveType',$_REQUEST)) {
  throwError('rightIdLeaveType parameter not found in REQUEST');
}
$idLeaveType = $_REQUEST['rightIdLeaveType'];

if (! array_key_exists('rightStartMonthPeriod',$_REQUEST)) {
  throwError('rightStartMonthPeriod parameter not found in REQUEST');
}
$startMonthPeriod = $_REQUEST['rightStartMonthPeriod'];
if ($startMonthPeriod==0) {
    $startMonthPeriod=null;
}

if (! array_key_exists('rightStartDayPeriod',$_REQUEST)) {
  throwError('rightStartDayPeriod parameter not found in REQUEST');
}
$startDayPeriod = $_REQUEST['rightStartDayPeriod'];

if (! array_key_exists('rightPeriodDuration',$_REQUEST)) {
  throwError('rightPeriodDuration parameter not found in REQUEST');
}
$periodDuration = $_REQUEST['rightPeriodDuration'];

if (! array_key_exists('rightQuantity',$_REQUEST)) {
  throwError('rightQuantity parameter not found in REQUEST');
}
$quantity = $_REQUEST['rightQuantity'];

if (! array_key_exists('rightEarnedPeriod',$_REQUEST)) {
  throwError('rightEarnedPeriod parameter not found in REQUEST');
}
$earnedPeriod = $_REQUEST['rightEarnedPeriod'];

if (array_key_exists('rightIsIntegerQuotity',$_REQUEST)) {
  $isIntegerQuotity = $_REQUEST['rightIsIntegerQuotity'];
}else{
  $isIntegerQuotity = 0;  
}

if (array_key_exists('rightNbDaysAfterNowLeaveDemandIsAllowed',$_REQUEST)) {
  $nbDaysAfterNowLeaveDemandIsAllowed = $_REQUEST['rightNbDaysAfterNowLeaveDemandIsAllowed'];
}else{
  $nbDaysAfterNowLeaveDemandIsAllowed = 0;  
}

if (array_key_exists('rightNbDaysBeforeNowLeaveDemandIsAllowed',$_REQUEST)) {
  $nbDaysBeforeNowLeaveDemandIsAllowed = $_REQUEST['rightNbDaysBeforeNowLeaveDemandIsAllowed'];
}else{
  $nbDaysBeforeNowLeaveDemandIsAllowed = 0;  
}

if (! array_key_exists('rightValidityDuration',$_REQUEST)) {
  throwError('rightValidityDuration parameter not found in REQUEST');
}
$validityDuration = $_REQUEST['rightValidityDuration'];

if (array_key_exists('rightIsJustifiable',$_REQUEST)) {
  $isJustifiable = $_REQUEST['rightIsJustifiable'];
}else{
  $isJustifiable = 0;  
}

if (array_key_exists('rightIsAnticipated',$_REQUEST)) {
  $isAnticipated = $_REQUEST['rightIsAnticipated'];
}else{
  $isAnticipated = 0;  
}
//rightIsAnticipated

Sql::beginTransaction();
$lvTypeOfEmpContractType = new LeaveTypeOfEmploymentContractType();
$lvTypeOfEmpContractType->id = $idLvTypeOfContractType;
$lvTypeOfEmpContractType->idLeaveType=$idLeaveType;
$lvTypeOfEmpContractType->idEmploymentContractType=$idEmploymentContractType;
$lvTypeOfEmpContractType->startMonthPeriod=$startMonthPeriod;
$lvTypeOfEmpContractType->startDayPeriod=$startDayPeriod;
$lvTypeOfEmpContractType->periodDuration=$periodDuration;
$lvTypeOfEmpContractType->quantity=$quantity;
$lvTypeOfEmpContractType->earnedPeriod=$earnedPeriod;
$lvTypeOfEmpContractType->validityDuration=$validityDuration;
$lvTypeOfEmpContractType->isJustifiable=$isJustifiable;
$lvTypeOfEmpContractType->isAnticipated=$isAnticipated;
$lvTypeOfEmpContractType->isIntegerQuotity=$isIntegerQuotity;
$lvTypeOfEmpContractType->nbDaysAfterNowLeaveDemandIsAllowed = $nbDaysAfterNowLeaveDemandIsAllowed;
$lvTypeOfEmpContractType->nbDaysBeforeNowLeaveDemandIsAllowed = $nbDaysBeforeNowLeaveDemandIsAllowed;

$result=$lvTypeOfEmpContractType->save();
displayLastOperationStatus($result);
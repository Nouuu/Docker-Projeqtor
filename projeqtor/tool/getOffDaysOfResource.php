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

// MTY - LEAVE SYSTEM

/** ============================================================================
 *  Get Off days et work days for a resource
 */
require_once "../tool/projeqtor.php";

//return all the leaves of the current user between startDate and enDate passed in REQUEST
if(!isset($_REQUEST['idRes'])){
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." ".i18n("missing")." : idRes", 
                                  false,
                                  "", 
                                  "CREATE OR SAVE LEAVE",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

$startDate=null;
$endDate=null;

if (isset($_REQUEST['startDate'])) {
    $startDate=$_REQUEST['startDate'];
}

if (isset($_REQUEST['endDate'])) {
    $endDate=$_REQUEST['endDate'];
}

$idRes = $_REQUEST['idRes'];

$res = new Resource($idRes);
// Off days for calendar and resource
$offDays = Calendar::getOffDayList($res->idCalendarDefinition, $startDate, $endDate);

// Work days for calendar and resource
$workDays = Calendar::getWorkDayList($res->idCalendarDefinition, $startDate, $endDate);

// Parameter Off Day
$defaultOffDays = array();
if (Parameter::getGlobalParameter('OpenDaySunday')=='offDays') {
    $defaultOffDays[0] = 0;
}    
if (Parameter::getGlobalParameter('OpenDayMonday')=='offDays') {
    $defaultOffDays[1] = 1;
}    
if (Parameter::getGlobalParameter('OpenDayTuesday')=='offDays') {
    $defaultOffDays[2] = 2;
}    
if (Parameter::getGlobalParameter('OpenDayWednesday')=='offDays') {
    $defaultOffDays[3] = 3;
}    
if (Parameter::getGlobalParameter('OpenDayThursday')=='offDays') {
    $defaultOffDays[4] = 4;
}    
if (Parameter::getGlobalParameter('OpenDayFriday')=='offDays') {
    $defaultOffDays[5] = 5;
}    
if (Parameter::getGlobalParameter('OpenDaySaturday')=='offDays') {
    $defaultOffDays[6] = 6;        
}
// Recursive Off Day of Calendar Definition
$calDef = new CalendarDefinition($res->idCalendarDefinition);
for ($i=0;$i<=6;$i++) {
    $dayOfWeek = "dayOfWeek".$i;
    if ($calDef->$dayOfWeek==1 and !array_key_exists($i, $defaultOffDays)) {
        $defaultOffDays[$i]=$i;
    }
}

$res=array(
    "offDays"=>$offDays,
    "workDays"=>$workDays,
    "defaultOffDays"=>$defaultOffDays
);

echo json_encode($res);

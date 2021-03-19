<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Eliott Legrand (Salto Consulting - 2018)
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

// ELIOTT - LEAVE SYSTEM

/** ============================================================================
 *  Create a new leave or update a leave with the datas passed in REQUEST
 *  Create when REQUEST['create'] = 'true'
 *  Update when REQUEST['create'] = 'false' AND REQUEST['idLeave'] not null ou not zero
 */
require_once "../tool/projeqtor.php";

if(     !isset($_REQUEST['idLeaveType']) || 
        !isset($_REQUEST['idLeaveStatus']) || 
        !isset($_REQUEST['startDate']) || 
        !isset($_REQUEST['endDate']) || 
        !isset($_REQUEST['nbDays']) || 
        !isset($_REQUEST['comment']) || 
        !isset($_REQUEST['startAMPM']) || 
        !isset($_REQUEST['endAMPM']) || 
        !isset($_REQUEST['create'])){
    $result= htmlSetResultMessage(null, 
                                  i18n("errorOneOfTheRequestFieldsIsMissing"), 
                                  false,
                                  "", 
                                  "CREATE OR SAVE LEAVE",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

if (!isset($_REQUEST['idEmployee'])) {
    $idEmployee = getSessionUser()->id;
} else {
    $idEmployee = $_REQUEST['idEmployee'];
}

if($_REQUEST['create']!=="true" && $_REQUEST['create']!=="false"){
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." ".i18n("CreateField"). " = ".$_REQUEST['create'], 
                                  false,
                                  "", 
                                  "CREATE OR SAVE LEAVE",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

if($_REQUEST['create']==="false" && !isset($_REQUEST['idLeave'])){
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." ".i18n("missing")." : idLeave", 
                                  false,
                                  "", 
                                  "CREATE OR SAVE LEAVE",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

if( ($_REQUEST['startAMPM']!="AM" && $_REQUEST['startAMPM']!="PM") || ($_REQUEST['endAMPM']!="AM" && $_REQUEST['endAMPM']!="PM") ){
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." AMPM - startAMPM = ".$_REQUEST['startAMPM']. " endAMPM = ".$_REQUEST['endAMPM'], 
                                  false,
                                  "", 
                                  "CREATE OR SAVE LEAVE",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

Sql::beginTransaction ();

if($_REQUEST['create']==="false"){
    $lv = new Leave($_REQUEST['idLeave']);
} else {
    $lv = new Leave();    
}

$lv->idEmployee = $idEmployee;
$lv->idLeaveType = $_REQUEST['idLeaveType'];
$lv->idStatus = $_REQUEST['idLeaveStatus'];
$lv->startDate = ( new DateTime( substr($_REQUEST['startDate'],0,15) ) ) -> format('Y-m-d');
$lv->startAMPM = $_REQUEST['startAMPM'];
$lv->endDate = ( new DateTime( substr($_REQUEST['endDate'],0,15) ) ) -> format('Y-m-d');
$lv->endAMPM = $_REQUEST['endAMPM'];
$lv->nbDays= $_REQUEST['nbDays'];
$lv->comment= $_REQUEST['comment'];

$lvSaveResult = $lv->save();
//for the update of the attribute left of employeeLeaveEarned
if (strpos($lvSaveResult,"OK") === false){
    Sql::rollbackTransaction();
} else {
    Sql::commitTransaction();
}

echo json_encode($lvSaveResult);
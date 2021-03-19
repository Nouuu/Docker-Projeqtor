<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Eliott Legrand (05/2018)
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

// LEAVE SYSTEM

/** ============================================================================
 *  Save leave's status and comment with the datas passed in REQUEST
 */
require_once "../tool/projeqtor.php";

if(     !isset($_REQUEST['idLeave']) || 
        !isset($_REQUEST['idLeaveStatus']) || 
        !isset($_REQUEST['comment']) 
){
    $result= htmlSetResultMessage(null, 
                                  i18n("errorOneOfTheRequestFieldsIsMissing"), 
                                  false,
                                  "", 
                                  "UPDADE LEAVE STATUS",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

Sql::beginTransaction ();

$lv = new Leave($_REQUEST['idLeave']);
$lv->idStatus = $_REQUEST['idLeaveStatus'];
$lv->comment=$_REQUEST['comment'];

$result = $lv->save();
$lastStatus = getLastOperationStatus($result);
if($lastStatus == "OK"){
    Sql::commitTransaction();
}else{
    Sql::rollbackTransaction();
}

echo json_encode($result);
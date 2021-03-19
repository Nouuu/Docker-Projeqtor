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

// ELIOTT - LEAVE SYSTEM

/** ============================================================================
 * delete the leave with the id passed in parameter
 */
require_once "../tool/projeqtor.php";

if(!isset($_REQUEST['idLeave'])){
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." ".i18n("missing")." : idLeave", 
                                  false,
                                  "", 
                                  "DELETE LEAVE",
                                  "INVALID");

    echo json_encode($result);
    exit;
}
Sql::beginTransaction ();
$lv = new Leave($_REQUEST['idLeave']);
$lvDelRes = $lv->delete();

//for the update of the attribute left of employeeLeaveEarned
if(getLastOperationStatus($lvDelRes) == "OK"){
    Sql::commitTransaction();
}else{
    Sql::rollbackTransaction();
}


$res=$lvDelRes;

echo json_encode($res);

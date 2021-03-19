<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Salto Consulting - 2018
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
 * 
 */
require_once "../tool/projeqtor.php";

//return the left quantity for all leave type and for an employee
if(isset($_REQUEST['idEmployee'])){
    $idEmployee = $_REQUEST['idEmployee'];
} else {
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." ".i18n("missing")." : idEmployee", 
                                  false,
                                  "", 
                                  "GET LEFT QUANTITY BY LEAVE TYPE FOR AN EMPLOYEE",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

$emp = new Employee($idEmployee);
if ($emp->id<0 or $emp->idle==1) { // idRes is'nt activ employee
    $result= htmlSetResultMessage(null, 
                                  i18n("EmployeePassedInRequestIsNotAnEmployee"), 
                                  false,
                                  "", 
                                  "GET LEFT QUANTITY BY LEAVE TYPE FOR AN EMPLOYEE",
                                  "INVALID");

    echo json_encode($result);
    exit;    
}

$leftList = $emp->getLeftLeavesByLeaveType();

$res=array("idEmployee" => $emp->id,
           "left" => $leftList);
echo json_encode($res);

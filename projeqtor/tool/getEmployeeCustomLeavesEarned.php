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
 * return the fields necessary to update the summary tab in view/leaveCalendar.php
 */
require_once "../tool/projeqtor.php";
if(!isset($_REQUEST['idEmployee'])){
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." ".i18n("missing")." : idEmployee", 
                                  false,
                                  "", 
                                  "GET EMPLOYEE CUSTOM LEAVES EARNED",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

$res=[];

$idEmployee = $_REQUEST['idEmployee'];
$lvsEarned = EmployeeLeaveEarned::getList(0, $idEmployee);

$critArrayEmpContract=array("idEmployee"=>(string)$idEmployee, "idle"=>'0');
$userEmpContract = SqlElement::getFirstSqlElementFromCriteria("EmploymentContract", $critArrayEmpContract);

// The leave types
$leaveTypes = LeaveType::getList();                         
foreach($leaveTypes as $lvt) {
    // Leave Type
    $resLine = [];
    $resLine["lvTColor"] = $lvt->color;
    $resLine["lvTOppositeColor"] = oppositeColor($lvt->color);
    $resLine["lvTName"] = $lvt->name;
    $resLine["custom"] = [];

    // Custom quantity
    $critArrayCustom = array("idLeaveType"=>(string)$lvt->id,"idEmploymentContractType"=>(string)$userEmpContract->idEmploymentContractType);
    $custom = new CustomEarnedRulesOfEmploymentContractType();
    $customs = $custom->getSqlElementsFromCriteria($critArrayCustom);
    $resLineCustom= [];
    foreach ($customs as $custom) {
        $resLineCustom["name"] = $custom->name;
        $resLineCustom["quantity"]= getCustomLeaveEarnedQuantity($custom, $idEmployee);
        $resLine["custom"][]=$resLineCustom;
    }
    $res[]=$resLine;    
}

echo json_encode($res);
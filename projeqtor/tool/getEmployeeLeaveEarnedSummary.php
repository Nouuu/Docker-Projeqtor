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
 * return the fields necessary to update the summary tab in view/leaveCalendar.php
 */
require_once "../tool/projeqtor.php";
if(!isset($_REQUEST['idEmployee'])){
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." ".i18n("missing")." : idEmployee", 
                                  false,
                                  "", 
                                  "GET EMPLOYEE LEAVE EARNED SUMMARY",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

$res=[];

$idEmployee = $_REQUEST['idEmployee'];
$lvsEarned = EmployeeLeaveEarned::getList(0, $idEmployee);

$critArrayEmpContract=array("idEmployee"=>(string)$idEmployee, "idle"=>'0');
$userEmpContract = SqlElement::getFirstSqlElementFromCriteria("EmploymentContract", $critArrayEmpContract);
if ($userEmpContract->id<=0) {
    $res = [];
    echo json_encode($res);    
} else {
    foreach($lvsEarned as $lvEarned){
        $resLine = [];
        $lvType = new LeaveType($lvEarned->idLeaveType);
        $resLine["lvTColor"] = $lvType->color;
        $resLine["lvTOppositeColor"] = oppositeColor($lvType->color);
        $resLine["lvTName"] = $lvType->name;
        if ($lvEarned->startDate==null) {
            $theStartDate = " ";
        } else {
            $theStartDate = (new DateTime($lvEarned->startDate))->format("Y/m/d");
        }
        if ($lvEarned->endDate!=null and $userEmpContract->endDate!=null and $userEmpContract->endDate<$lvEarned->endDate) {
            $theEndDate = (new DateTime($userEmpContract->endDate))->format("Y/m/d");                
        } else {
            if ($lvEarned->endDate==null) {
                $theEndDate=" ";
            } else {
                $theEndDate = (new DateTime($lvEarned->endDate))->format("Y/m/d");
            }
        }
        $critArrayLvTypeOf = array("idLeaveType"=>(string)$lvType->id,"idEmploymentContractType"=>(string)$userEmpContract->idEmploymentContractType);
        $lvTypeOfEmpContractType = SqlElement::getFirstSqlElementFromCriteria("LeaveTypeOfEmploymentContractType", $critArrayLvTypeOf);

        if($lvTypeOfEmpContractType->periodDuration){
            $resLine["periodDuration"] = $lvTypeOfEmpContractType->periodDuration.' '.i18n( ($lvTypeOfEmpContractType->periodDuration<=1 ? 'month':'months') );
        }else{
            $resLine["periodDuration"] = " - ";
        }
        $resLine["startDateEndDate"] = $theStartDate.' - '.$theEndDate;
        if($lvEarned->quantity){
            $resLine["quantity"] = $lvEarned->quantity;
            $resLine["taken"] = $lvEarned->quantity - $lvEarned->leftQuantity;
            $resLine["left"] = $lvEarned->leftQuantity;
        }else{
            $resLine["quantity"] = " - ";
            $resLine["taken"] = " - ";
            $resLine["left"] = " - ";
        }

        $right = $lvEarned->getLeavesRight(true,false);
        if ($right['quantity']) {
            if ($lvEarned->leftQuantity<0) {
                $theQuantity = max(0,$right["quantity"]+$lvEarned->leftQuantity);
            } else {
                $theQuantity = $right["quantity"];
            }

            $resLine["earnedPeriodPlusOne"] = $theQuantity;
        } else {
            $resLine["earnedPeriodPlusOne"] = " - ";
        }

        $res[]=$resLine;
    }
    echo json_encode($res);
}
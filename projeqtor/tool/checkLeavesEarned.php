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
 *  Check leaves earned for an employee or employee of manager or all employee if userId is Leave Administrator
 *  REQUEST['userId'] = The userId for which check leaves
 */
require_once "../tool/projeqtor.php";
require_once "../tool/projeqtor-hr.php";

$res="OK";
if(!isset($_REQUEST['userId'])){
    $res= "userId is not in REQUEST";
    echo json_encode($res);
    exit;
}

$userId = $_REQUEST['userId'];
// What kind of userid => Employee, Manager of Employee, Leave Administrator
$user = new Resource($userId);
$isManager = isManagerOfEmployee($userId);
$isLeaveAdm = isLeavesAdmin($userId);

// List of activ leave types
$lvTypesList = LeaveType::getList();

// Check for self, if is employee
if ($user->isEmployee) {
    $res = checkLeaveEarnedEnd($userId);
    if ($res=='OK') { 
        $res = checkValidity($userId);
        if ($res=='OK') { 
            $res = checkEarnedPeriod($userId);
        }
    }
}

if ($res!="OK") {
    echo json_encode($res);
    exit;    
}

$employees = null;
// Check for the employee of the manager if is manager
if ($isManager) {
    $manager = new EmployeeManagerMain($userId);
    $employees = $manager->getManagedEmployees();
    
} elseif ($isLeaveAdm) { // Leaves Administrator
    // Check for all employees
    $employee = new Employee();
    $crit = array("idle" => '0');
    $employeesList = $employee->getSqlElementsFromCriteria($crit);
    foreach($employeesList as $emp) {
        $employees[$emp->id] = $emp->name;
    }
}

if (!empty($employees)) {
    foreach ($employees as $key=>$emp) {
        // Don't do for itself. It's yet done
        if ($key == $userId) { continue;}
        $res = checkLeaveEarnedEnd($key);
        if ($res=='OK') { 
            $res = checkValidity($key);
            if ($res=='OK') { 
                $res = checkEarnedPeriod($key);
            }
        }
    }
}

$res = "OK";
echo json_encode($res);
exit;

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

// LEAVE SYSTEM

/* ============================================================================
 * Add Employees Manager in Bulk mode
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/addEmployeesManagedBulk.php');

if (!isset($_REQUEST['idEmployeeManager'])) {
  throwError('idEmployeeManager parameter not found in REQUEST');
}
$idEmployeeManager = $_REQUEST['idEmployeeManager'];

if (!isset($_REQUEST['itSelf'])) {
  throwError('itSelf parameter not found in REQUEST');
}
$itSelf = $_REQUEST['itSelf'];

if (!isset($_REQUEST['mode'])) {
  throwError('mode parameter not found in REQUEST');
}
$mode = $_REQUEST['mode'];

if (!isset($_REQUEST['startDate'])) {
  throwError('startDate parameter not found in REQUEST');
}
if ($_REQUEST['startDate']=="Invalid Date") {
    $startDate = null;
} else {
    $startDate = ( new DateTime( substr($_REQUEST['startDate'],0,15) ) ) -> format('Y-m-d');
}

if (!isset($_REQUEST['endDate'])) {
  throwError('endDate parameter not found in REQUEST');
}
if ($_REQUEST['endDate']=="Invalid Date") {
    $endDate = null;
} else {
    $endDate = ( new DateTime( substr($_REQUEST['endDate'],0,15) ) ) -> format('Y-m-d');
}

if (!isset($_REQUEST['idOrganization'])) {
  throwError('idOrganization parameter not found in REQUEST');
}
$idOrganization = $_REQUEST['idOrganization'];

if (!isset($_REQUEST['idTeam'])) {
  throwError('idTeam parameter not found in REQUEST');
}
$idTeam = $_REQUEST['idTeam'];

if ($itSelf=="YES") {
    $employeesManaged = new EmployeesManaged();
    $employeesManaged->idEmployee = $idEmployeeManager;
    $employeesManaged->idEmployeeManager = $idEmployeeManager;
    $employeesManaged->idle = 0;
    $employeesManaged->startDate = $startDate;
    $employeesManaged->endDate = $endDate;
    $employeesManaged->save();
}

switch ($mode) {
    case 'OS' :
        if ($idOrganization==null or $idOrganization<1) {
            return;
        }
        $organization = new Organization($idOrganization);
        $organizations = $organization->getRecursiveSubOrganizationsFlatList(true, true);
        foreach ($organizations as $id => $name) {
            $crit = array("idle" => "0",
                          "isEmployee" => "1",
                          "idOrganization" => $id
                         );
            $resource = new Resource();
            $resources = $resource->getSqlElementsFromCriteria($crit);
            foreach($resources as $res) {
                if ($res->id == $idEmployeeManager) {
                    continue;
                }
                $employeesManaged = new EmployeesManaged();
                $employeesManaged->idEmployee = $res->id;
                $employeesManaged->idEmployeeManager = $idEmployeeManager;
                $employeesManaged->idle = 0;
                $employeesManaged->startDate = $startDate;
                $employeesManaged->endDate = $endDate;
                $employeesManaged->save();            
            }                    
        }
        break;
    case 'O' :
        if ($idOrganization==null or $idOrganization<1) {
            return;
        }
        $crit = array("idle" => "0",
                      "isEmployee" => "1",
                      "idOrganization" => $idOrganization
                     );
        $resource = new Resource();
        $resources = $resource->getSqlElementsFromCriteria($crit);
        foreach($resources as $res) {
            if ($res->id == $idEmployeeManager) {
                continue;
            }
            $employeesManaged = new EmployeesManaged();
            $employeesManaged->idEmployee = $res->id;
            $employeesManaged->idEmployeeManager = $idEmployeeManager;
            $employeesManaged->idle = 0;
            $employeesManaged->startDate = $startDate;
            $employeesManaged->endDate = $endDate;
            $employeesManaged->save();            
        }        
        break;
    case 'T' :
        if ($idTeam==null or $idTeam<1) {
            return;
        }
        $crit = array("idle" => "0",
                      "isEmployee" => "1",
                      "idTeam" => $idTeam
                     );
        $resource = new Resource();
        $resources = $resource->getSqlElementsFromCriteria($crit);
        foreach($resources as $res) {
            if ($res->id == $idEmployeeManager) {
                continue;
            }
            $employeesManaged = new EmployeesManaged();
            $employeesManaged->idEmployee = $res->id;
            $employeesManaged->idEmployeeManager = $idEmployeeManager;
            $employeesManaged->idle = 0;
            $employeesManaged->startDate = $startDate;
            $employeesManaged->endDate = $endDate;
            $result = $employeesManaged->save();
        }
        break;
}

$employeeManager = new EmployeeManager($idEmployeeManager);
$result = $employeeManager->drawEmployeesManaged();
echo $result;

?>
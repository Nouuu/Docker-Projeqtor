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
 * Save a CustomEarnedRulesOfEmploymentContractType object from the form sent by dynamicDialogCustomEarnedRulesOfEmpContractType.php
 */
// LEAVE SYSTEM
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/saveEmployeesManaged.php');

if (! array_key_exists('idEmployeesManaged',$_REQUEST)) {
  throwError('idEmployeesManaged parameter not found in REQUEST');
}
$idEmployeesManaged = $_REQUEST['idEmployeesManaged'];

if (! array_key_exists('idEmployeeManagerEmployeesManaged',$_REQUEST)) {
  throwError('idEmployeeManagerEmployeesManaged parameter not found in REQUEST');
}
$idEmployeeManager = $_REQUEST['idEmployeeManagerEmployeesManaged'];

if (! array_key_exists('idEmployeeEmployeesManaged',$_REQUEST)) {
  throwError('idEmployeesEmployeesManaged parameter not found in REQUEST');
}
$idEmployee = $_REQUEST['idEmployeeEmployeesManaged'];

if (! array_key_exists('startDateEmployeesManaged',$_REQUEST)) {
  throwError('startDateEmployeesManaged parameter not found in REQUEST');
}
$startDate = $_REQUEST['startDateEmployeesManaged'];

if (! array_key_exists('endDateEmployeesManaged',$_REQUEST)) {
  throwError('endDateEmployeesManaged parameter not found in REQUEST');
}
$endDate = $_REQUEST['endDateEmployeesManaged'];
$idle=0;
if (array_key_exists('idleEmployeesManaged',$_REQUEST)) {
  $idle=1;
}

Sql::beginTransaction();
$employeesManaged = new EmployeesManaged();
$employeesManaged->id = $idEmployeesManaged;
$employeesManaged->idEmployee = $idEmployee;
$employeesManaged->idEmployeeManager = $idEmployeeManager;
$employeesManaged->startDate = $startDate;
$employeesManaged->endDate = $endDate;
$employeesManaged->idle = $idle;

$result = $employeesManaged->save();
displayLastOperationStatus($result);
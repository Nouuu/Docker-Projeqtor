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
// ELIOTT - LEAVE SYSTEM
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/saveCustomEarnedRulesOfEmpContractType.php');

if (! array_key_exists('idEmploymentContractType',$_REQUEST)) {
  throwError('idEmploymentContractType parameter not found in REQUEST');
}
$idEmploymentContractType = $_REQUEST['idEmploymentContractType'];

if (! array_key_exists('idCustomEarnedRuleOfEmpContractType',$_REQUEST)) {
  throwError('idCustomEarnedRuleOfEmpContractType parameter not found in REQUEST');
}
$idCustomEarnedRuleOfEmpContractType = $_REQUEST['idCustomEarnedRuleOfEmpContractType'];

if (! array_key_exists('ruleName',$_REQUEST)) {
  throwError('ruleName parameter not found in REQUEST');
}
$name = $_REQUEST['ruleName'];

if (! array_key_exists('ruleCustomEarnedRule',$_REQUEST)) {
  throwError('ruleCustomEarnedRule parameter not found in REQUEST');
}
$rule = $_REQUEST['ruleCustomEarnedRule'];

if (! array_key_exists('whereClauseCustomEarnedRule',$_REQUEST)) {
  throwError('whereClauseCustomEarnedRule parameter not found in REQUEST');
}
$whereClause = $_REQUEST['whereClauseCustomEarnedRule'];

if (! array_key_exists('ruleQuantity',$_REQUEST)) {
  throwError('ruleQuantity parameter not found in REQUEST');
}
$quantity = $_REQUEST['ruleQuantity'];

if (! array_key_exists('ruleIdLeaveType',$_REQUEST)) {
  throwError('ruleIdLeaveType parameter not found in REQUEST');
}
$idLeaveType = $_REQUEST['ruleIdLeaveType'];

Sql::beginTransaction();
$customEarnedRule = new CustomEarnedRulesOfEmploymentContractType();
$customEarnedRule->id = $idCustomEarnedRuleOfEmpContractType;
$customEarnedRule->idEmploymentContractType = $idEmploymentContractType;
$customEarnedRule->name = $name;
$customEarnedRule->rule = $rule;
$customEarnedRule->whereClause = $whereClause;
$customEarnedRule->quantity = $quantity;
$customEarnedRule->idLeaveType = $idLeaveType;

$result = $customEarnedRule->save();
displayLastOperationStatus($result);
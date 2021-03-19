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

/** ===========================================================================
 * Move task (from before to)
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/moveTask.php');
if (! array_key_exists('from',$_REQUEST)) {
  throwError('from parameter not found in REQUEST');
}
$from=$_REQUEST['from'];

$arrayFrom=explode(',', $from);

if (! array_key_exists('to',$_REQUEST)) {
  throwError('to parameter not found in REQUEST');
}
$to=$_REQUEST['to'];

if (! array_key_exists('mode',$_REQUEST)) {
  throwError('mode parameter not found in REQUEST');
}
$mode=$_REQUEST['mode'];
if ($mode!='before' and $mode!='after') {
  $mode='before';
}

if ($mode=='after') {
  $arrayFrom=array_reverse($arrayFrom);
}
  
PlanningElement::$_noDispatch=true;
$needRefreshInserted=false;
$doNotTriggerAlerts=true;
Sql::beginTransaction();
foreach ($arrayFrom as $from) {
  projeqtor_set_time_limit(300);
  $idFrom=substr($from, 6); // validated to be numeric value in SqlElement base constructor
  $idTo=substr($to, 6); // validated to be numeric value in SqlElement base constructor
  if (! is_numeric($idTo) or $idTo>PlanningElementExtension::$_startId) {
    $returnValue=i18n('moveCancelledNotPlanned');
    $returnValue .= '<input type="hidden" id="lastOperation" value="move" />';
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
    $returnValue .= '<input type="hidden" id="lastPlanStatus" value="OK" />';
    $result=$returnValue;
  } else {
    $task=GlobalPlanningElement::getTaskFromPlanningId($idFrom);
    $result=$task->moveTo($idTo,$mode);
  }
  if (getLastOperationStatus($result)!='OK') break; // Stop loop of moves
  if ($task->refType=='Project' and ! $needRefreshInserted) {
    echo '<input type="hidden" id="needProjectListRefresh" value="true" />';
    $needRefreshInserted=true;
  }
}
if (isset($result) and getLastOperationStatus($result)=='OK') PlanningElement::moveTaskFinalize();
displayLastOperationStatus($result);
?>
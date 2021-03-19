<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Salto Consulting - 2019 
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

//return the statuses of the workflow associated to the type id of leave
if(!isset($_REQUEST['idType']) ){
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." ".i18n("missing")." : idType", 
                                  false,
                                  "", 
                                  "GET WORKFLOW STATUSES OF TYPE",
                                  "INVALID");

    echo json_encode($result);
    exit;
}
$idType = $_REQUEST['idType'];

if(!isset($_REQUEST['idEmployee']) ){
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." ".i18n("missing")." : idEmployee", 
                                  false,
                                  "", 
                                  "GET WORKFLOW STATUSES OF TYPE",
                                  "INVALID");

    echo json_encode($result);
    exit;
}
$idEmployee = $_REQUEST['idEmployee'];

if(!isset($_REQUEST['from']) ){
    $from = "";
} else {
    $from = $_REQUEST['from'];
}

$user = getSessionUser();
$isAll=false;
if (isLeavesAdmin($user->id) or isManagerOfEmployee($user->id, $idEmployee) or $from=="fromLeaveCalendar") {
    $isAll=true;
}

$theLeaveType = new LeaveType($idType);
$theWorkflow = new Workflow($theLeaveType->idWorkflow);
$lstStatus = $theWorkflow->getWorkflowstatus();
$theStatusList = array();
foreach($lstStatus as $status) {
    if (!array_key_exists($status->idStatusFrom, $theStatusList)) {
        $theStatus = new Status($status->idStatusFrom);
        if (($isAll===false and $theStatus->setRejectedLeave==0 and $theStatus->setAcceptedLeave==0) or $isAll===true) {
            $theStatusList[$status->idStatusFrom] = $theStatus;
        }
    }
    if (!array_key_exists($status->idStatusTo, $theStatusList)) {
        $theStatus = new Status($status->idStatusTo);
        if (($isAll===false and $theStatus->setRejectedLeave==0 and $theStatus->setAcceptedLeave==0) or $isAll===true) {
            $theStatusList[$status->idStatusTo] = $theStatus;
        }
    }
}

usort($theStatusList, function($a, $b)
    {
        return strcmp($a->sortOrder, $b->sortOrder);
    }
);

$statusFromToList[$idType] = $theWorkflow->getListStatusFromTo();

$theResult = array(
  "status" => $theStatusList,
  "FromTo" => $statusFromToList
);

echo json_encode($theResult);

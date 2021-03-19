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
 * 
 */
require_once "../tool/projeqtor.php";

//return all the leaves of the current user between startDate and enDate passed in REQUEST
if(!isset($_REQUEST['startDate']) and !isset($_REQUEST['endDate'])){//error missing startDate and endDate
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." ".i18n("missing")." : startDate AND endDate", 
                                  false,
                                  "", 
                                  "GET LEAVES",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

if(!isset($_REQUEST['startDate'])){//error missing startDate
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." ".i18n("missing")." : startDate", 
                                  false,
                                  "", 
                                  "GET LEAVES",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

if(!isset($_REQUEST['endDate'])){//missing endDate
    $result= htmlSetResultMessage(null, 
                                  i18n("errorWrongRequest")." ".i18n("missing")." : endDate", 
                                  false,
                                  "", 
                                  "GET LEAVES",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

$idRes = null;
if(isset($_REQUEST['idRes'])){
    $idRes = $_REQUEST['idRes'];
} else {
    $idRes = getSessionUser()->id;
}

$lvList=getUserLeaves($_REQUEST['startDate'],$_REQUEST['endDate'], $idRes);

if($lvList==-1){//error in getUserLeaves()
    $result= htmlSetResultMessage(null, 
                                  i18n("EmployeePassedInRequestIsNotAnEmployee"), 
                                  false,
                                  "", 
                                  "GET LEAVES",
                                  "INVALID");

    echo json_encode($result);
    exit;
}

$emp = new Employee($idRes);
if ($emp->id<0 or $emp->idle==1) { // idRes is'nt activ employee
    $result= htmlSetResultMessage(null, 
                                  i18n("EmployeePassedInRequestIsNotAnEmployee"), 
                                  false,
                                  "", 
                                  "GET LEAVES",
                                  "INVALID");

    echo json_encode($result);
    exit;    
}

$user = getSessionUser();

// The list of the leave types
// filter for each Ressource based on the Contract Type
$employmentContract = new EmploymentContract();
$employment = SqlElement::getSingleSqlElementFromCriteria('EmploymentContract',array('idEmployee'=>$idRes,'idle'=>'0'));
$idEmploymentContractType = $employment->idEmploymentContractType;

$lvECT = new LeaveTypeOfEmploymentContractType();
$leaveTypeOfEmploymentContractType = $lvECT->getSqlElementsFromCriteria(array("idEmploymentContractType"=> $idEmploymentContractType));

$resLvTypes = [];
// The left for each leave type of the employee
$idLeaveT = [];
$leftList = $emp->getLeftLeavesByLeaveType();

$lvType=new LeaveType();
$defaultLvType = $lvType->getSqlElementsFromCriteria(array());
foreach($leaveTypeOfEmploymentContractType as $lt) {
    //$lvType = $lvType->getSqlElementsFromCriteria(array('id'=> $lt->idLeaveType))[0];
    $lvType =new LeaveType($lt->idLeaveType);
    array_push($resLvTypes, $lvType);
    array_push($idLeaveT, $lt->idLeaveType);
}

foreach ($leftList as $key=>$value) {
    if (!in_array($key, $idLeaveT)) {
        unset($leftList[$key]);
    }
}


// For each leave type, store the associated workflow
$WfList[0] = 0;
foreach($resLvTypes as $lvType) {
    if (!array_key_exists($lvType->idWorkflow,$WfList)) {
      $WfList[$lvType->idWorkflow] = $lvType->id;  
    } 
}
unset($WfList[0]);

$statusFromToListByWorkflow= array();
$theStatusList = array();

// For each associated workflow to leave types
foreach($WfList as $key=>$idLvTp) {
    $theWorkflow = new Workflow($key);
    
    // List status FromTo status of the workflow
    $statusFromToList = $theWorkflow->getListStatusFromTo();
    $statusFromToListByWorkflow[$idLvTp] = $statusFromToList;
        
    // List the status of the workflow
    $lstStatus = $theWorkflow->getWorkflowstatus();
    foreach($lstStatus as $status) {
        if (!array_key_exists($status->idStatusFrom, $theStatusList)) {
            $theStatus = new Status($status->idStatusFrom);
            $theStatusList[$status->idStatusFrom] = $theStatus;
        }
        if (!array_key_exists($status->idStatusTo, $theStatusList)) {
            $theStatus = new Status($status->idStatusTo);
            $theStatusList[$status->idStatusTo] = $theStatus;
        }
    }    
}
$statusList = $theStatusList;

$resStatus=null;
foreach($statusList as $status) {
    if ($resStatus==null) {
        $resStatus[0] = $status;
    } else {
        array_push($resStatus, $status);
    }
} 
if ($resStatus and count($resStatus)>0) {
  usort($resStatus, function($a, $b)
      {
          return strcmp($a->sortOrder, $b->sortOrder);
      }
  );
}

if(! $lvList){
    $res=array(
        "leaves"=>"empty",
        "leaveTypes"=>$resLvTypes,
        "status"=>$resStatus,
        "statusFromTo"=>$statusFromToListByWorkflow,
        "left"=>$leftList,
        "default"=>$defaultLvType
    );
    echo json_encode($res);
    exit;
}

$res=array(
    "leaves"=>$lvList,
    "leaveTypes"=>$resLvTypes,
    "status"=>$resStatus,
    "statusFromTo"=>$statusFromToListByWorkflow,
    "left"=>$leftList,
    "default"=>$defaultLvType
);
echo json_encode($res);

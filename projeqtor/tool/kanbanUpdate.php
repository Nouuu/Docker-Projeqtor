<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
******************************************************************************
*** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
******************************************************************************
*
* Copyright 2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
*
* This file is an add-on to ProjeQtOr, packaged as a plug-in module.
* It is NOT distributed under an open source license.
* It is distributed in a proprietary mode, only to the customer who bought
* corresponding licence.
* The company ProjeQtOr remains owner of all add-ons it delivers.
* Any change to an add-ons without the explicit agreement of the company
* ProjeQtOr is prohibited.
* The diffusion (or any kind if distribution) of an add-on is prohibited.
* Violators will be prosecuted.
*
*** DO NOT REMOVE THIS NOTICE ************************************************/

/* ============================================================================
 * Habilitation defines right to the application for a menu and a profile.
 */ 
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
require_once "../tool//kanbanConstructPrinc.php";
if (! array_key_exists('idKanban',$_REQUEST)) {
  throwError('Parameter idKanban not found in REQUEST');
}
$Kanban=new Kanban($_REQUEST['idKanban']);
$json=json_decode($Kanban->param,true);
$typeKanbanType=$json['typeData'];
if (! array_key_exists('idTicket',$_REQUEST)) {
  throwError('Parameter idTicket not found in REQUEST');
}
$idTicket=$_REQUEST['idTicket'];

$idTargetId=-1;
if(array_key_exists('targetId', $_REQUEST)){
  $idTargetId=$_REQUEST['targetId'];
}

if (! array_key_exists('type',$_REQUEST)) {
  throwError('Parameter type not found in REQUEST');
}
$type=$_REQUEST['type'];

if (! array_key_exists('newStatut',$_REQUEST)) {
  throwError('Parameter newStatut not found in REQUEST');
}
$reponse="";
$newStatut=$_REQUEST['newStatut'];
$ticket=new $typeKanbanType($idTicket);

$hasVersion=(property_exists($typeKanbanType,'idTargetProductVersion'))?true:false;

if (array_key_exists('kanbanResourceList',$_REQUEST)) {
  $ticket->idResource=$_REQUEST['kanbanResourceList'];
}
if (array_key_exists('kanbanResult',$_REQUEST)) {
  $ticket->result=$_REQUEST['kanbanResult'];
}
if (array_key_exists('kanbanResolutionList',$_REQUEST)) {
  $ticket->idResolution=$_REQUEST['kanbanResolutionList'];
}
$needIdKanban='';
if(array_key_exists('needIdKanban',$_REQUEST)){
  $needIdKanban=$_REQUEST['needIdKanban'];
}
/*if(securityGetAccessRightYesNo("menuTicket", "update", $ticket)=="NO"){
  echo 'No access';
  exit();
}*/

if($type=="Status"){
	$nameVar='id'.$typeKanbanType.'Type';
	$nameVar2=$typeKanbanType.'Type';
	$ticketType=new $nameVar2($ticket->$nameVar);
	// PBE : status is not always the first of column, but can be one avalable in the column
	$statusList=SqlList::getList('Status');
	$targetStatus=array();
	$json['column']=array_merge($json['column'],array()); // will "fill holes" in keys : 0, 1, 3, 5 will become 0, 1, 2, 3 
	for ($i=0;$i<count($json['column']);$i++) {
	  $itemKanban=$json['column'][$i];
	  $idFrom=$itemKanban['from'];
	  if ($idFrom==$newStatut) {
  	  $targetStatus[$idFrom]=$idFrom;
  	  $found=false;
  	  foreach ($statusList as $idS=>$nameS) {
  	    if ($found) {
  	      if ($i<count($json['column'])-1 and $idS==$json['column'][$i+1]['from']) {
  	        break;
  	      } else {
  	        $targetStatus[$idS]=$idS;
  	      }
  	    } else if ($idS==$idFrom) {
  	      $found=true;
  	    }
  	  }
	  }
	}
	$workflowId=$ticketType->idWorkflow;
	$wf=new Workflow($workflowId);
	$mapWorkflow=$wf->getWorkflowstatusArray();
	$user=getSessionUser();
	$prof=$user->getProfile($ticket);
	foreach ($targetStatus as $testStatus) {
	  if (isset($mapWorkflow[$ticket->idStatus][$testStatus]) 
	  and isset($mapWorkflow[$ticket->idStatus][$testStatus][$prof]) 
	  and $mapWorkflow[$ticket->idStatus][$testStatus][$prof]==1) {
	    $newStatut=$testStatus;
	    break;
	  }
	}
	// PBE - End
	$status=new Status($newStatut);
  if($ticketType->mandatoryResourceOnHandled && $status->setHandledStatus && !$ticket->idResource){
    $reponse.="&needRessource=true";
  }
  if($ticketType->mandatoryResultOnDone && $status->setDoneStatus && !$ticket->result){
    $reponse.="&needResult=true";
  }
  if($ticketType->mandatoryResolutionOnDone && $status->setDoneStatus && !$ticket->idResolution){
    $reponse.="&needResolution=true";
  }
}
if($reponse==""){
  $newV='id'.$type;
  if($newStatut!='n'){
    $ticket->$newV=$newStatut;
  }else{
    $ticket->$newV=null;
  }
  $result=$ticket->save();
  $resultOk=getLastOperationStatus($result);
  if($resultOk=="OK"){
    $line=array();
    $line['id']=$ticket->id;
    $line['idstatus']=$ticket->idStatus;
    $line['idtargetproductversion']=($hasVersion)?$ticket->idTargetProductVersion:null;
    if(property_exists($ticket,'idActivity')){
     $line['idactivity']=$ticket->idActivity;
    }
    echo $idTicket.'-'.$type.'-'.$newStatut.'[splitcustom]'.kanbanAddPrinc($line).'[splitcustom2]'.formatUserThumb($ticket->idResource, SqlList::getNameFromId("Resource", $ticket->idResource), "", 22, 'left', false, $ticket->idResource);
  }else{
    echo 'messageError/split/'.getLastOperationMessage($result);
  }
}else{
  echo $reponse."&idTicket=".$idTicket."&idStatus=".$newStatut;
}
?>
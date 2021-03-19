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

/** ============================================================================
 * Save real work allocation.
 */

require_once "../tool/projeqtor.php";
$status="NO_CHANGE";
$errors="";
$finalResult="";

if (!isset($_REQUEST['nbLines'])) {
  traceLog('WARNING - Left work not retrieved from screen');
  traceLog('        - Maybe max_input_vars is too small in php.ini (actual value is '.ini_get('max_input_vars').')');
  trigger_error('Error - Maybe max_input_vars is too small in php.ini',E_USER_ERROR);
  exit;
}
$rangeType=$_REQUEST['rangeType'];
$rangeValue=$_REQUEST['rangeValue'];
$userId=$_REQUEST['userId'];
$nbLines=$_REQUEST['nbLines'];
if ($rangeType=='week') {
  $nbDays=7;
}
// Save main comment
if (isset($_REQUEST['imputationComment'])) {
	$comment=$_REQUEST['imputationComment'];
	$period=new WorkPeriod();
  $crit=array('idResource'=>$userId, 'periodRange'=>$rangeType,'periodValue'=>$rangeValue);
  $period=SqlElement::getSingleSqlElementFromCriteria('WorkPeriod', $crit);
  $period->comment=$comment;
  $result=$period->save();
  if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
    $status='ERROR';
    $finalResult=$result;
  } else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
    $status='OK';
  } else { 
    if ($finalResult=="") {
      $finalResult=$result;
    }
  }
}
Sql::beginTransaction();
for ($i=0; $i<$nbLines; $i++) {
	$imputable=$_REQUEST['imputable'][$i];
  $locked=$_REQUEST['locked'][$i];
  $changed=false;
  if ($imputable and ! $locked) {
    $line=new ImputationLine();
    $line->idAssignment=$_REQUEST['idAssignment'][$i];
    $ass=new Assignment($line->idAssignment);
    $line->refType=$ass->refType;
    $line->refId=$ass->refId;
    $line->idResource=$userId;
    if (isset($_REQUEST['leftWork'][$i])) {
      $line->leftWork=Work::convertImputation($_REQUEST['leftWork'][$i]);
    } else {
    	traceLog('WARNING - Left work not retrieved from screen');
    	traceLog('        - Maybe max_input_vars is too small in php.ini (actual value is '.ini_get('max_input_vars').')');
    	traceLog('        - Assignment #'.$ass->id.' on '.$ass->refType.' #'.$ass->refId.' for resource #'.$ass->idResource. ' - '.SqlList::getNameFromId('Resource',$ass->idResource));
    	trigger_error('Error - Maybe max_input_vars is too small in php.ini',E_USER_ERROR);
    }
    $line->imputable=$imputable;
    $arrayWork=array();
    for ($j=1; $j<=$nbDays; $j++) {
    	$workId=null;
    	if (array_key_exists('workId_' . $j, $_REQUEST)) {
        $workId=$_REQUEST['workId_' . $j][$i];
    	}
      $workValue=Work::convertImputation($_REQUEST['workValue_'.$j][$i]);
      $workDate=$_REQUEST['day_' . $j];
      if ($workId and $workId!='x') {
        $work=new Work($workId);
      } else {
        $crit=array('idAssignment'=>$line->idAssignment,
                    'workDate'=>$workDate, 'idWorkElement'=>null , 'idResource'=>$line->idResource);
        $work=SqlElement::getSingleSqlElementFromCriteria('Work', $crit);
      } 
      if ($workId=='x') {
        $crit=array('idAssignment'=>$line->idAssignment,
            'workDate'=>$workDate, 'idResource'=>$line->idResource);
        $plannedWork=SqlElement::getSingleSqlElementFromCriteria('PlannedWork', $crit);
        if ($plannedWork and $plannedWork->id) {
          $resPlan=$plannedWork->delete();
          $statPlan=getLastOperationStatus($resPlan);
          if ($statPlan=='OK') $changed=true;
        }
      }
      $arrayWork[$j]=$work;
      $arrayWork[$j]->work=$workValue;
      $arrayWork[$j]->idResource=$userId;
      $arrayWork[$j]->idProject=$ass->idProject;
      $arrayWork[$j]->refType=$line->refType;
      $arrayWork[$j]->refId=$line->refId;
      $arrayWork[$j]->idAssignment=$line->idAssignment;     
      $arrayWork[$j]->setDates($workDate);
    }
    $line->arrayWork=$arrayWork;
    $result=$line->save();
    $stat=($result)?getLastOperationStatus($result):'NO_CHANGE';
    if ($stat=="ERROR" or $stat=="INVALID") {
      $status='ERROR';
      $finalResult=$result;
      break;
    } else if ($stat=="OK") {
      $stat='OK';
      $changed=true;
    } else { 
      if ($finalResult=="") {
        $finalResult=$result;
      }
    }
    $pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', array("refType"=>$ass->refType,"refId"=>$ass->refId));
    if($pe->idPlanningMode=="23"){
      $crit=array('refType'=>$line->refType, 'refId'=>$line->refId, 'idResource'=>$line->idResource);
      $pw= new PlannedWork();
      $w=new Work();
      $realwork=$w->sumSqlElementsFromCriteria('work', $crit);
      $plannedwork=$pw->sumSqlElementsFromCriteria('work', $crit);
      $ass->assignedWork=$plannedwork+$realwork;
    }
    if ($ass->leftWork!=$line->leftWork) {
    	$changed=true;
    	$ass->leftWork=$line->leftWork;
    }
    if ($changed) {
       $resultAss=$ass->saveWithRefresh();
       $statAss=getLastOperationStatus($resultAss);
       if ($statAss=="OK" or $statAss=='NO_CHANGE') { // NO_CHANGE means work was changed, but not assignment (Ex : -1 day 1, +1 day 2)
       	$status='OK';
       } else if ($statAss=="ERROR"){
       	$status='ERROR';
       	$finalResult=$resultAss;
       	break;
       }
    }
  }
}

if ($status=='ERROR') {
	Sql::rollbackTransaction();
  echo '<div class="messageERROR" >' . $finalResult . '</div>';
} else if ($status=='OK'){ 
	Sql::commitTransaction();
  echo '<div class="messageOK" >' . i18n('messageImputationSaved') . '</div>';
  checkSendAlert($userId,$rangeValue);
} else {
	Sql::rollbackTransaction();
  echo '<div class="messageNO_CHANGE" >' . i18n('messageNoImputationChange') . '</div>';
}
echo '<input type="hidden" id="lastOperation" name="lastOperation" value="save">';
echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="' . $status .'">';

function checkSendAlert($userId,$periodValue) {
  $currentUser=getSessionUser();
  if ($userId==$currentUser->id) return;
  $param=Parameter::getGlobalParameter('imputationAlertInputByOther');
  if (!trim($param) or $param=='NO') return;
  $name=($currentUser->resourceName)?$currentUser->resourceName:$currentUser->name;
  $name='"'.$name.'"';
  $periodValue=substr($periodValue,0,4).'-'.substr($periodValue,4);
  $alertSendTitle=i18n('messageAlertImputationByOtherTitle');
  $alertSendMessage=i18n('messageAlertImputationByOtherBody',array($name,$periodValue));
  if ($param=='ALERT' or $param=='ALERT&MAIL') {
    $alertSendType='WARNING';
    $alert=new Alert();
    $alert->idUser=$userId;
    $alert->alertType=$alertSendType;
    $alert->alertInitialDateTime=date('Y-m-d H:i:s');
    $alert->alertDateTime=date('Y-m-d H:i:s');
    $alert->title=mb_substr($alertSendTitle,0,100);
    $alert->message=$alertSendMessage;
    $result=$alert->save();
  }
  if ($param=='MAIL' or $param=='ALERT&MAIL') {
    $to=SqlList::getFieldFromId('User', $userId, 'email');
    if (trim($to)) {
      $result=sendMail($to, '['.Parameter::getGlobalParameter('paramDbDisplayName').'] '.$alertSendTitle, $alertSendMessage);
    }
  }
}
?>
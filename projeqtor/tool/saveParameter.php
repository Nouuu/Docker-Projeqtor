<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : 
 *  => g.miraillet : Fix #1502
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
 * Save some information to session (remotely).
 */
require_once "../tool/projeqtor.php";

// TODO (SECURITY) : enforce security (habilitation to change parameters, lock fixed params, ...)
$status="NO_CHANGE";
$errors="";
$type=$_REQUEST['parameterType'];
Security::checkDisplayMenuForUser(ucfirst($type));
Sql::beginTransaction();
$forceRefreshMenu='';
// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
$changeNotificationSystemActiv=false;
// END - ADD BY TABARY - NOTIFICATION SYSTEM
// MTY - LEAVE SYSTEM
$changeLeavesSystemActiv=false;
// MTY - LEAVE SYSTEM
if ($type=='habilitation') {
  $crosTable=htmlGetCrossTable('menu', 'profile', 'habilitation') ;
  $hab=new Habilitation();
  $allHab=$hab->getSqlElementsFromCriteria(array());
  foreach ($allHab as $hab) {
    $allHab[$hab->idMenu.'#'.$hab->idProfile]=$hab;
    unset($allHab[$hab->id]);
  }
  $forceRefreshMenu=false;
  foreach($crosTable as $lineId => $line) {
    foreach($line as $colId => $val) {
      //$crit['idMenu']=$lineId;
      //$crit['idProfile']=$colId;
      //$obj=SqlElement::getSingleSqlElementFromCriteria('Habilitation', $crit);
      $key=$lineId.'#'.$colId;
      if (isset($allHab[$key])) {
        $obj=$allHab[$key];
      } else {
        $obj=new Habilitation();
        $obj->idMenu=$lineId;
        $obj->idProfile=$colId;
      }
      $newVal=($val)?1:0;
      if ($obj->allowAccess!=$newVal) {
        $obj->allowAccess=$newVal;
        $result=$obj->save();
        $isSaveOK=strpos($result, 'id="lastOperationStatus" value="OK"');
        $isSaveNO_CHANGE=strpos($result, 'id="lastOperationStatus" value="NO_CHANGE"');
        if ($isSaveNO_CHANGE===false) {
          if ($isSaveOK===false) {
            $status="ERROR";
            $errors=$result;
          } else if ($status=="NO_CHANGE") {
            $status="OK";
            if ($obj->idProfile==getSessionUser()->idProfile) {
              $forceRefreshMenu='habilitation';
            }
          }
        }
      }
    }
    resetUser();
  }
  Habilitation::correctUpdates(); // Call correct updates 3 times, to assure all level updates
  Habilitation::correctUpdates();
  Habilitation::correctUpdates();
} else if ($type=='habilitationReport') {
  $crosTable=htmlGetCrossTable('report', 'profile', 'habilitationReport') ;
  foreach($crosTable as $lineId => $line) {
    foreach($line as $colId => $val) {
      $crit['idReport']=$lineId;
      $crit['idProfile']=$colId;
      $obj=SqlElement::getSingleSqlElementFromCriteria('HabilitationReport', $crit);
      $obj->allowAccess=($val)?1:0;
      $result=$obj->save();
      $isSaveOK=strpos($result, 'id="lastOperationStatus" value="OK"');
      $isSaveNO_CHANGE=strpos($result, 'id="lastOperationStatus" value="NO_CHANGE"');
      if ($isSaveNO_CHANGE===false) {
        if ($isSaveOK===false) {
          $status="ERROR";
          $errors=$result;
        } else if ($status=="NO_CHANGE") {
          $status="OK";
          //if ($obj->idProfile==getSessionUser()->idProfile) {
          //  $forceRefreshMenu='habilitationReport';
          //}
        }
      }
    }
  }
} else if ($type=='habilitationOther') {
  $crosTable=htmlGetCrossTable(array(
                                 'imputation'=>i18n('imputationAccess'),
                                     'workValid'=>i18n('workValidate'),
  		                               'diary'=>i18n('diaryAccess'),
  		                               'subscription'=>i18n('canSubscribeForOthers'),
                                     'scheduledReport'=>i18n('autoSendReportAccess'),
                                     //'expense'=>i18n('resourceExpenseAccess'),
                                 'work'=>i18n('workAccess'),
                                     'cost'=>i18n('costAccess'),
  		                           'assignmentView'=>i18n('assignmentViewRight'),
  		                               'assignmentEdit'=>i18n('assignmentEditRight'),
                                 'combo'=>i18n('comboDetailAccess'),
  		                               'checklist'=>i18n('checklistAccess'),
									                   'joblist'=>i18n('joblistAccess'),
                                     'multipleUpdate'=>i18n('buttonMultiUpdate'), 
                                    'lockedImputation'=>i18n('buttonLockedImputation'),
                                    'validationImputation'=>i18n('buttonValidationImputation'),
                                 'planning'=>i18n('planningRight'),
                                     'planningWithOveruse'=>i18n('canPlanWithInfiniteCapacity'),
  									                 'resourcePlanning'=>i18n('resourcePlanningRight'),
                                     'changeValidatedData'=>i18n('changeValidatedData'),
                                     'changePriorityProj'=>i18n('changePriorityProject'),
                                     'changePriorityOther'=>i18n('changePriorityOther'),
                                     'changeManualProgress'=>i18n('changeManualProgress'),
                                     'validatePlanning'=>i18n('validatePlanning'),
                                 'document'=>i18n('documentUnlockRight'),
                                     'requirement'=>i18n('requirementUnlockRight'),
                                 'reportResourceAll'=>i18n('reportResourceAll'),
                                 'canChangeNote'=>i18n('canChangeNote'),
                                     'canDeleteAttachment'=>i18n('canDeleteAttachment'),     
                                     'canForceDelete'=>i18n('canForceDelete'),
                                     'canDeleteRealWork'=>i18n('canDeleteRealWork'),
                                     'canForceClose'=>i18n('canForceClose'),
                                     'canUpdateCreation'=>i18n('canUpdateCreationInfo'), 
                                     'viewComponents'=>i18n('viewComponents'),
                                     'generateProjExpense'=>i18n('generateProjectExpenseButton'),
                                     'situation'=>i18n('situationRight'),
                                 'resVisibilityList'=>i18n('resourceVisibilityList'),
                                     'resVisibilityScreen'=>i18n('resourceVisibilityScreen'),
                                 'orgaVisibilityList'=>i18n('organizationVisibilityList'),
                                     'orgaVisibilityScreen'=>i18n('organizationVisibilityScreen')      
                                ),                               
                               'profile', 
                               'habilitationOther') ;
  foreach($crosTable as $lineId => $line) {
    foreach($line as $colId => $val) {
      $crit['scope']=$lineId;
      $crit['idProfile']=$colId;
      $obj=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
      $obj->rightAccess=($val)?$val:0;
      $result=$obj->save();
      $isSaveOK=strpos($result, 'id="lastOperationStatus" value="OK"');
      $isSaveNO_CHANGE=strpos($result, 'id="lastOperationStatus" value="NO_CHANGE"');
      if ($isSaveNO_CHANGE===false) {
        if ($isSaveOK===false) {
          $status="ERROR";
          $errors=$result;
        } else if ($status=="NO_CHANGE") {
          $status="OK";
          //if ($obj->idProfile==getSessionUser()->idProfile) {
          //  $forceRefreshMenu='habilitationOther';
          //}
        }
      }
    }
    $crit=array('idProfile'=>$user->idProfile, 'scope'=>'changeValidatedData');
    $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
    $act=new ActivityMain();
    if ($habil and $habil->id and $habil->rightAccess=='1') {
      //$canChangeResource=true;
    }
    
  }
} else if ($type=='accessRight') {
  $crosTable=htmlGetCrossTable('menuProject', 'profile', 'accessRight') ;
  foreach($crosTable as $lineId => $line) {
    foreach($line as $colId => $val) {
      $crit['idMenu']=$lineId;
      $crit['idProfile']=$colId;
      $obj=SqlElement::getSingleSqlElementFromCriteria('AccessRight', $crit);
      $obj->idAccessProfile=$val;
      $result=$obj->save();
      $isSaveOK=strpos($result, 'id="lastOperationStatus" value="OK"');
      $isSaveNO_CHANGE=strpos($result, 'id="lastOperationStatus" value="NO_CHANGE"');
      if ($isSaveNO_CHANGE===false) {
        if ($isSaveOK===false) {
          $status="ERROR";
          $errors=$result;
        } else if ($status=="NO_CHANGE") {
          $status="OK";
          //if ($obj->idProfile==getSessionUser()->idProfile) {
          //  $forceRefreshMenu='accessRight';
          //}
        }
      }
    }
    resetUser();
  }
} else if ($type=='accessRightNoProject') {
  $tableCrossRef=array('menuReadWritePrincipal','menuReadWriteConfiguration','menuReadWriteTool','menuReadWriteEnvironment','menuReadWriteAutomation','menuReadWriteList','menuReadWriteType');
  foreach ($tableCrossRef as $crossRef) {
    $crosTable=htmlGetCrossTable($crossRef, 'profile', 'accessRight') ;
    foreach($crosTable as $lineId => $line) {
      foreach($line as $colId => $val) {
        $crit['idMenu']=$lineId;
        $crit['idProfile']=$colId;
        $obj=SqlElement::getSingleSqlElementFromCriteria('AccessRight', $crit);
        $obj->idAccessProfile=$val;
        $result=$obj->save();
        $isSaveOK=strpos($result, 'id="lastOperationStatus" value="OK"');
        $isSaveNO_CHANGE=strpos($result, 'id="lastOperationStatus" value="NO_CHANGE"');
        if ($isSaveNO_CHANGE===false) {
          if ($isSaveOK===false) {
            $status="ERROR";
            $errors=$result;
          } else if ($status=="NO_CHANGE") {
            $status="OK";
            //if ($obj->idProfile==getSessionUser()->idProfile) {
            //  $forceRefreshMenu='accessRightNoProject';
            //}
          }
        }
      }
    }
    resetUser();
  }
} else if ($type=='userParameter') {
  $parameterList=Parameter::getParamtersList($type);
  foreach($_REQUEST as $fld => $val) {
    if (array_key_exists($fld, $parameterList)) {
      $user=getSessionUser();
      $crit['idUser']=$user->id;
      $crit['idProject']=null;
      $crit['parameterCode']=$fld;
      $obj=SqlElement::getSingleSqlElementFromCriteria('Parameter', $crit);
      $obj->parameterValue=$val;
      $result=$obj->save();
      $isSaveOK=strpos($result, 'id="lastOperationStatus" value="OK"');
      $isSaveNO_CHANGE=strpos($result, 'id="lastOperationStatus" value="NO_CHANGE"');
      if ($isSaveNO_CHANGE===false) {
        if ($isSaveOK===false) {
          $status="ERROR";
          $errors=$result;
        } else if ($status=="NO_CHANGE") {
          $status="OK";
        }
      }
    }
  }
} else if ($type=='globalParameter') {
  $parameterList=Parameter::getParamtersList($type);
  $changeImputationAlerts=false;
// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
  $changeNotificationSystemActiv = false;
// END - ADD BY TABARY - NOTIFICATION SYSTEM  
// MTY - LEAVE SYSTEM
  $changeLeavesSystemActiv = false;  
// MTY - LEAVE SYSTEM  
  $samlEnable=(strtolower(RequestHandler::getValue('SAML_allow_login'))=='true')?true:false;
  if ($samlEnable and (   ! RequestHandler::getValue('SAML_idpCert') or ! RequestHandler::getValue('SAML_idpId')
  		                 or ! RequestHandler::getValue('SAML_SingleSignOnService') or ! RequestHandler::getValue('SAML_SingleLogoutService')
  		                 or ! RequestHandler::getValue('SAML_attributeUid') )) {
    $status='CONTROL';
    $errors=i18n('invalidSAMLdefinition');
    $parameterList=array();
  }
  foreach($_REQUEST as $fld => $val) { // TODO (SECURITY) : forbit writting of db and prefix params
    if (array_key_exists($fld, $parameterList)) {
      $crit['parameterCode']=$fld;
      $crit['idUser']=null;
      $crit['idProject']=null;
      if ($fld == 'mailerTestMessage'){
         $text=$val;
      }
      if($fld =='cronArchivePlannedDate'){
        $cronExecutionActiv = SqlElement::getSingleSqlElementFromCriteria('CronExecution', array('fonctionName'=>'archiveHistory'));
        $hoursArchiv = substr($val, 1, 2);
        $minutesArchiv = substr($val, 4, -3);
        $cronExecutionActiv->cron = $minutesArchiv.' '.$hoursArchiv.' * * *';
        $resArchi=$cronExecutionActiv->save();
        $cronExecutionActiv->calculNextTime();
      }
      $obj=SqlElement::getSingleSqlElementFromCriteria('Parameter', $crit);
      if ($parameterList[$fld]=='time') {
        $val=substr($val,1,5);
      }
      $val=str_replace('#comma#',',',$val);
      /*if ($fld=='imputationAlertGenerationDay'  or $fld=='imputationAlertGenerationHour'
       or $fld=='imputationAlertControlDay'     or $fld=='imputationAlertControlNumberOfDays'
       or $fld=='imputationAlertSendToResource' or $fld=='imputationAlertSendToProjectLeader'
       or $fld=='imputationAlertSendToTeamManager') {
        $$fld=$val;
        if ($obj->parameterValue!=$val) {
          $changeImputationAlerts=true;
        }
      }*/
// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
//       if ($obj->parameterValue!=$val and $obj->parameterCode=="notificationSystemActiv") {
//           $changeNotificationSystemActiv = true;
//       }
// END - ADD BY TABARY - NOTIFICATION SYSTEM      
// MTY - LEAVE SYSTEM
      if ($obj->parameterValue!=$val and $obj->parameterCode=="leavesSystemActiv") {
          $changeLeavesSystemActiv = true;
      }
      if ($obj->parameterValue!=$val and $obj->parameterCode=="leavesSystemAdmin") {
          if ($val == getSessionUser()->id or $obj->parameterValue == getSessionUser()->id) {
            $changeLeavesSystemActiv = true;
          }
      }      
// MTY - LEAVE SYSTEM
      $oldValue = $obj->parameterValue;
      $obj->parameterValue=$val;
      $obj->idUser=null;
      $obj->idProject=null;
      $result=$obj->save();
      $paramCode='globalParameter_'.$fld;
      setSessionValue($paramCode, $val);
      $isSaveOK=strpos($result, 'id="lastOperationStatus" value="OK"');
      $isSaveNO_CHANGE=strpos($result, 'id="lastOperationStatus" value="NO_CHANGE"');
      if ($isSaveNO_CHANGE===false) {
        if ($isSaveOK===false) {
          $status="ERROR";
          $errors=$result;
        } else if ($status=="NO_CHANGE") {
          $status="OK";
        }
      }

// MTY - GENERIC DAY OFF
      if (substr($obj->parameterCode,0,7)=="OpenDay" and $oldValue!=$val and $isSaveOK) {
        $weekDayNumWeekDayName = array("OpenDaySunday"=>"dayOfWeek0",
                                       "OpenDayMonday"=>"dayOfWeek1",
                                       "OpenDayTuesday"=>"dayOfWeek2",
                                       "OpenDayWednesday"=>"dayOfWeek3",
                                       "OpenDayThursday"=>"dayOfWeek4",
                                       "OpenDayFriday"=>"dayOfWeek5",
                                       "OpenDaySaturday"=>"dayOfWeek6");
        $calDef = new CalendarDefinition();
        $critCalDef = array("idle" => "0");
        $calDefList = $calDef->getSqlElementsFromCriteria($critCalDef);
        $theField = $weekDayNumWeekDayName[$obj->parameterCode];
        foreach($calDefList as $calDef) {
            $calDef->$theField = ($val=="openDays"?0:1);
            $calDef->save(true);
        }          
      }      
// MTY - GENERIC DAY OFF
      
    }/* else if  ($fld=='imputationAlertGenerationDay'  or $fld=='imputationAlertGenerationHour'
       or $fld=='imputationAlertControlDay'     or $fld=='imputationAlertControlNumberOfDays'
       or $fld=='imputationAlertSendToResource' or $fld=='imputationAlertSendToProjectLeader'
       or $fld=='imputationAlertSendToTeamManager') {
        $$fld=$val;
        $changeImputationAlerts=true;
    }*/
  }
  if ($changeImputationAlerts) {
    /*$cronExec=SqlElement::getSingleSqlElementFromCriteria('CronExecution',array('fonctionName'=>'generateImputationAlert'));
    if (isset($imputationAlertControlDay) and $imputationAlertControlDay=='NEVER'
    or (    isset($imputationAlertSendToResource) and $imputationAlertSendToResource=='NO' 
        and isset($imputationAlertSendToProjectLeader) and $imputationAlertSendToProjectLeader=='NO'
        and isset($imputationAlertSendToTeamManager) and $imputationAlertSendToTeamManager=='NO')) {
      if ($cronExec->id) {
        $cronExec->delete();
      } else {
        // No cron, nothing to do
      }
    } else {
      $hours=substr($imputationAlertGenerationHour,0,2);
      $minutes=substr($imputationAlertGenerationHour,3,2);;
      $dayOfMonth='*';
      $month='*';
      $dayOfWeek=$imputationAlertGenerationDay;
      $cronStr=$minutes.' '.$hours.' '.$dayOfMonth.' '.$month.' '.$dayOfWeek;
      $cronExec->cron=$cronStr;
      $cronExec->fileExecuted='../tool/generateImputationAlert.php';
      $cronExec->idle=false;
      $cronExec->fonctionName='generateImputationAlert';
      $cronExec->nextTime=null;
      $cronExec->save();
    }*/
    //Cron::restart();
    //$errors=i18n("cronRestartRequired");
    //$status='WARNING';
  }

// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
  if ($changeNotificationSystemActiv) {
     $forceRefreshMenu="globalParameter";
     resetUser();
  }
// END - ADD BY TABARY - NOTIFICATION SYSTEM    
// MTY - LEAVE SYSTEM
  if ($changeLeavesSystemActiv) {
     unsetSessionValue("leavesSystemMenus"); 
     $forceRefreshMenu="globalParameter";
     resetUser();
  }
// MTY - LEAVE SYSTEM    
  Parameter::clearGlobalParameters();// force refresh 
}else if($type=='dataCloning'){
  $profileList=SqlList::getList('profile');
  $SaveChange = array();
  foreach ($profileList as $idProfile=>$name){
    $right = RequestHandler::getValue('dataCloningRight'.$idProfile);
    $dataCloningRight=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array("scope"=>"dataCloningRight", "idProfile"=>$idProfile));
    $dataCloningRight->rightAccess = ($right)?$right:0;
    $result=$dataCloningRight->save();
    array_push($SaveChange, $result);
    $access = RequestHandler::getValue('dataCloningAccess'.$idProfile);
    $access = ($access)?1:0;
    $habilitation=SqlElement::getSingleSqlElementFromCriteria('Habilitation', array("idProfile"=>$idProfile, "idMenu"=>"222"));
    $habilitation->allowAccess = $access;
    $result=$habilitation->save();
    array_push($SaveChange, $result);
    
    $creaTotal = RequestHandler::getValue('dataCloningTotal'.$idProfile);
    $paramCreaTotal=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array("scope"=>"dataCloningTotal", "idProfile"=>$idProfile));
    $paramCreaTotal->rightAccess = $creaTotal;
    $result=$paramCreaTotal->save();
    array_push($SaveChange, $result);
  }
  $request = RequestHandler::getValue('dataCloningCreationRequest');
  $frequency = RequestHandler::getValue('dataCloningSpecificFrequency');
  $dataCloningCreationRequest=SqlElement::getSingleSqlElementFromCriteria('Parameter', array("parameterCode"=>"dataCloningCreationRequest"));
  $dataCloningCreationRequest->idUser = null;
  $cronExecution = SqlElement::getSingleSqlElementFromCriteria('CronExecution', array('fonctionName'=>'dataCloningCheckRequest'));
  if($request=='specificHours'){
    $dataCloningCreationRequest->parameterValue = $request;
    $specificHours = RequestHandler::getValue('dataCloningSpecificHours');
    $hours = substr($specificHours, 1, 2);
    $minutes = substr($specificHours, 4, -3);
    $cronExecution->cron = $minutes.' '.$hours.' * * *';
  }else if($request=='immediate'){
    $dataCloningCreationRequest->parameterValue = $frequency;
    if($frequency <= 30){
      $cronExecution->cron = '*/'.$frequency.' * * * *';
    }else{
      $frequency = $frequency/60;
      $cronExecution->cron = '* */'.$frequency.' * * *';
    }
  }else{
    $dataCloningCreationRequest->parameterValue = 'specificHours';
    $specificHours = RequestHandler::getValue('dataCloningSpecificHours');
    $endPm = Parameter::getGlobalParameter('endPM');
    $date=new DateTime();
    $date->setTimestamp(strtotime($endPm));
    $date->modify('+60 minute');
    $endPm = date('H:i', $date->getTimestamp());
    $startAm = Parameter::getGlobalParameter('startAM');
    $date=new DateTime();
    $date->setTimestamp(strtotime($startAm));
    $date->modify('-45 minute');
    $startAm = date('H:i', $date->getTimestamp());
    if(substr($specificHours, 1) >= $endPm or $startAm >= substr($specificHours, 1)){
      $hours = substr($specificHours, 1, 2);
      $minutes = substr($specificHours, 4, -3);
      $cronExecution->cron = $minutes.' '.$hours.' * * *';
    }else{
      $status='INVALID HOURS';
    }
  }
  $result=$dataCloningCreationRequest->save();
  array_push($SaveChange, $result);
  $result=$cronExecution->save();
  $cronExecution->calculNextTime();
  array_push($SaveChange, $result);
  $creaPerDay = RequestHandler::getValue('dataCloningPerDay');
  $paramPerDay=SqlElement::getSingleSqlElementFromCriteria('Parameter', array("parameterCode"=>"dataCloningPerDay"));
  $paramPerDay->parameterValue = $creaPerDay;
  $paramPerDay->idUser = null;
  $result=$paramPerDay->save();
  array_push($SaveChange, $result);
  if($status!='INVALID HOURS'){
    foreach ($SaveChange as $change){
    	$isSaveOK=strpos($change, 'id="lastOperationStatus" value="OK"');
    	$isSaveNO_CHANGE=strpos($change, 'id="lastOperationStatus" value="NO_CHANGE"');
    	if ($isSaveNO_CHANGE===false) {
    		if ($isSaveOK===false) {
    			$status="ERROR";
    			$errors=$result;
    			break;
    		} else if ($status=="NO_CHANGE") {
    			$status="OK";
    		}
    	}else{
    	  continue;
    	}
    }
  }else{
    $status="CONTROL";
    $errors=i18n('messageInvalidTimeNamed', array(i18n('dataCloningSpecificHours')));
  }
}else {
   $errors="Save not implemented";
   $status='WARNING';
}
if ($status=='ERROR') {
	Sql::rollbackTransaction();
  echo '<div class="messageERROR" >' . $errors . '</div>';
} else if ($status=='WARNING'){ 
	Sql::commitTransaction();
  echo '<div class="messageWARNING" >' . i18n('messageParametersSaved') . ' - ' .$errors .'</div>';
  $status='INVALID';
} else if ($status=='CONTROL'){ 
	Sql::commitTransaction();
  echo '<div class="messageWARNING" >' .$errors .'</div>';
  $status='INVALID';
} else if ($status=='OK'){ 
	Sql::commitTransaction();
  echo '<div class="messageOK" >' . i18n('messageParametersSaved') . '</div>';
} else {
	Sql::rollbackTransaction();
  echo '<div class="messageNO_CHANGE" >' . i18n('messageParametersNoChangeSaved') . '</div>';
}
echo '<input type="hidden" id="forceRefreshMenu" value="'.$forceRefreshMenu.'" />';
echo '<input type="hidden" id="lastOperation" name="lastOperation" value="save">';
echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="' . $status .'">';

function resetUser() {
	$user=getSessionUser();
  $user->reset();
	setSessionUser($user);
}
?>
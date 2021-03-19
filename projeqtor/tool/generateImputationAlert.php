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
require_once("../tool/projeqtor.php");

function cronImputationAlertCronFonction($from) {
    
    $dest = substr($from,23);
    $refEndDate=date('Y-m-d');
    $refStartDate=$refEndDate;
    calculDateByDest($refStartDate, $refEndDate, $dest);
    $sendToResource="NO";
    $sendToProjectLeader="NO";
    $sendToTeamManager="NO";
    $sendToOrganismManager="NO";
    $incompleteResource=false;
    $incompleteProjectLeader=false;
    $incompleteTeamManager=false;
    $incompleteOrganismManager=false;
    foreach (Cron::$listCronExecution as $id=>$cronExecution) {
        verifyCronExecution($cronExecution, $sendToResource, $incompleteResource, 'Resource', $from, $refStartDate, $refEndDate);
        verifyCronExecution($cronExecution, $sendToProjectLeader, $incompleteProjectLeader, 'ProjectLeader', $from, $refStartDate, $refEndDate);
        verifyCronExecution($cronExecution, $sendToTeamManager, $incompleteTeamManager, 'TeamManager', $from, $refStartDate, $refEndDate);
        verifyCronExecution($cronExecution, $sendToOrganismManager, $incompleteOrganismManager, 'OrganismManager', $from, $refStartDate, $refEndDate);
    }
    if (! $refStartDate or ! $refEndDate) {
      traceLog("Cron::run() - generationImputationAlert() - Incorrect start date = '$refStartDate' or end date = '$refEndDate' - Exiting");
      return;
    }
    generateImputationAlert($refStartDate, $refEndDate, $sendToResource, $sendToProjectLeader, $sendToTeamManager, $sendToOrganismManager, $incompleteResource, $incompleteProjectLeader, $incompleteTeamManager, $incompleteOrganismManager);
    traceLog("Cron::run() - generateImputationAlert for ".$from.' at '.Cron::$lastCronTimeExecution. " $sendToResource $sendToProjectLeader $sendToTeamManager $sendToOrganismManager" );
}

function verifyCronExecution($cronExecution, &$sendTo, &$incomplete, $dest, $from, $refStartDate, $refEndDate){
    if($cronExecution->fonctionName=="cronImputationAlertCron".$dest){
        $endDate=date('Y-m-d');
        $startDate=$endDate;
        calculDateByDest($startDate, $endDate, $dest);
        if($cronExecution->cron==Cron::$lastCronExecution && (($cronExecution->nextTime == Cron::$lastCronTimeExecution && $refStartDate == $startDate && $refEndDate == $endDate) || $cronExecution->fonctionName == $from)){
            $sendTo=Parameter::getGlobalParameter('imputationAlertSendTo'.$dest);
            $incomplete=Parameter::getGlobalParameter('imputationOnlyIncomplete'.$dest.'Work');
            traceLog("Cron::run() - Calcul Imputation Alert for ".$dest);
            if($cronExecution->fonctionName != $from){
                $cronExecution->calculNextTime();
            }
        }
    }
}

function calculDateByDest(&$startDate, &$endDate, $dest){
    $endDate=date('Y-m-d');
    $controlDay=Parameter::getGlobalParameter('imputationAlertControlDay'.$dest);
    if (!$controlDay) {
        traceLog("generationImputationAlert() - No control day defined - Exiting");
        return;
    }
    if ($controlDay=='next') {
        $endDate=addDaysToDate($endDate, 1);
    } else if ($controlDay=='previous') {
        $endDate=addDaysToDate($endDate, -1);
    } // else = current => nothing to do
    
    $numberOfDays=Parameter::getGlobalParameter('imputationAlertControlNumberOfDays'.$dest);
    if ($numberOfDays=="" or $numberOfDays==null) {
        traceLog("generationImputationAlert() - No number of days defined - Exiting");
        return;
    }
    $startDate=addDaysToDate($endDate, (-1)*($numberOfDays-1));
}

function cronImputationAlertCronResource() {
    cronImputationAlertCronFonction("cronImputationAlertCronResource");
}

function cronImputationAlertCronProjectLeader() {
    cronImputationAlertCronFonction("cronImputationAlertCronProjectLeader");
}

function cronImputationAlertCronTeamManager() {
    cronImputationAlertCronFonction("cronImputationAlertCronTeamManager");
}

function cronImputationAlertCronOrganismManager() {
    cronImputationAlertCronFonction("cronImputationAlertCronOrganismManager");
}

function generateImputationAlert($startDate, $endDate, $sendToResource, $sendToProjectLeader, $sendToTeamManager, $sendToOrganismManager, $incompleteResource, $incompleteProjectLeader, $incompleteTeamManager, $incompleteOrganismManager) {
  $lstRes=array();
  calculListToSend($startDate, $endDate, $lstRes, $incompleteResource, $incompleteProjectLeader, $incompleteTeamManager, $incompleteOrganismManager);
  $dest=array();
  foreach ($lstRes as $id=>$res) {
    if (!$res['full']) {
      if ($sendToResource and $sendToResource!='NO') {
          if (isset($dest[$id])) {
          	$dest[$id]['ress'][$id]=$res['workDetail'];
          	if ($dest[$id]['send']!=$sendToResource) {
          		$dest[$id]['send']='ALERT&MAIL';
          	}
          } else {
          	$dest[$id]=array(
          			'ress'=>array($id=>$res['workDetail']),
          			'send'=>$sendToResource
          	);
          }
      }
      if ($sendToTeamManager and $sendToTeamManager!='NO') {
        $team=SqlList::getFieldFromId('Resource', $id, 'idTeam');
        $manager=(trim($team))?SqlList::getFieldFromId('Team', $team, 'idResource'):'';
        if (trim($manager) and isset($dest[$manager])) {
          $dest[$manager]['ress'][$id]=$res['workDetail'];
          if ($dest[$manager]['send']!=$sendToTeamManager) {
            $dest[$manager]['send']='ALERT&MAIL';
          }
        } else if (trim($manager)) {
          $dest[$manager]=array(
              'ress'=>array($id=>$res['workDetail']),
              'send'=>$sendToTeamManager
          );
        }
      }
      if ($sendToProjectLeader and $sendToProjectLeader!='NO') {
        foreach ($lstRes[$id]['projects'] as $proj) {
          $plList=Affectation::getProjectLeaderList($proj);
          foreach ($plList as $idPL=>$namePL) {
            if (isset($dest[$idPL])) {
              $dest[$idPL]['ress'][$id]=$res['workDetail'];
              if ($dest[$idPL]['send']!=$sendToProjectLeader) {
                $dest[$idPL]['send']='ALERT&MAIL';
              }
            } else {
              $dest[$idPL]=array(
                'ress'=>array($id=>$res['workDetail']),
                'send'=>$sendToProjectLeader
              );
            }
          }
        }
      }
      if ($sendToOrganismManager and $sendToOrganismManager!='NO') {
          $organization=SqlList::getFieldFromId('Resource', $id, 'idOrganization');
          $manager=(trim($organization))?SqlList::getFieldFromId('Organization', $organization, 'idResource'):'';
          if (trim($manager) and isset($dest[$manager])) {
              $dest[$manager]['ress'][$id]=$res['workDetail'];
              if ($dest[$manager]['send']!=$sendToOrganismManager) {
                  $dest[$manager]['send']='ALERT&MAIL';
              }
          } else if (trim($manager)) {
              $dest[$manager]=array(
                  'ress'=>array($id=>$res['workDetail']),
                  'send'=>$sendToOrganismManager
              );
          }
      }
    }
  }
  foreach ($dest as $id=>$dst) {
    $send=$dst['send'];
    $list=$dst['ress'];
    $title=i18n("messageAlertImputationProjectLeader",array(htmlFormatDateTime($endDate)));
    if (count($list)==1 and isset($list[$id])) {
      $title=i18n("messageAlertImputationResource",array(htmlFormatDateTime($endDate)));
    }
    if ($send=='ALERT' or $send=='ALERT&MAIL') {
      $msg="";
      foreach ($list as $idRes=>$detRes) {
        $msg.=(($msg=="")?'':', ').SqlList::getNameFromId('Resource',$idRes);
      }
      sendAlertForImputationAlert($id,$title,$msg);
    }
    if ($send=='MAIL' or $send=='ALERT&MAIL') {
      $msg="";
      foreach ($list as $idRes=>$detRes) {
        $msg.=$detRes;
      }
      sendMailForImputationAlert($id,$title,$msg);
    }
  }
}

function calculListToSend($startDate, $endDate, &$lstRes, $incompleteResource, $incompleteProjectLeader, $incompleteTeamManager, $incompleteOrganismManager){
    $tmpDate=$startDate;
    $emptyArray=array(
        'name'=>'',
        'full'=>false,
        //'overCapacity'=>false,
        'days'=>array(),
        'capacity'=>1,
        'projects'=>array()
    );
    while ($tmpDate<=$endDate) {
        $emptyArray['days'][$tmpDate]=array(
            'open'=>isOpenDay($tmpDate),
            'work'=>0
        );
        $tmpDate=addDaysToDate($tmpDate, 1);
    }
    $lstResource=SqlList::getList('Resource','name',null,false);
    unset(SqlElement::$_cachedQuery['Habilitation']);
    // Initialize list of resources
    foreach ($lstResource as $id=>$name) {
        $userTmp=new User($id);
        if (!$userTmp->id or ! securityCheckDisplayMenu(null,'Imputation',$userTmp)) continue; // #2506 : do not send alert on Real work input if resource does not have access to Timesheet screen 
        $emptyArray['name']=$name;
        if(!isset($lstRes[$id])){
            $lstRes[$id]=array(
                'name'=>$name,
                'full'=>false,
                //'overCapacity'=>false,
                'days'=>array(),
                'capacity'=>SqlList::getFieldFromId('Resource', $id, 'capacity'),
                'projects'=>array()
            );
        }
        // Initialize list of days for the period
        $tmpDate=$startDate;
        while ($tmpDate<=$endDate) {
            if(!isset($lstRes[$id]['days'][$tmpDate])){
                $lstRes[$id]['days'][$tmpDate]=array(
                    'open'=>isOpenDay($tmpDate,SqlList::getFieldFromId('Resource', $id, 'idCalendarDefinition')),
                    'work'=>0
                );
            }
            $tmpDate=addDaysToDate($tmpDate, 1);
        }
        // Store projects the resource is affected to
        $aff=new Affectation();
        $lstAff=$aff->getSqlElementsFromCriteria(array('idResource'=>$id,'idle'=>'0'));
        foreach ($lstAff as $aff) {
            if ( (! $aff->startDate or $aff->startDate<=$endDate) and (! $aff->endDate or $aff->endDate>=$startDate) ) {
                $lstRes[$id]['projects'][$aff->idProject]=$aff->idProject;
            }
        }
    }
    
    $where="workDate>='$startDate' and workDate<='$endDate'";
    $wk=new Work();
    $workList=$wk->getSqlElementsFromCriteria(null,false,$where);
    foreach ($workList as $wk) {
        if (!isset($lstRes[$wk->idResource])) continue; // $lstRes[$wk->idResource]=$emptyArray; // Keep exclusion from access rights, as defined line 218
        if (!isset($lstRes[$wk->idResource]['days'])) $lstRes[$wk->idResource]['days']=array();
        if (!isset($lstRes[$wk->idResource]['days'][$wk->workDate])) $lstRes[$wk->idResource]['days'][$wk->workDate]=array();
        if (!isset($lstRes[$wk->idResource]['days'][$wk->workDate]['work'])) $lstRes[$wk->idResource]['days'][$wk->workDate]['work']=0;
        $lstRes[$wk->idResource]['days'][$wk->workDate]['work']+=$wk->work;
    }
    
    foreach ($lstRes as $idRes=>$res) {
        $tmpDate=$startDate;
        $full=true;
        $overCap=false;
        while ($tmpDate<=$endDate) {
            if($incompleteResource=='true' or $incompleteProjectLeader=='true' or $incompleteTeamManager=='true' or $incompleteOrganismManager=='true'){
              if (isset($res['days'][$tmpDate]) and $res['days'][$tmpDate]['open']=='1' and abs($res['days'][$tmpDate]['work'] - $res['capacity']) >= 0.01 and ($res['days'][$tmpDate]['work'] < $res['capacity'])) {
              	$full=false;
              }
            }else{
              if (isset($res['days'][$tmpDate]) and $res['days'][$tmpDate]['open']=='1' and abs($res['days'][$tmpDate]['work'] - $res['capacity']) >= 0.01) {
              	$full=false;
              }
            }
            $tmpDate=addDaysToDate($tmpDate, 1);
        }
        $lstRes[$idRes]['full']=$full;
        if (!$full) {
            $lstRes[$idRes]['workDetail']=getImputationSummary($res);
        } else {
            $lstRes[$idRes]['workDetail']=null;
        }
    }
}

function getImputationSummary($resTab) {
  $workHeader="";
  $workData="";
  foreach ($resTab['days'] as $day=>$dayData) {
    $colorDay=($dayData['open']=='1')?'#eeeeee':'#aaaaaa';
    $workHeader.='<td style="text-align:center;border:1px solid #555555;width:80px;background-color:'.$colorDay.'">'.htmlFormatDate($day).'</td>';
    $colorData=$colorDay;
    $dayData['work']=round($dayData['work'],2);
    if ($dayData['work']>$resTab['capacity']) {
      $colorData='#ffaaaa';
    } else if ($dayData['open']=='1') {
      if ($dayData['work']==$resTab['capacity']) {
        $colorData='#aaffaa';
      } else if ($dayData['work']<$resTab['capacity']) {
        $colorData='#ffffaa';
      }
    } else if ($dayData['work']>0) {
      $colorData='#ffaaaa';
    }
    $workData.='<td style="text-align:center;border:1px solid #555555;background-color:'.$colorData.'">'.Work::displayWorkWithUnit($dayData['work']).'</td>';
  }
  $result='<table style="font-family:Verdana, Arial;font-size:8pt;border:1px solid #555555;border-collapse: collapse;">';
  $result.='<tr><td style="font-weight:bold;color:#ffffff;text-align:center;border:1px solid #555555;border-right:1px solid #eeeeee;background-color:#555555;width:150px">'.i18n('colIdResource').'</td>'
      .'<td colspan="'.count($resTab['days']).'" style="font-weight:bold;color:#ffffff;text-align:center;background-color:#555555">'.i18n('colWork').'</td></tr>';
  $result.='<tr><td rowspan="2" style="text-align:left;border:1px solid #555555;">'.$resTab['name'].'</td>'.$workHeader.'</tr>';
  $result.='<tr>'.$workData.'</tr>';
  $result.='</table>';    
  return $result;
}

function sendAlertForImputationAlert($alertSendTo,$alertSendTitle,$alertSendMessage){
  $alertSendType='WARNING';
  $alert=new Alert();
  $alert->idUser=$alertSendTo;
  $alert->alertType=$alertSendType;
  $alert->alertInitialDateTime=date('Y-m-d H:i:s');
  $alert->alertDateTime=date('Y-m-d H:i:s');
  $alert->title=mb_substr($alertSendTitle,0,100);
  $alert->message=htmlspecialchars($alertSendMessage,ENT_QUOTES,'UTF-8');
  $result=$alert->save();
}
function sendMailForImputationAlert($alertSendTo,$alertSendTitle,$alertSendMessage) {
  $to=SqlList::getFieldFromId('Resource', $alertSendTo, 'email');
  if (trim($to)) {
    $result=sendMail($to, '['.Parameter::getGlobalParameter('paramDbDisplayName').'] '.$alertSendTitle, $alertSendMessage);
  }
}
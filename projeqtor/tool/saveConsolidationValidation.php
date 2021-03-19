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
 * Save consolidation validation.
 */

require_once "../tool/projeqtor.php";

//parameter
$mode = RequestHandler::getValue('mode');
$lstProj = explode(',', RequestHandler::getValue('lstProj'));
$month= RequestHandler::getValue('month');
$all= RequestHandler::getValue('all');
$currentUser=getCurrentUserId();
$user=getSessionUser();
$res=array();
$lstCons=array();
$lock=($mode=='Locked')?$month:"";
$proj=new Project();
$adminProjects=$proj->getAdminitrativeProjectList(true);
//___get Recursive Sub Projects___//
foreach ($lstProj as $id=>$val){  
  $val=(($mode =='validaTionCons' or $mode=='cancelCons') and $all=='false')?substr($val,6):$val;
  $project= new Project($val);
  $proectsSubList=$project->getRecursiveSubProjectsFlatList();
  $lstSub=array();
  foreach ($proectsSubList as $key=>$name){
    $proj=new Project($key);
    $prof=$user->getProfile($proj);
    if($mode =='validaTionCons' or $mode=='cancelCons'){
      $habilitationVal=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$prof,'scope'=>'validationImputation'));
      if($habilitationVal->rightAccess!='1'){
        unset($proectsSubList[$key]);
        continue;
      }
    }else if($mode=='Locked' or $mode=='Unlocked'){
      $habilitationLock=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$prof,'scope'=>'lockedImputation'));
      if($habilitationLock->rightAccess!='1' ){
        unset($proectsSubList[$key]);
        continue;
      }
    }
    foreach ($lstProj as $idProj){
      if($all=='false' and $month.$key==$idProj){
       unset($proectsSubList[$key]);
      }else if( $all=='true' and $key==$idProj){
        unset($proectsSubList[$key]);
      }
    }
  }
  if(($mode =='validaTionCons' or $mode=='cancelCons') and $all=='false')$lstProj[$id]=$val;
  if(isset($proectsSubList)){
    foreach ($proectsSubList as $key=>$name){
      $lstProj[]=$key;
    }
  }
}
//==============//
if($mode =='validaTionCons'){ // create all consolidationValidation for save 
  $lstImpLocked=array();
  foreach($lstProj as $idVal=>$projId){
    $cons=SqlElement::getSingleSqlElementFromCriteria("ConsolidationValidation",array("idProject"=>$projId,"month"=>$month));
    $projectP=new Project($projId);
    $profile=$user->getProfile($projectP);
    $habilitationLock=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$profile,'scope'=>'lockedImputation'));
//     if($habilitationLock->rightAccess=='2' ){
//       $locked=SqlElement::getSingleSqlElementFromCriteria("LockedImputation",array("idProject"=>$projId,"month"=>$month));
//       if($locked->id!=''){
//         unset($lstProj[$idVal]);
//         continue;
//       }
//     }
    $habValidation=
    $cons->idProject=$projId;
    $idproj=$month.$projId;
    $cons->idResource=$currentUser;
    $cons->month=$month;
    $cons->revenue=RequestHandler::getValue('revenue_'.$idproj);;
    $cons->validatedWork=RequestHandler::getValue('validatedWork_'.$idproj);
    $cons->realWork=RequestHandler::getValue('realWork_'.$idproj);;
    $cons->realWorkConsumed=RequestHandler::getValue('realWorkConsumed_'.$idproj);
    $cons->plannedWork=RequestHandler::getValue('plannedWork_'.$idproj);
    $cons->leftWork=RequestHandler::getValue('leftWork_'.$idproj);
    $cons->margin=RequestHandler::getValue('margin_'.$idproj);
    $cons->validationDate=date('Y-m-d');
    $lstCons[]=$cons;
//     $critArray=array('idProject'=>$projId,'month'=>$month);
//     $lockedImp=SqlElement::getSingleSqlElementFromCriteria('LockedImputation', $critArray);
//     if($lockedImp->id!='')$lstImpLocked[]=$lockedImp->id;
  }
}

Sql::beginTransaction();
if($mode !='validaTionCons' and $mode!='cancelCons'){
  if($mode=='Locked'){
    foreach($lstProj as $projId){
      if (isset($adminProjects[$projId])) continue; 
      $lockImp=new LockedImputation();
      $lockImp->idProject=$projId;
      $lockImp->idResource=$currentUser;
      $lockImp->month=$lock;
      $lockImp->save();
    }
  }else{
    $lstProj=implode(',', $lstProj);
    $where="idProject in ($lstProj) and month ='".$month."'";
    $lockImputation=new LockedImputation();
    $lstImputLocked=$lockImputation->purge($where);
  }
}else {
  if($mode=='validaTionCons'){
    foreach ($lstCons as $cons) {
      $cons->save();
    }
//     if(!empty($lstImpLocked)){
//       $lockedImpProjects= new LockedImputation();
//       $lstImpLocked=implode(',', $lstImpLocked);
//       $clause="id in ($lstImpLocked) and month = '".$month."'";
//       $res=$lockedImpProjects->purge($clause);
//     }
  }else {
      $cons=new ConsolidationValidation();
      $lstProj=implode(',', $lstProj);
      $where="idProject in ($lstProj) and month ='".$month."'";
      $cons->purge($where);
  }
}
Sql::commitTransaction();
?>

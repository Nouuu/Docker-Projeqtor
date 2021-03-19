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

/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/view/refreshSubmitValidateDiv.php'); 

$projId = RequestHandler::getValue('proj');
$reelIdProj=substr($projId,6);
$month = RequestHandler::getValue('month');
$mode=RequestHandler::getValue('mode');
$curUser=getSessionUser();
$canChangeValidation=true;
$project=new Project($reelIdProj);
$prof=$curUser->getProfile($project);
$consValPproj=SqlElement::getSingleSqlElementFromCriteria("ConsolidationValidation",array("idProject"=>$reelIdProj,"month"=>$month));
if($consValPproj->id!=''){
  $clauseWhere="idProject=$reelIdProj and month > '".$month."'";
  $consValPprojAfter=$consValPproj->getSqlElementsFromCriteria(null,null,$clauseWhere);
  if(!empty($consValPprojAfter)){
    $canChangeValidation=false;
  }
}
if($mode!='validaTionCons' and $mode!='cancelCons'){
  $lockImp= new LockedImputation();
  $where="idProject=$projId and month< '".$month."'";
  $critArray= array('idProject'=>$projId,'month'=>$month);
  $lock = SqlElement::getSingleSqlElementFromCriteria('LockedImputation', array("idProject"=>$reelIdProj,"month"=>$month));
  $lock=($mode=="UnLocked")?'':$lock->month;
  $res = ConsolidationValidation::drawLockedDiv($projId, $month, $lock,'',false,$prof,false /*,$consValPproj*/);
}else if ($mode=='validaTionCons' or $mode=='cancelCons'){
  $res = ConsolidationValidation ::drawValidationDiv($consValPproj,$canChangeValidation,$projId,$month,false,$prof);
}
echo $res;
?>

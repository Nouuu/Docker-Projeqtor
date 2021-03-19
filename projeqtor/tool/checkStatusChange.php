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
require_once '../tool/projeqtor.php';

if ( !isset($_REQUEST['newReal'])
   or !isset($_REQUEST['newLeft']) 
   or !isset($_REQUEST['idAssignment']) ) 
exit;

$newReal=$_REQUEST['newReal'];
$newLeft=$_REQUEST['newLeft'];
$idAssignment=$_REQUEST['idAssignment'];

if (! $idAssignment) exit;
Security::checkValidId($idAssignment);

$ass=new Assignment($idAssignment);
$resHandled=null;
$resDone=null;
$pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', array('refType'=>$ass->refType,'refId'=>$ass->refId));
if ($newReal>0 and $pe->realWork==0) {
  $resHandled=$pe->setHandledOnRealWork('check');
  
}

if ($newLeft==0 and $pe->leftWork>0) {
  $crit="id!=".Sql::fmtId($ass->id)
    ." and refType='".Sql::fmtStr($ass->refType)."' and refId=".Sql::fmtId($ass->refId)
    ." and leftWork>0";
  $assList=$ass->getSqlElementsFromCriteria(null,null, $crit);
  if (count($assList)==0) {
    $resDone=$pe->setDoneOnNoLeftWork('check',(($resHandled && $resHandled!='[noResource]')?$resHandled:null));
  }
} 

if ($resHandled) {
  if ($resHandled=='[noResource]') { 
    echo '<img src="../view/css/images/statusStartKO.png" title="'.i18n('moveToHandledStatusFailResource').'" />';
  } else {
    echo '<img src="../view/css/images/statusStart.png" title="'.i18n('moveToNewStatus',array($resHandled)).'" />';
  }
}

if ($resDone) {
  if ($resDone=='[noResult]'){
    echo '<img src="../view/css/images/statusFinishKO.png" title="'.i18n('moveToDoneStatusFailResult').'" />';
  } else {
    echo '<img src="../view/css/images/statusFinish.png" title="'.i18n('moveToNewStatus',array($resDone)).'" />';
  } 
}
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

// Artefact to avoid scriptLog display even if debug level = 4. Comment the line to have it displayed again.
$noScriptLog=true;
require_once "../tool/projeqtor.php";
// Save Audit
//Audit::updateAudit(); done on projeqtor.php

//scriptLog('   ->/tool/checkAlertToDisplay.php');
if (! sessionUserExists()) {
	echo "noUser";
	return;
}
$user=getSessionUser();
$crit=array('idUser'=>$user->id,'readFlag'=>'0', 'idle'=>'0');
$alert=new Alert();
$lst=$alert->getSqlElementsFromCriteria($crit, false, null, 'id asc');
$profile = SqlList::getFieldFromId('Profile', $user->idProfile, 'profileCode', false);
if($profile=='ADM'){
	echo '<input type="hidden" id="cronStatusRefresh" name="cronStatusRefresh" value="'.Cron::check().'" />';
}
// BABYNUS : check new notes
$curObj=SqlElement::getCurrentObject();
if ($curObj and $curObj->id and (isset($curObj->_Note)) and isset($curObj->_storageDateTime)) {
  $note=new Note();
  $dt=$curObj->_storageDateTime;
  $myId=getSessionUser()->id;
  $crit="refType='".get_class($curObj)."' and refId=".$curObj->id." and ( creationDate>'$dt' or updateDate>'$dt' or ( (creationDate='$dt' or updateDate='$dt') and idUser!=$myId) )";
  $cpt=$note->countSqlElementsFromCriteria(null,$crit);
  if ($cpt>0) {
    echo '<input type="hidden" id="alertNeedStreamRefresh" name="alertNeedStreamRefresh" value="' .$cpt. '" />';
  }
}
// END

if (count($lst)==0) {
	return;
}
$date=date('Y-m-d H:i:s');
$cptAlerts=0;
foreach($lst as $alert) { if ($alert->alertDateTime<=$date) {$cptAlerts++;} }
foreach($lst as $alert) {
	if ($alert->alertDateTime<=$date) {
	  echo '<b>' . htmlEncode($alert->title) . '</b>';
	  echo '<br/>';
	  echo  $alert->message;
	  echo '<input type="hidden" id="idAlert" name="idAlert" value="' . htmlEncode($alert->id) . ' " ./>';
	  echo '<input type="hidden" id="alertType" name="alertType" value="' . htmlEncode($alert->alertType) . '" ./>';
	  echo '<input type="hidden" id="alertCount" name="alertCount" value="' . $cptAlerts . '" ./>';
	  return;
	}
}
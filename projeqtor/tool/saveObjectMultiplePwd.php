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

/** ===========================================================================
 * Save the current object : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 * The old values are fetched in $currentObject of SESSION
 * Only changed values are saved. 
 * This way, 2 users updating the same object don't mess.
 */

require_once "../tool/projeqtor.php";
$className = RequestHandler::getClass('objectClass');
$myUserId = getCurrentUserId();
$myUser = new User($myUserId,true);
if (! array_key_exists('selection',$_REQUEST)) {
  throwError('selection parameter not found in REQUEST');
}
$selection=trim($_REQUEST['selection']);
$selectList=explode(';',$selection);

if (!$selection or count($selectList)==0) {
	 $summary='<div class=\'messageWARNING\' >'.i18n('messageNoData',array(i18n($className))).'</div >';
	 echo '<input type="hidden" id="summaryResult" value="'.$summary.'" />';
	 exit;
}

SqlElement::unsetCurrentObject();
$cptOk=0;
$cptError=0;
$cptWarning=0;
$cptNoChange=0;
echo "<table>";
foreach ($selectList as $id) {
	if (!trim($id)) { continue;}
	projeqtor_set_time_limit(300);
	Sql::beginTransaction();
	echo '<tr>';
	echo '<td valign="top"><b>#'.$id.'&nbsp:&nbsp;</b></td>';
	$item=new $className($id);
	if (property_exists($item, 'locked') and $item->locked) {
		Sql::rollbackTransaction();
    $cptWarning++;
    echo '<td><span class="messageWARNING" >' . i18n($className) . " #" . htmlEncode($item->id) . ' '.i18n('colLocked'). '</span></td>';
		continue;
	}
	$statusSave2 = false;
	$resultSave='';
	if($item->email){
	  $oldCrypto = $item->crypto;
	  $oldPwd = $item->password;
	  $newPwd = User::getRandomPassword();
	  $item->crypto = null;
	  $item->password = $newPwd;
  	$resultSave=$item->save();
  	//send mail
  	$dest=$item->email;
  	$title=$item->parseMailMessage(Parameter::getGlobalParameter('paramMailTitleUser'));
  	$msg=$item->parseMailMessage(Parameter::getGlobalParameter('paramMailBodyUser'));
  	$result=(sendMail($dest,$title,$msg))?'OK':'';
  	if (!$result) {
  	  $item->crypto = $oldCrypto;
  	  $item->password = $oldPwd;
  	  $statusSave2 = true;
  	  $item->save();
  	}
	}
	if(!$resultSave)$resultSave=$item->save();
	$resultSave=str_replace('<br/><br/>','<br/>',$resultSave);
	$statusSave = getLastOperationStatus ( $resultSave );
	if ($statusSave=="ERROR" ) {
	  Sql::rollbackTransaction();
	  $cptError++;
	} else if ($statusSave=="OK") {
	  Sql::commitTransaction();
	  $cptOk++;
	} else if ($statusSave=="NO_CHANGE") {
	  Sql::commitTransaction();
	  $cptNoChange++;
	} else { 
	  Sql::rollbackTransaction();
	  $cptWarning++;
  }
  if($statusSave=="OK"){
    if($statusSave2){
      traceLog($item->email.i18n(i18n('noMailSent', array($item->name, $item->email))));
       echo '<td><div style="padding: 0px 5px;" class="messageNO_CHANGE" >'.i18n('User').' #'.$item->id.'  '.i18n('messageNoChange').'.</div></td>';
    }else{
      echo '<td><div style="padding: 0px 5px;" class="message'.$statusSave.'" >' . $resultSave .'. '. i18n('ResetPasswordadm') . '. ' . i18n(('mailSentTo'),array($item->email)).'</div></td>';
    }
  }else{
    echo '<td><div style="padding: 0px 5px;" class="messageNO_CHANGE" >'.i18n('User').' #'.$item->id.'  '.i18n('messageNoChange').'.</div></td>';
  }
  echo '</tr>';
}
echo "</table>";
$summary="";
if ($cptError) {
  $summary.='<div class=\'messageERROR\' >' . $cptError." ".i18n('resultError') . '</div>';
}
if ($cptOk) {
  $summary.='<div class=\'messageOK\' >' . $cptOk." ".i18n('resultOk') . '</div>';
}
if ($cptWarning) {
  $summary.='<div class=\'messageWARNING\' >' . $cptWarning." ".i18n('resultWarning') . '</div>';
}
if ($cptNoChange) {
  $summary.='<div class=\'messageNO_CHANGE\' >' . $cptNoChange." ".i18n('resultNoChange') . '</div>';
}
echo '<input type="hidden" id="summaryResult" value="'.$summary.'" />';
?>
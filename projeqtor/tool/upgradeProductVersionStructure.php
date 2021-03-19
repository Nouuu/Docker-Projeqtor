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
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";
// Get the link info
$objectClass=RequestHandler::getClass('objectClass',true);
$objectId=RequestHandler::getId('objectId',true);
$confirm=(RequestHandler::getAlphanumeric('confirm',true)=='true')?true:false;
$strId=RequestHandler::getId('structureId',false);

$str=new ProductVersionStructure();
$crit = array('idProductVersion'=>$objectId);
if ($strId) {
	$crit=array('id'=>$strId);
}
$strList=$str->getSqlElementsFromCriteria($crit);
global $doNotUpdateAllVersionProject;
$doNotUpdateAllVersionProject=true;
Sql::beginTransaction();
$result="";
//Retrieve the existing list of versions 
// and for each version, find the next version for the component
if (!$confirm) {
	echo '<b>'.i18n('upgradeProductVersionStructure'.(($strId)?'Single':'')).'</b><br/><br/>';
	echo '<table style="width:100%">';
	echo '<tr><td class="noteHeader">'.i18n('colValueBefore').'</td><td class="noteHeader">'.i18n('colValueAfter').'</td></tr>';
}
foreach ($strList as $str) {
	$vers=new ComponentVersion($str->idComponentVersion);
	$oldLabel=$vers->name;
	$newLabel='<i>'.i18n('noChange').'</i>';
	$change=false;
	$crit="idProduct=$vers->idComponent AND (isEis=1 OR isDelivered=1) AND versionNumber IS NOT NULL";
	$lstCompVers=$vers->getSqlElementsFromCriteria(null,false,$crit,'versionNumber DESC');
	if (count($lstCompVers)>0) {
		$new=reset($lstCompVers);
		if ($new->id!=$vers->id) {
			$change=true;
			$str->idComponentVersion=$new->id;
			$newLabel=$new->name;
		}
	}
	if ($confirm) {
	  $prod=new ProductOrComponent($str->idProductVersion);
	  $doNotUpdateAllVersionProject=($prod->scope=='Product')?false:true;// If link is between component versions, do not update all version
	  $res=$str->save();
	} else {
		echo '<tr><td class="noteData">'.$oldLabel.'</td><td class="noteData">'.$newLabel.'</td></tr>';
	}
	if ($confirm) {
	  if (!$result) {
	    $result=$res;
	  } else if (stripos($res,'id="lastOperationStatus" value="OK"')>0 ) {
	  	if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
	  		$deb=stripos($res,'#');
	  		$fin=stripos($res,' ',$deb);
	  		$resId=substr($res,$deb, $fin-$deb);
	  		$deb=stripos($result,'#');
	      $fin=stripos($result,' ',$deb);
	      $result=substr($result, 0, $fin).','.$resId.substr($result,$fin);
	  	} else {
	  	  $result=$res;
	  	} 
	  }
	}
}
if (!$confirm) {
	echo "</table>";
	echo '<br/>'.i18n("messageConfirmationNeeded").'<br/><br/>';
}
// Message of correct saving
if ($confirm) displayLastOperationStatus($result);
?>
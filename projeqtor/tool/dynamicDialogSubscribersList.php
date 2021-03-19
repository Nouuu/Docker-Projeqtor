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
 * List of items subscribed by a user.
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/view/dynamicSubscriptionForOhter.php');
  
$objectClass=RequestHandler::getClass('objectClass',true); 
$objectId=RequestHandler::getId('objectId',true);

$crit="idle=0";
$sub=new Subscription();
$crit=array("refType"=>$objectClass,"refId"=>$objectId);
$stockSub=$sub->getSqlElementsFromCriteria($crit,false,null,null,false);

if (sessionValueExists('screenHeight') and getSessionValue('screenHeight')) {
	$showHeight = round(getSessionValue('screenHeight') * 0.4)."px";
} else {
	$showHeight="100%";
}

$lstSub=array();
foreach ( $stockSub as $id => $sub ) {
	$key='#'.$sub->idAffectable;
	$lstSub[$key]=new Affectable($sub->idAffectable);
}

uasort($lstSub,'Affectable::sort');

echo '<input type="hidden" id="subscriptionObjectClass" value="'.$objectClass.'" />';
echo '<input type="hidden" id="subscriptionObjectId" value="'.$objectId.'" />';
echo '<table style="width:100%;height:100%;min-height:300px">';
echo '<tr style="height:20px">';
echo '<td style="position:relative;">';
echo '<input dojoType="dijit.form.TextBox" id="subscriptionSubscribedSearch" class="input" style="width:230px" value="" onKeyUp="filterDnDList(\'subscriptionSubscribedSearch\',\'subscriptionSubscribed\',\'div\');" />';
echo '<div style="position:absolute;right:4px;top:3px;" class="iconView iconSize16 imageColorNewGui"></div>';

echo '</td></tr>';
echo '<tr>';
echo '<td style="position:relative;" class="noteHeader" >';
echo '<div style="position:absolute;bottom:5px;left:5px;width:24px;height:24px;opacity:0.7;" class="dijitButtonIcon dijitButtonIconSubscribe" ></div>';
echo '<div style="height:'.$showHeight.';overflow:auto;" id="subscriptionSubscribed" dojotype="dojo.dnd.Source" >';
foreach($lstSub as $sub) {
  drawResourceTile($sub);
}
echo '</td>';
echo '</tr>';
echo '</table>';
echo'<br/><table style="width: 100%;" ><tr><td style="width: 100%;" align="center">'
    .'<button dojoType="dijit.form.Button" type="button" onclick="dijit.byId(\'dialogSubscribersList\').hide();">'.i18n("close").'</button>'
    .'</td></tr></table>';

function drawResourceTile($res){
  global $objectClass, $objectId;
  $name=($res->name)?$res->name:$res->userName;
  echo '<div class="subscription" id="subscription'.$res->id.'" value="'.str_replace('"','',$name).'" style="position:relative;padding: 2px 5px 3px 5px;margin:5px;color:#707070;min-height:22px;background-color:#ffffff; border:1px solid #707070" >'
    .formatUserThumb($res->id, "", "")
    .$name
    .'</div>';
}
?>
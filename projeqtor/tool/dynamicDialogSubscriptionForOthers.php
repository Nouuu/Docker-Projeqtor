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

$user=getSessionUser();
$res=new Affectable();
$scope=Affectable::getVisibilityScope();
$crit="idle=0";
if ($scope=='orga') {
	$crit.=" and idOrganization in (". Organization::getUserOrganisationList().")";
} else if ($scope=='team') {
	$aff=new Affectable(getSessionUser()->id,true);
	$crit.=" and idTeam=".Sql::fmtId($aff->idTeam);
}
$lstRes=$res->getSqlElementsFromCriteria(null,false,$crit,'fullName asc, name asc',true);
$sub=new Subscription();
$crit=array("refType"=>$objectClass,"refId"=>$objectId);
$lstSub=$sub->getSqlElementsFromCriteria($crit,false,null,null,false);

$object=new $objectClass($objectId);

if (sessionValueExists('screenHeight') and getSessionValue('screenHeight')) {
	$showHeight = round(getSessionValue('screenHeight') * 0.4)."px";
} else {
	$showHeight="100%";
}

foreach ($lstSub as $idSub=>$sub) {
  if (isset($lstRes['#'.$sub->idAffectable])) {
    $lstSub['#'.$sub->idAffectable]=$lstRes['#'.$sub->idAffectable];
    unset($lstRes['#'.$sub->idAffectable]);
  } else {
    $lstSub['#'.$sub->idAffectable]=new Affectable($sub->idAffectable);
  }
  unset($lstSub[$idSub]);
}

// Should be done depending on profile on corresponding project
//$object=new $objectClass($objectId);
//$profile=$user->getProfile($object);
$profile=getSessionUser()->idProfile; // as of today, only take into account default profile

$crit=array('scope' => 'subscription','idProfile' => $profile);
$habilitation=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
$scope=new AccessScopeSpecific($habilitation->rightAccess, true);
if (! $scope->accessCode or $scope->accessCode == 'NO') {
	$lstRes=array(); // No access to this feature ;)
	$lstSub=array(); // No access to this feature ;)
} else if ($scope->accessCode == 'ALL') {
	// OK
} else if ($scope->accessCode == 'OWN')  {
	$lstRes=array(); // Not for other, should not come here
	$lstSub=array(); // Not for other, should not come here
} else if ($scope->accessCode == 'PRO') {
	$stockRes=$lstRes;
	$lstRes=array();
	$crit='idProject in ' . transformListIntoInClause($user->getAffectedProjects(true));
	$aff=new Affectation(); 
	$lstAff=$aff->getSqlElementsFromCriteria(null, false, $crit, null, true, true);
	$fullTable=SqlList::getList('Resource');
	foreach ($lstSub as $id=>$sub) {
	  $sub->_readOnly=true; // Add readonly
		$lstSub[$id]=$sub;
	}
	foreach ( $lstAff as $id => $aff ) {
		$key='#'.$aff->idResource;
		if (isset($stockRes[$key])) {
		  $lstRes[$key]=$stockRes[$key];
		}
		if (isset($lstSub[$key])) {
			$sub=$lstSub[$key];
			if (isset($sub->_readOnly)) {
				unset($sub->_readOnly);
				$lstSub[$key]=$sub;
			}
		}
	}
} else if ($scope->accessCode == 'TEAM') {
	$lstRes=$user->getManagedTeamResources(true);
	$fullTable=SqlList::getList('Resource');
	foreach ($lstSub as $id=>$sub) {
	  $sub->_readOnly=true; // Add readonly
		$lstSub[$id]=$sub;
	}
	foreach ( $lstRes as $id => $res ) {
		$key=$id;
		if (isset($lstSub[$key])) {
			$sub=$lstSub[$key];
			if (isset($sub->_readOnly)) {
				unset($sub->_readOnly);
				$lstSub[$key]=$sub;
			}
			unset($lstRes[$key]);
		}
	}
} else {
  traceHack("unknown access code '$scope->accessCode'");
}

uasort($lstRes,'Affectable::sort');
uasort($lstSub,'Affectable::sort');

echo '<input type="hidden" id="subscriptionObjectClass" value="'.$objectClass.'" />';
echo '<input type="hidden" id="subscriptionObjectId" value="'.$objectId.'" />';
echo '<table style="width:100%;height:100%;min-height:300px">';
echo '<tr style="height:20px">';
echo '<td class="section" style="width:200px">'.i18n('titleAvailable').'</td>';
echo '<td class="" style="width:50px">&nbsp;</td>';
echo '<td class="section" style="width:200px">'.i18n('titleSelected').'</td>';
echo '</tr>';
echo '<tr style="height:10px"><td colspan="3">&nbsp;</td></tr>';
echo '<tr style="height:20px">';
echo '<td style="position:relative">';
echo '<input dojoType="dijit.form.TextBox" id="subscriptionAvailableSearch" class="input" style="width:210px" value="" onKeyUp="filterDnDList(\'subscriptionAvailableSearch\',\'subscriptionAvailable\',\'div\');" />';
if(!isNewGui()){
  $iconViewPosition = "right:4px;top:3px;";
}else{
  $iconViewPosition = "right:6px;top:10px;";
}
echo '<div style="position:absolute;'.$iconViewPosition.'" class="iconSearch iconSize16 imageColorNewGuiNoSelection"></div>';
echo '</td>';
echo '<td >&nbsp;</td>';
echo '<td style="position:relative;">';
echo '<input dojoType="dijit.form.TextBox" id="subscriptionSubscribedSearch" class="input" style="width:210px" value="" onKeyUp="filterDnDList(\'subscriptionSubscribedSearch\',\'subscriptionSubscribed\',\'div\');" />';
echo '<div style="position:absolute;'.$iconViewPosition.'" class="iconSearch iconSize16 imageColorNewGuiNoSelection"></div>';
echo '</td></tr>';
echo '<tr>';
echo '<td style="position:relative;max-width:200px;vertical-align:top;" class="noteHeader" >';
$imageColorNewGui = "";
if(isNewGui()){
  $imageColorNewGui = 'imageColorNewGuiNoSelection';
}
echo '<div style="position:absolute;bottom:5px;left:5px;width:24px;height:24px;opacity:0.7;" class="dijitButtonIcon dijitButtonIconDelete '.$imageColorNewGui.'" ></div>';
echo '<div style="height:'.$showHeight.';overflow:auto;" id="subscriptionAvailable" dojotype="dojo.dnd.Source" dndType="subsription" withhandles="false" data-dojo-props="accept: [ \'subscription\' ]">';
foreach($lstRes as $res) {
  drawResourceTile($res,"subscriptionAvailable");
}
echo '</div>';
echo '</td>';
echo '<td class="" ></td>';
echo '<td style="position:relative;max-width:200px;max-height:'.$showHeight.';vertical-align:top;" class="noteHeader" >';
echo '<div style="position:absolute;bottom:5px;left:5px;width:24px;height:24px;opacity:0.7;" class="dijitButtonIcon dijitButtonIconSubscribe '.$imageColorNewGui.'" ></div>';
echo '<div style="height:'.$showHeight.';overflow:auto;" id="subscriptionSubscribed" dojotype="dojo.dnd.Source" dndType="subsription" withhandles="false" data-dojo-props="accept: [ \'subscription\' ]">';
foreach($lstSub as $sub) {
  drawResourceTile($sub,"subscriptionSubscribed");
}
echo '</td>';
echo '</tr>';
echo '</table>';
echo'<br/><table style="width: 100%;" ><tr><td style="width: 100%;" align="center">'
    .'<button dojoType="dijit.form.Button" type="button" onclick="dijit.byId(\'dialogSubscriptionForOthers\').hide();">'.i18n("close").'</button>'
    .'</td></tr></table>';

function drawResourceTile($res,$dndSource){
  global $objectClass, $objectId;
  $name=($res->name)?$res->name:$res->userName;
  $canDnD=(isset($res->_readOnly))?false:true;
  echo '<div class="'.(($canDnD)?'dojoDndItem':'').' subscription" id="subscription'.$res->id.'" value="'.str_replace('"','',$name).'" objectclass="'.$objectClass.'" objectid="'.$objectId.'" userid="'.$res->id.'" currentuserid="'.getSessionUser()->id.'" dndType="subscription" style="position:relative;padding: 2px 5px 3px 5px;margin:5px;color:#707070;min-height:22px;background-color:#ffffff; border:1px solid #707070" >'
    .formatUserThumb($res->id, "", "")
    .$name
    .'</div>';
}
?>
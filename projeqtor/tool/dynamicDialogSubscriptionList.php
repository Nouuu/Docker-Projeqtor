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
  scriptLog('   ->/view/dynamicSubscriptionList.php');
  
  $userId=RequestHandler::getId('userId',true); 

echo'<table style="width: 100%;" ><tr><td style="width: 100%;" align="center">'
    .'<button dojoType="dijit.form.Button" type="button" onclick="dijit.byId(\'dialogSubscriptionList\').hide();">'.i18n("close").'</button>'
    .'</td></tr></table><br/>';
echo '<table>';
echo '<tr>';
echo '<td width="75%" class="reportTableHeader" colspan="2">'.i18n('colElement').'</td>';
echo '<td width="15%" class="reportTableHeader" >'.i18n('colIdStatus').'</td>';
echo '<td width="10%" class="reportTableHeader" colspan="2">'.i18n('colSubscription').'</td>';
echo '</tr>';
$sub=new Subscription();
$critArray=array('idAffectable'=>$userId);
$list=$sub->getSqlElementsFromCriteria($critArray);

foreach ($list as $sub) {
  $item=new $sub->refType($sub->refId);
  if (property_exists($item, 'idStatus')) {
    $objStatus=new Status($item->idStatus);
  } else {
  	$objStatus=new Status();
  }
  if (property_exists($item, 'idle') and $item->idle) continue;
  echo '<tr>';
  echo '<td class="reportTableData" width="10%" style="text-align:left;padding:0px 5px;white-space:nowrap;">'
      .'<table><tr><td>'.formatIcon($sub->refType,16).'</td><td>&nbsp;</td><td>'.i18n($sub->refType).' #'.$sub->refId.'</td></tr></table></td>';
  echo '<td class="reportTableData" width="65%" style="text-align:left;padding:0px 5px">'.$item->name.'</td>';
  echo '<td class="reportTableData colorNameData" width="15%">';
  if ($objStatus->id) echo colorNameFormatter($objStatus->name . "#split#" . $objStatus->color);
  echo '</td>';
  echo '<td class="reportTableData" width="5%">'.formatDateThumb($sub->creationDateTime, null).formatUserThumb($sub->idUser, SqlList::getNameFromId('User', $sub->idUser),'').'</td>';
  echo '<td class="reportTableData">';
  echo '<a id="subscribtionButton'.$sub->refType.$sub->refId.'" style="cursor:pointer;display:none;" '
      .'onClick="changeSubscriptionFromDialog(\'on\',\'list\',\''.$sub->refType.'\',\''.$sub->refId.'\',\''.$userId.'\',\''.$sub->refType.$sub->refId.'\',\''.$userId.'\');">';
  echo formatSmallButton('Subscribe');
  echo '</a>';
  echo '<a id="unsubscribtionButton'.$sub->refType.$sub->refId.'" style="cursor:pointer;display:inline-block;" '
      .'onClick="changeSubscriptionFromDialog(\'off\',\'list\',\''.$sub->refType.'\',\''.$sub->refId.'\',\''.$userId.'\',\''.$sub->refType.$sub->refId.'\',\''.$userId.'\');">';
  echo formatSmallButton('Remove');
  echo '</a>';
  echo '</td>';
  echo '</tr>';
}
echo '</table>';
echo'<br/><table style="width: 100%;" ><tr><td style="width: 100%;" align="center">'
    .'<button dojoType="dijit.form.Button" type="button" onclick="dijit.byId(\'dialogSubscriptionList\').hide();">'.i18n("close").'</button>'
        .'</td></tr></table>';

?>
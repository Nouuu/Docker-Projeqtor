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

include_once("../tool/projeqtor.php");
include_once '../tool/formatter.php';

$idNotif = RequestHandler::getId('id');
if($idNotif != ""){
  $readNotif = new Notification($idNotif);
  $readNotif->idStatusNotification = 2;
  $readNotif->save();
}

$notif = new Notification();
$currentUser = getCurrentUserId ();
$crit = array('idStatusNotification'=>1,'idUser'=>$currentUser);
$notifsList=$notif->getSqlElementsFromCriteria($crit, false);
foreach ($notifsList as $result) {
    if ($result->notificationDate>date("Y-m-d") or ($result->notificationDate==date("Y-m-d") and $result->notificationTime>date('H:i:s'))) continue;
    $type=new Type($result->idNotificationType);
    $color = $type->color;
    $userId =  $result->idResource;
    $userName=SqlList::getNameFromId('User', $userId);
    
    echo '<div style=" border-top:0.1px solid #000000;">';
    echo '  <div style="margin-top:5px; margin-bottom:5px;">';
    echo '<table style=" width:99%;">';
    echo' <tr>';
    echo'   <td  style="border-right: 2px solid '.$color.';position:relative;" rowspan="2" width="33px">';
    echo'<div style="margin-right:4px;top:1px;">';
    echo      formatUserThumb($userId,$userName,'Creator','32');
    echo '    <div style="z-index:99;position:absolute; cursor:pointer; top:0px; left:325px; width:16px" onClick="readNotification(\''.htmlEncode($result->id).'\')"  title="'.i18n('markAsRead').'" class="imageColorNewGui iconNotification16 iconNotification iconSize16"> &nbsp;</div>';
    echo'</div>';
    echo'   </td>';
    echo'   <td>';
    echo '  <div onClick="gotoElement(\'Notification\',\''.htmlEncode($result->id).'\')" style="max-width:310px;cursor:pointer; width:90%; overflow-x:hidden;position:relative;left:8px"><b>'. $result->title.' </b></div>';
    echo'   </td>';
    echo' </tr>';
    echo' <tr>';
    echo'   <td colspan="2" align="left">';
    $not=new Notifiable($result->idNotifiable);
    $ref=$not->notifiableItem;
    //$item=new $ref($result->notifiedObjectId);
    if ($result->notifiedObjectId) {
      echo '    <div onClick="gotoElement(\''.$ref.'\',\''.htmlEncode($result->notifiedObjectId).'\')"  style="color:blue;cursor:pointer;text-align:left; font-size:90%; max-height:100px; margin-left:8px; margin-top:5px; overflow-y:auto;">'.i18n($ref) . ' #'.$result->notifiedObjectId.'</div>';
    }
    $content = htmlSetClickableImages($result->content,197);
    echo '    <div style="text-align:left; font-size:90%; max-height:100px; margin-left:8px; margin-top:5px; overflow-y:auto;">'.$content.'</div>';
    echo'   </td>';
    echo' </tr>';
    echo '</table>';
    echo '  </div>';
    echo '</div>';    
}
?>  

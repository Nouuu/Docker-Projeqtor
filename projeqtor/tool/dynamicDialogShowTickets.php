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
 * List of parameter specific to a user.
 * Every user may change these parameters (for his own user only !).
 */
  require_once "../tool/projeqtor.php";
  require_once "../tool/formatter.php";
  scriptLog('   ->/view/dynamicDialogShowTickets.php');
  if (! array_key_exists('refType',$_REQUEST)) {
    throwError('refType parameter not found in REQUEST');
  }
  $refType=$_REQUEST['refType'];
  if (! array_key_exists('refId',$_REQUEST)) {
    throwError('refId parameter not found in REQUEST');
  }
  $refId=$_REQUEST['refId'];
  $user=getSessionUser();

  $obj = new $refType($refId);

  $list=array();
  if ($refType=='Activity') {
    $t=new Ticket();
    $list=$t->getSqlElementsFromCriteria(array('idActivity'=>$refId));
  }
  echo '<table style="width:100%;">';
  echo '<tr>';
  echo '<td class="linkHeader" style="width:15%">' . i18n('colElement') . '</td>';
  echo '<td class="linkHeader" style="width:35%">' . i18n('colName') . '</td>';
  echo '<td class="linkHeader" style="width:10%">' . i18n('colEstimated') . '</td>';
  echo '<td class="linkHeader" style="width:10%">' . i18n('colReal') . '</td>';
  echo '<td class="linkHeader" style="width:10%">' . i18n('colLeft') . '</td>';
  echo '<td class="linkHeader" style="width:20%">' . i18n('colIdStatus') . '</td>';
  echo '</tr>';
  foreach ( $list as $ticket ) {
    $userId=$ticket->idUser;
    $class=get_class($ticket);
    $userName=SqlList::getNameFromId('User', $userId);
    $creationDate=$ticket->creationDateTime;
    $canGoto=(securityCheckDisplayMenu(null, $class) and securityGetAccessRightYesNo('menu'.$class, 'read', $ticket) == "YES")?true:false;
    echo '<tr>';
    $className=i18n($class);
    echo '<td class="linkData" style="white-space:nowrap;width:15%"><table><tr><td>'.formatIcon($class,16).'</td><td>&nbsp;'.$className .' #' . $ticket->id.'</td></tr></table>';
    echo '</td>';
    $goto="";
    if ($canGoto) {
      $goto=' onClick="dijit.byId(\'dialogShowTickets\').hide();gotoElement(' . "'" . $class . "','" . htmlEncode($ticket->id) . "'" . ');" style="cursor: pointer;" ';
    }
    echo '<td class="linkData '.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'" ' . $goto . ' style="position:relative;width:35%">';
    echo htmlEncode($ticket->name);
    echo formatUserThumb($userId, $userName, 'Creator');
    echo formatDateThumb($ticket->creationDateTime, null);
    echo '</td>';
    $workElement = $ticket->WorkElement;
    echo '<td class="linkData"  style="width:10%;text-align:center;">' . Work::displayWorkWithUnit($workElement->plannedWork) . '</td>';
    echo '<td class="linkData"  style="width:10%;text-align:center;">' . Work::displayWorkWithUnit($workElement->realWork) . '</td>';
    echo '<td class="linkData"  style="width:10%;text-align:center;">' . Work::displayWorkWithUnit($workElement->leftWork) . '</td>';
    $objStatus=new Status($ticket->idStatus);
    echo '<td class="dependencyData colorNameData"  style="width:20%">' . colorNameFormatter($objStatus->name . "#split#" . $objStatus->color) . '</td>';
    echo '</tr>';
  }
  echo '</table>';
?>
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
scriptLog('   ->/view/refreshMenuBarFavoriteCount.php');

$idRow = intval(RequestHandler::getValue('idFavoriteRow'));
$defaultMenu = RequestHandler::getValue('defaultMenu');
$nbFavoriteRow=5;
?>
<table style="width:100%;height:100%;"<?php if($defaultMenu == 'menuBarRecent')echo 'display:none';?>>
   <tr>
   <td id="editFavoriteButton" title="<?php echo i18n('editFavoriteRow');?>" onClick="editFavoriteRow(false);" style="padding-left:10px;padding-right: 5px;"><?php echo formatNewGuiButton('Edit', 22, false);?></td>
   <td id="favoriteSwitchRow" style="padding-right:5px;">
    <table style="height:22px;width:10px">
      <tr><td style="font-size:12px;color: var(--color-dark);cursor:pointer;" onClick="switchFavoriteRow(<?php echo $idRow;?>, 'up', <?php echo $nbFavoriteRow;?>);" title="<?php echo i18n('previousRow');?>">▲</td></tr>
      <tr><td style="font-size:12px;color: var(--color-dark);cursor:pointer;" onClick="switchFavoriteRow(<?php echo $idRow;?>, 'down', <?php echo $nbFavoriteRow;?>);" title="<?php echo i18n('nextRow');?>">▼</td></tr>
    </table>
   </td>
 </tr>
</table>

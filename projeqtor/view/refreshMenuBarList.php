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
scriptLog('   ->/view/refreshMenuBarList.php');

$historyTable = RequestHandler::getValue('historyTable');
$defaultMenu = RequestHandler::getValue('menuFilter');
$idRow = intval(Parameter::getUserParameter('idFavoriteRow'));
$nbFavoriteRow=5;
?>
<table  style="width:100%;height:100%;" onWheel="wheelFavoriteRow(<?php echo $idRow;?>, event, <?php echo $nbFavoriteRow;?>);" oncontextmenu="event.preventDefault();editFavoriteRow(false);">
  <tr >
    <td onclick="editFavoriteRow(false);" style="cursor:pointer;
     <?php if($defaultMenu=='menuBarCustom'){
       echo 'width: 50px;border-right: 1px solid var(--color-dark);color: var(--color-dark);font-size: 13pt;font-weight: bold;text-align: center;';
     }else{
         echo 'width: 10px;color: var(--color-dark);font-size: 15pt;font-weight: bold;text-align: center;';
       }?>">
      <?php if($defaultMenu=='menuBarCustom')echo $idRow;?>
    </td>
    <td style="height:100%;">
     <div dojoType="dojo.dnd.Source" id="menuBarDndSource" jsId="menuBarDndSource" dndType="menuBar" data-dojo-props="accept: ['menuBar'], horizontal: true" style="width: 1000%;height: 43px;">
       <input type="hidden" id="idFavoriteRow" name="idFavoriteRow" value="<?php echo $idRow;?>">
       <?php Menu::drawAllNewGuiMenus($defaultMenu, $historyTable, $idRow);?>
     </div>
   </td>
  </tr>
</table>

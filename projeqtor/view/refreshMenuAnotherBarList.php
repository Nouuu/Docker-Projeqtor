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

$defaultMenu = RequestHandler::getValue('menuFilter');
$isMenuLeftOpen = RequestHandler::getValue('isMenuLeftOpen');
if($defaultMenu == 'menuBarCustom'){
  $idRow = intval(Parameter::getUserParameter('idFavoriteRow'));
  $startRow = $idRow+1;
}else{
  $idRow = 1;
  $startRow = $idRow;
}

?>
<table style="width:100%;"><tr>
<td id="hideMenuLeftMargin" style="width:37px;<?php if($isMenuLeftOpen == 'true')echo 'display:none;';?>"></td>
<td style="width:120px;">
  <div style="margin: 5px 5px 5px 5px;height: 43px;width: auto;border: 1px solid var(--color-dark);border-radius: 5px;background: white;overflow:hidden;">
  <?php $menuBarTopMode = Parameter::getUserParameter('menuBarTopMode');?>
    <table style="width:100%;height:100%;">
           <tr>
             <td class="<?php if($menuBarTopMode=='ICON'){echo 'imageColorNewGuiSelected';}else{ echo 'imageColorNewGui';}?>" style="padding-left:8px;" onclick="saveUserParameter('menuBarTopMode', 'ICON');menuNewGuiFilter('menuBarCustom', null);" title="<?php echo i18n('setToIcon');?>"><?php echo formatNewGuiButton('FavorisIcon', 22, true);?></td>
             <td class="<?php if($menuBarTopMode=='ICONTXT'){echo 'imageColorNewGuiSelected';}else{ echo 'imageColorNewGui';}?>" onclick="saveUserParameter('menuBarTopMode', 'ICONTXT');menuNewGuiFilter('menuBarCustom', null);" title="<?php echo i18n('setToIconTxt');?>"><?php echo formatNewGuiButton('FavorisIconTxt', 22, true);?></td>
             <td class="<?php if($menuBarTopMode=='TXT'){echo 'imageColorNewGuiSelected';}else{ echo 'imageColorNewGui';}?>" onclick="saveUserParameter('menuBarTopMode', 'TXT');menuNewGuiFilter('menuBarCustom', null);" title="<?php echo i18n('setToTxt');?>"><?php echo formatNewGuiButton('FavorisTxt', 22, true);?></td>
           </tr>
    </table>
  </div>
  <div title="<?php echo i18n('removeMenu');?>" id="removeMenuDiv" dojoType="dojo.dnd.Source" data-dojo-props="accept: ['menuBar']" style="margin: 0px 5px 0px 5px;height: 141px;width: auto;border: 1px solid var(--color-dark);border-radius: 5px;background: white;overflow:hidden;visibility: hidden;">
    <table style="width:100%;height:100%;">
     <tr>
       <td align="center" style="font-style: italic;font-size: 11px;color: #9c9c9c;"><?php echo i18n('removeMenu');?></td>
     </tr>
     <tr>
       <td class="imageColorNewGui" align="center" title="<?php echo i18n('removeMenu');?>" style="padding-bottom: 20px;"><?php echo formatNewGuiButton('Remove', 32, false);?></td>
     </tr>
    </table>
  </div>
</td>
<td>
<div id="anotherMenubarList" name="anotherMenubarList" style="width:100%;z-index:9999999;">
<?php
$nbFavoriteRow = 5;
for($i=$startRow; $i<=($idRow+4); $i++){
  if($i > 5){
    $idAnotherRow = $i-5;
  }else{
    $idAnotherRow = $i;
  }
  $idDiv = "menuBarDndSource$idAnotherRow";
  $idInput = "idFavoriteRow$idAnotherRow";
  ?>
  <div id="<?php echo 'anotherBar'.$idAnotherRow;?>" class="anotherBar" style="overflow:hidden;margin-top: 5px;height: 43px;width:100%;border: 1px solid var(--color-dark);border-radius: 5px;background: white;">
    <input type="hidden" id="<?php echo $idInput;?>" name="<?php echo $idInput;?>" value="<?php echo $idAnotherRow;?>">
    <table style="width:100%;height:100%;" onWheel="wheelFavoriteRow(<?php echo $idRow;?>, event, <?php echo $nbFavoriteRow;?>);" oncontextmenu="event.preventDefault();editFavoriteRow(false);">
         <tr>
         <td style="font-weight: bold;font-size: 13pt;text-align: center;color: var(--color-dark);width: 50px;border-right: 1px solid var(--color-dark);cursor:pointer;" onclick="saveUserParameter('idFavoriteRow', <?php echo $idAnotherRow;?>);gotoFavoriteRow(<?php echo $idRow;?>,<?php echo $idAnotherRow;?>);">
          <?php echo $idAnotherRow; ?>
         </td>
          <td style="height:100%;">
            <div dojoType="dojo.dnd.Source" class="anotherBarDiv" id="<?php echo $idDiv;?>" jsId="<?php echo $idDiv;?>" name="<?php echo $idDiv;?>" data-dojo-props="accept: ['menuBar'], horizontal: true" style="width: 1000%;height: 43px;vertical-align:middle;">
              <?php Menu::drawAllNewGuiMenus('menuBarCustom', null, $idAnotherRow,true);?>
            </div>
          </td>
         </tr>
    </table>
    </div>
<?php }?>
</div>
</td>
<td style="width:70px;"></td>
</tr></table>
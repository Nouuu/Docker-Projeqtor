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
 * Presents left menu of application. 
 */
  require_once "../tool/projeqtor.php";
  
if(!trim($defaultMenu) or $defaultMenu != 'menuBarCustom'){
  $defaultMenu = 'menuBarCustom';
  Parameter::storeUserParameter('defaultMenu', 'menuBarCustom');
}
$iconSize=22;
$idRow = intval(Parameter::getUserParameter('idFavoriteRow'));
if($idRow==0){
  $idRow=1;
  Parameter::storeUserParameter('idFavoriteRow', $idRow);
}
$nbFavoriteRow=5;
$paramAccessMode = Parameter::getUserParameter('newItemAccessMode');
if(!$paramAccessMode)$paramAccessMode='direct';
?>
<div id="statusBarDiv" dojoType="dijit.layout.ContentPane" region="top" style="height:46px; position:absolute !important;top:30px;left:250px;">
  <div id="menuBarVisibleDiv" style="height:auto;width:auto;  top: 0px; height:43px; left:248px; z-index:0;border-bottom: 3px solid var(--color-dark);">
    <div id="contentMenuBar" class="contentMenuBar" style="left: 0px; top:1px; overflow:hidden; z-index:0">
	    <div  name="menubarContainer" id="menubarContainer" style="height:43px;width:auto; position: relative; left:0px; overflow:hidden;z-index:0">
	      <input type="hidden" id="isEditFavorite" name="isEditFavorite" value="false">
	      <table style="height:43px;width:100%;"><tr>
	           <td style="width: 120px;">
	             <div name="menuBarButtonDiv" id="menuBarButtonDiv" style="width:100%;height:100%;">
	               <table style="width:100%;height:100%;">
        	           <tr>
        	             <td style="padding-left: 10px;">
        	               <div dojoType="dijit.form.DropDownButton" id="addItemButton" jsId="addItemButton" name="addItemButton" class=""
                            showlabel="false" iconClass="iconAdd iconSize22 roundedIconButton imageColorNewGui" title="<?php echo i18n('comboNewButton');?>">
                            <div dojoType="dijit.TooltipDialog" class="white" style="width:200px;height:100%;">
                              <input type="hidden" id="objectClass" name="objectClass" value="" /> 
                              <input type="hidden" id="objectId" name="objectId" value="" />
                              <div style="font-weight:bold; height:25px;text-align:center">
                              <?php echo i18n('comboNewButton');?>
                              </div>
                              <div style="height:25px;" title="<?php echo i18n('helpNewItemAccessMode');?>">
                              <input type="hidden" id="newItemAccessMode" name="newItemAccessMode" value="<?php echo $paramAccessMode?>" />
                              <table style="width:100%">
                              <tr>
                                <td><div style="float:right;"><?php echo i18n('newItemAccessMode');?></div></td>
                                <td style="float:right;">
                                  <div id="dialogAddPopUpAcces" name="dialogAddPopUpAcces" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" 
                                    value="<?php if($paramAccessMode=='direct'){echo 'off';}else{echo 'on';}?>" leftLabel="" rightLabel=""
                                    style="width:10%;top:3px;">
                                      <script type="dojo/method" event="onStateChanged" >
                                        var mode = null;
                                        if(this.value == 'on'){
                                          mode = 'dialog';
                                        }else{
                                          mode = 'direct';
                                        }
                                        dojo.byId('newItemAccessMode').value = mode;
                                        saveUserParameter('newItemAccessMode', mode);
                                      </script>
                                  </div>
                                </td>
                              </tr></table>
                              </div>
                              <?php $arrayItems=array('Project','Resource','Activity','Ticket','Meeting','Milestone');
                              foreach($arrayItems as $item) {
                                $canCreate=securityGetAccessRightYesNo('menu' . $item,'create');
                                if ($canCreate=='YES') {
                                  if (! securityCheckDisplayMenu(null,$item) ) {
                                    $canCreate='NO';
                                  }
                                }
                                if ($canCreate=='YES') {?>
                                <div class="newGuiIconText" style="vertical-align:top;cursor:pointer;margin-top:5px;height:22px;"
                                 onClick="addNewGuiItem('<?php echo $item;?>',null);">
                                  <table width:"100%" ><tr style="height:22px">
                                  <td style="vertical-align:top; width: 30px;padding-left:5px"><?php echo formatIconNewGui($item, 22, null, false);?></td>    
                                  <td style="vertical-align:top;padding-top:2px;"><?php echo i18n($item)?></td>
                                  </tr></table>   
                                </div>
                                <?php } 
                                }?>
                            </div>
                          </div>
        	             </td>
        	             <td>
        	               <input type="hidden" id="itemSelected" name="itemSelected" value="null">
        	               <div name="menuBarFavoriteButton" id="menuBarFavoriteButton" style="width:100%;height:100%;">
        	                 <table style="width:100%;height:100%;">
        	                   <tr>
        	                     <td style="padding-right:5px" class="<?php if($defaultMenu=='menuBarCustom')echo 'imageColorNewGuiSelected';?>" id="favoriteButton" title="<?php echo i18n('Favorite');?>" onclick="menuNewGuiFilter('menuBarCustom', null);"><?php echo formatNewGuiButton('Favoris', 22, true);?></td>
    	                         <td style="padding-right:10px" class="<?php if($defaultMenu=='menuBarRecent')echo 'imageColorNewGuiSelected';?>" id="recentButton" title="<?php echo i18n('Recent');?>" onclick="editFavoriteRow(true);menuNewGuiFilter('menuBarRecent', null);"><?php echo formatNewGuiButton('Recent', 22, true);?></td>
        	                   </tr>
        	                 </table>
        	               </div>
        	             </td>
        	           </tr>
      	           </table>    
	             </div>
	           </td>
    	       <td>
    	         <div name="menuBarListDiv" id="menuBarListDiv" dojoType="dijit.layout.ContentPane"  style="overflow:hidden;width: 100%;height: 43px;border-left: 1px solid var(--color-dark);"> 
        	         <table style="width:100%;height:100%;" onWheel="wheelFavoriteRow(<?php echo $idRow;?>, event, <?php echo $nbFavoriteRow;?>);" oncontextmenu="event.preventDefault();editFavoriteRow(false);">
        	           <tr>
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
        	                 <?php Menu::drawAllNewGuiMenus($defaultMenu, null, $idRow);?>
        	               </div>
        	             </td>
        	           </tr>
        	         </table>
    	         </div>
    	       </td>
    	       <td style="width:70px;">
      	         <div id="favoriteSwitch" style="width:100%;height:100%;">
      	           <table style="width:100%;height:100%;<?php if($defaultMenu == 'menuBarRecent')echo 'display:none';?>">
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
  	             </div>
              </td>     
    	   </tr></table>
  	    </div>
    </div>
  </div>
<button id="menuBarMoveLeft" dojoType="dijit.form.Button" showlabel="false" style="display:none"></button>
<button id="menuBarMoveRight" dojoType="dijit.form.Button" showlabel="false"  style="display:none"></button>
</div>

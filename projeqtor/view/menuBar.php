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
  scriptLog('   ->/view/menuBar.php');
  //$iconSize=Parameter::getUserParameter('paramTopIconSize');
  $iconSize=32;
  $showMenuBar=Parameter::getUserParameter('paramShowMenuBar');
  $showMenuBar='YES';
  $iconClassWithSize=(0 and isNewGui())?false:true;
  //$showMenuBar='NO';
  if (! $iconSize or $showMenuBar=='NO') $iconSize=16;
  $allMenuClass=array('menuBarItem'=>'all','menuBarCustom'=>'custom');
  
  $customMenuArray=SqlList::getListWithCrit("MenuCustom",array('idUser'=>getSessionUser()->id));
  
  $simuIndex=Parameter::getGlobalParameter('simuIndex');
  if($simuIndex){
    $simuClass = 'simuToolBar';
    $simuBarColor = 'style="background-color:#ff7777 !important;"';
  }else{
    $simuClass = '';
    $simuBarColor='';
  }
  $user = getSessionUser();
  $profile = SqlList::getFieldFromId('Profile', $user->idProfile, 'profileCode', false);
  
  $cptAllMenu=0;
  $obj=new Menu();
  $menuList=$obj->getSqlElementsFromCriteria(null, false);
  $pluginObjectClass='Menu';
  $tableObject=$menuList;
  $lstPluginEvt=Plugin::getEventScripts('list',$pluginObjectClass);
  foreach ($lstPluginEvt as $script) {
    require $script; // execute code
  }
  $menuList=$tableObject;
  $defaultMenu=Parameter::getUserParameter('defaultMenu');

// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM  
  $isNotificationSystemActiv = isNotificationSystemActiv();
// END - ADD BY TABARY - NOTIFICATION SYSTEM
  $isLanguageActive=(Parameter::getGlobalParameter('displayLanguage')=='YES')?true:false;
  
  if (! $defaultMenu) $defaultMenu='menuBarItem';
  if (! $defaultMenu and isNewGui()) $defaultMenu='menuBarCustom';
  
  $arrayGlobalMenus=array('menuProject','menuAlert','menuToday','menuParameter','menuUserParameter','menuActivityStream','menuKanban','menuReports');
  foreach ($menuList as $menu) {
// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM  
    if (! $isNotificationSystemActiv and strpos($menu->name, "Notification")!==false) { continue; }
    if (! $menu->canDisplay() ) { continue;}
// END - ADD BY TABARY - NOTIFICATION SYSTEM
    if (! $isLanguageActive and $menu->name=="menuLanguage") { continue; }
    if (securityCheckDisplayMenu($menu->id,substr($menu->name,4))) {
      $menuClass=$menu->menuClass;
      if (in_array($menu->name,$customMenuArray)) $menuClass.=" menuBarCustom";
      if ($menu->type!='menu' and (strpos(' menuBarItem '.$menuClass, $defaultMenu)>0)) {
        $cptAllMenu+=1;
      }
      if ($menu->type=='menu' or in_array($menu->name,$arrayGlobalMenus) ) {
        continue;
      }
      $sp=explode(" ", $menu->menuClass);
      foreach ($sp as $cl) {
        if  ( Module::moduleExists('module'.$cl) and ! Module::isModuleActive('module'.$cl)) continue;
        if (trim($cl)) {
          $allMenuClass[$cl]=$cl;
        }
      }
    }
  }
  
  function drawMenu($menu) {
  	global $iconSize, $defaultMenu,$customMenuArray,$iconClassWithSize;
  	$menuName=$menu->name;
  	$menuClass=' menuBarItem '.$menu->menuClass;
  	if (in_array($menu->name,$customMenuArray)) $menuClass.=' menuBarCustom';
  	$idMenu=$menu->id;
    $style=(strpos($menuClass, $defaultMenu)===false)?'display: none;':'display: block; opacity: 1;';
  	if ($menu->type=='menu') {
    	if ($menu->idMenu==0) {
    		//echo '<td class="menuBarSeparator" style="width:5px;"></td>';
    	}
    } else if ($menu->type=='item') {
    	  $class=substr($menuName,4); 
        //echo '<td  title="' .(($menuName=='menuReports')?'':i18n($menu->name)) . '" >';
    	  echo '<td  title="' .i18n($menu->name) . '" >';
        echo '<div class="'.$menuClass.'" style="position:relative;'.$style.'" id="iconMenuBar'.$class.'" ';
        echo 'onClick="hideReportFavoriteTooltip(0);loadMenuBarItem(\'' . $class .  '\',\'' . htmlEncode(i18n($menu->name),'quotes') . '\',\'bar\');" ';
        echo 'oncontextmenu="event.preventDefault();customMenuManagement(\''.$class.'\');" ';
        if ($menuName=='menuReports' and isHtml5() ) {
          echo ' onMouseEnter="showReportFavoriteTooltip();"';
          echo ' onMouseLeave="hideReportFavoriteTooltip(2000);"';
        }
        echo '>';
        //echo '<img src="../view/css/images/icon' . $class . $iconSize.'.png" />';
        echo '<div class="'.(($iconClassWithSize)?'icon' . $class . $iconSize:'').' icon'.$class.' iconSize'.$iconSize.'" style="margin-left:9px;width:'.$iconSize.'px;height:'.$iconSize.'px" ></div>';
        echo '<div class="menuBarItemCaption">'.i18n($menu->name).'</div>';
        if ($menuName=='menuReports' and isHtml5() ) {?>
          <button class="comboButtonInvisible" dojoType="dijit.form.DropDownButton" 
           id="listFavoriteReports" name="listFavoriteReports" style="position:relative;top:-10px;left:-10px;height: 0px; overflow: hidden; ">
            <div dojoType="dijit.TooltipDialog" id="favoriteReports" style="position:absolute;"
              href="../tool/refreshFavoriteReportList.php"
              onMouseEnter="clearTimeout(closeFavoriteReportsTimeout);"
              onMouseLeave="hideReportFavoriteTooltip(200)"
              onDownloadEnd="checkEmptyReportFavoriteTooltip()">
              <?php Favorite::drawReportList();?>
            </div>
          </button>
        <?php }
        echo '</div>';
        echo '</td>'; 
    } else if ($menu->type=='plugin') {
      $class=substr($menuName,4);
      echo '<td  title="' .i18n($menu->name) . '" >';
      echo '<div class="'.$menuClass.'" style="'.$style.'" id="iconMenuBar'.$class.'"';
      echo 'oncontextmenu="event.preventDefault();customMenuManagement(\''.$class.'\');" ';
      echo 'onClick="loadMenuBarPlugin(\'' . $class .  '\',\'' . htmlEncode(i18n($menu->name),'quotes') . '\',\'bar\');">';
      echo '<img src="../view/css/images/icon' . $class . $iconSize.'.png" />';
      echo '<div class="menuBarItemCaption">'.i18n($menu->name).'</div>';
      echo '</div>';
      echo '</td>';
    } else if ($menu->type=='object') { 
      $class=substr($menuName,4);
      if (securityCheckDisplayMenu($idMenu, $class)) {
      	echo '<td title="' .i18n('menu'.$class) . '" >';
      	echo '<div class="'.$menuClass.'" style="'.$style.'" id="iconMenuBar'.$class.'" ';
      	echo 'oncontextmenu="event.preventDefault();customMenuManagement(\''.$class.'\');" ';
      	echo 'onClick="loadMenuBarObject(\'' . $class .  '\',\'' . htmlEncode(i18n($menu->name),'quotes') . '\',\'bar\');" >';
      	echo '<div class="'.(($iconClassWithSize)?'icon' . $class . $iconSize:'').' icon'.$class.' iconSize'.$iconSize.'" style="margin-left:9px;width:'.$iconSize.'px;height:'.$iconSize.'px" ></div>';
      	//echo '<img src="../view/css/images/icon' . $class . $iconSize. '.png" />';
      	echo '<div class="menuBarItemCaption">'.i18n('menu'.$class).'</div>';
      	echo '</div>';
      	echo '</td>';
      }
    }
  }  
  
  function drawAllMenus($menuList) {
    global $isLanguageActive, $isNotificationSystemActiv,$iconClassWithSize;
    //echo '<td>&nbsp;</td>';
    $obj=new Menu();
    $menuList=$obj->getSqlElementsFromCriteria(null, false);
    $pluginObjectClass='Menu';
    $tableObject=$menuList;
    $lstPluginEvt=Plugin::getEventScripts('list',$pluginObjectClass);
    foreach ($lstPluginEvt as $script) {
      require $script; // execute code
    }
    $menuList=$tableObject;
    $lastType='';
    foreach ($menuList as $menu) { 
      if (! $isLanguageActive and $menu->name=="menuLanguage") { continue; }
      if (! $isNotificationSystemActiv and strpos($menu->name, "Notification")!==false) { continue; }
      if (! $menu->canDisplay() ) { continue;}
      if (securityCheckDisplayMenu($menu->id,substr($menu->name,4)) ) {
    		drawMenu($menu);
    		$lastType=$menu->type;
    	}
    }
    //echo '<td>&nbsp;</td>';
  }
  
?>
<table width="100%">
  <tr height="<?php echo $iconSize+8; ?>px;" <?php echo $simuBarColor;?> >  
    <td style="min-width:320px;width:20%;position:relative; white-space:nowrap">
      <button id="menuBarUndoButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonUndoItem');?>"
       disabled="disabled"
       style="position:relative;left: 5px; top: -7px; z-index:30;height:18px"
       iconClass="dijitButtonIcon dijitButtonIconPrevious" class="detailButton" >
        <script type="dojo/connect" event="onClick" args="evt">
          undoItemButton();
        </script>
      </button>  
      <button id="menuBarRedoButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonRedoItem');?>"
       disabled="disabled"
       style="position:relative;left: 0px; top: -7px; z-index:30;height:18px"
       iconClass="dijitButtonIcon dijitButtonIconNext" class="detailButton" >
        <script type="dojo/connect" event="onClick" args="evt">
          redoItemButton();
        </script>
      </button>
      <a id="menuBarNewtabButton" title="<?php echo i18n('buttonNewtabItem');?>"
         style="height:22px; position:relative;left: 5px; top:-7px; z-index:30; width:60px;" 
         href="" target="_blank">
        <button dojoType="dijit.form.Button" iconClass="dijitButtonIcon iconNewtab  <?php if(isNewGui()){?>iconSize22 <?php }?>" class="detailButton"
          style="height:22px;width:60px;">
          <script type="dojo/connect" event="onClick" args="evt">
            var url="main.php?directAccess=true";
            if (dojo.byId('objectClassManual') && dojo.byId('objectClassManual').value) {
              //url+="&objectClass="+dojo.byId('objectClassManual').value;
              url+="&objectClass=Today";
            } else if (dojo.byId('objectClass') && dojo.byId('objectClass').value) { 
              url+="&objectClass="+dojo.byId('objectClass').value;
            } else {
              url+="&objectClass=Today";
            }
            if (dojo.byId('objectClass') && dojo.byId('objectId') && dojo.byId('objectId').value) {
              url+="&objectId="+dojo.byId('objectId').value;
            } else {
              url+="&objectId=";
            }
            dojo.byId("menuBarNewtabButton").href=url;
          </script>
        </button>
      </a>
          
      <span class="titleProject" style="position: relative; left:20px; top:-<?php echo (isNewGui())?8:6;?>px; text-align:right;">
        &nbsp;<?php echo (i18n("projectSelector"));?>&nbsp;:&nbsp;
      </span>
      <span style="display:inline-block;width:250px; position:relative;left : 10px; top:-<?php echo (isNewGui())?9:6;?>px" title="<?php echo i18n("projectSelectorHelp");?>">
        <span style="postion:absolute;height:16px;" dojoType="dijit.layout.ContentPane" region="center"   id="projectSelectorDiv" 
          <?php if(isNewGui())echo 'onmouseover="showActionProjectSelector();" onmouseout="hideActionProjectSelector();" onfocus="hideActionProjectSelector();"';?>>
          &nbsp;<?php include "menuProjectSelector.php"?>
        </span>
      </span>
      <?php if (isNewGui()){ ?>
      <span style="width: auto;position:relative;top:-7px;left: 130px;">
        <div id="toolbar_projectSelector" class="fade-in dijitTextBox toolbarForSelect" style="width: auto;"
        onmouseover="showActionProjectSelector();"
        onmouseout="hideActionProjectSelector();">
        <table>
          <tr>
            <td>
              <span style="margin-right:3px;" class="roundedButtonSmall">
                <button id="projectSelectorParametersButton" dojoType="dijit.form.Button" showlabel="false"
                 title="<?php echo i18n('dialogProjectSelectorParameters');?>" style="top:2px;height:20px;"
                 iconClass="<?php if ($iconClassWithSize) echo 'iconParameter16';?> iconParameter iconSize16" xclass="detailButton">
                  <script type="dojo/connect" event="onClick" args="evt">
                    loadDialog('dialogProjectSelectorParameters', null, true);
                  </script>
                </button>
              </span>
            </td>
            <td>
              <span style="margin-right:3px;" class="roundedButtonSmall">
                <button id="projectSelectorSelectCurrent" dojoType="dijit.form.Button" showlabel="false"
                   title="<?php echo i18n('selectCurrentProject');?>" style="top:2px;height:20px;width:20px;"
                   ondblclick="directUnselectProject();"
                   onClick="if (!timeoutDirectSelectProject) {showWait();timeoutDirectSelectProject=setTimeout('directSelectProject();',500);}"
                   iconClass="<?php if ($iconClassWithSize) echo 'iconProject16';?> iconProject iconSize16" xclass="detailButton">
                </button>
              </span>
            </td>
            <td>
              <span style="margin-right:3px;" class="roundedButtonSmall">
                <button id="projectSelectorComboButton" dojoType="dijit.form.Button" showlabel="false " style="top:2px;height:20px;width:20px;"
                   title="<?php echo i18n('searchProject');?>" iconClass="iconSearch16 iconSearch iconSize16" >
                   <script type="dojo/connect" event="onClick" args="evt">        
                      showDetail('projectSelectorFiletering', false , 'Project',true,null,true);    
                   </script>
                 </button>
        	   </span>
            </td>
          </tr>
        </table>
        </div>
      </span>
      <?php }else{?>
      <span style="position: relative; left:7px; top:-7px; height: 20px">
        <button id="projectSelectorParametersButton" dojoType="dijit.form.Button" showlabel="false"
         title="<?php echo i18n('dialogProjectSelectorParameters');?>" style="top:2px;height:20px;"
         iconClass="<?php if ($iconClassWithSize) echo 'iconParameter16';?> iconParameter iconSize16" xclass="detailButton">
          <script type="dojo/connect" event="onClick" args="evt">
           loadDialog('dialogProjectSelectorParameters', null, true);
          </script>
        </button>
      </span>
      <span style="position: relative; left:7px; top:-7px; height: 20px">
      <button id="projectSelectorSelectCurrent" dojoType="dijit.form.Button" showlabel="false"
         title="<?php echo i18n('selectCurrentProject');?>" style="top:2px;height:20px;width:20px;"
         ondblclick="directUnselectProject();"
         onClick="if (!timeoutDirectSelectProject) {showWait();timeoutDirectSelectProject=setTimeout('directSelectProject();',500);}"
         iconClass="<?php if ($iconClassWithSize) echo 'iconProject16';?> iconProject iconSize16" xclass="detailButton">
        </button>
      </span>
      <?php }?>
      
    </td>
    <td width="" style="vertical-align:top;text-align:center;" <?php if(isNewGui())echo 'onmouseover="showActionProjectSelector();" onmouseout="hideActionProjectSelector();"';?> >
      <span id="dataBaseTitle" style="position:relative;top:5px;font-size:130%;font-family: Helvetica, Verdana, Arial, Tahoma, sans-serif;z-index:999;"><?php htmlDisplayDatabaseInfos();?></span>
    </td>
    <?php if(isNotificationSystemActiv() and securityCheckDisplayMenu(null,'Notification')) {?>
    <td  width="<?php echo (isNewGui())?42:57;?>px;" style=""> 
     <div dojoType="dijit.layout.ContentPane" id="menuBarNotificationCount"  style="text-align: center; position:relative;top:-5px">
       <div dojoType="dijit.form.DropDownButton"  id=""
            style="display: table-cell;vertical-align: middle;" >
          <span  class="<?php if ($iconClassWithSize) echo 'iconNotification22';?> iconNotification iconSize22" style="display: table-cell;">  
            <span id="countNotifications" class="menuBarNotificationCount" style="text-align:center;" >
              0
            </span>
          </span>
          <div id="drawNotificationUnread" dojoType="dijit.TooltipDialog"
               style="  max-width:360px; overflow-x:hidden; height:300px;  max-height:300px;  width:360px;">
              <?php include "menuNotificationRead.php" ?>          
          </div>       
       </div>         
     </div>
    </td>
    <?php } ?>
    <?php if(isNewGui()){ ?>
    <?php $archiveOn = (sessionValueExists('projectSelectorShowIdle') and getSessionValue('projectSelectorShowIdle')==1)?1:0;?>
    <?php $display = 'display:none;';if($archiveOn==1)$display = '';?>
    <td id="archiveOnSeparator" style="position:relative;width: 5px;<?php echo $display;?>">
      <div class="menuBarSeparatorDiv" style=""></div>
    </td>
    <td id="archiveOnDiv" style="vertical-align: middle;text-align:center;width:32px;<?php echo $display;?>">
        <div id="archiveOn" style="padding-left: 4px;cursor:pointer;<?php echo $display;?>" onClick="setArchiveMode();" title="<?php echo i18n('archiveOn');?>">
          <div class="iconHistArchive iconSize22 imageColorNewGui" style="width:22px;height:22px;position: relative;top: 3px;" title="<?php echo i18n('archiveOn');?>"></div>
          <div style="top: -8px;position: relative;left: 5px;">
            <img style="height:12px;width:12px;" src="img/iconCronRunning.png">
          </div>
        </div>
    </td>
    <?php }?>
    <?php if(isset($paramNewGuiSwitch) and $paramNewGuiSwitch){ ?>
    <?php drawSeparator();?>
    <td style="vertical-align: middle;width:32px;">
       <div id="switchNewGui" class="pseudoButton" style="cursor:pointer;position: relative;top: -<?php echo (isNewGui())?3:5;?>px;left: <?php echo (isNewGui())?5:-3;?>px;height: <?php echo (isNewGui())?28:25;?>px;<?php echo (isNewGui())?'':'padding-top: 3px;padding-left: 3px;';?>" onClick="switchNewGui();">
          <?php echo formatNewGuiButton('Refresh', 22, true);?>
       </div>
    </td>
    <?php }?>
    <?php if($profile == 'ADM'){
     $cronStatus = ucfirst(Cron::check());
     ?>
     <?php drawSeparator();?>
     <td style="vertical-align: middle;text-align:center;width:32px;">
       <div id="menuBarCronStatus" name="menuBarCronStatus" >
        <div class="pseudoButton <?php echo $simuClass;?>"  
        style="height:28px; position:relative;top:-5px; z-index:30; width:32px;" title="<?php if(Cron::check() == 'running'){echo i18n('cronRunning');}else{echo i18n('cronStopped');}?>"
        onClick="checkCronStatus('<?php echo $cronStatus;?>');">
          <img id="cronStatus" name="cronStatus" style="height:22px;width:22px;padding-top:3px;" src="img/iconCron<?php echo $cronStatus;?>.png" />
        </div>
       </div>
    </td>
    <?php }?>    
        <?php drawSeparator();?>
    <td title="<?php echo i18n('infoMessage');?>" style="vertical-align: middle;text-align:center;width:105px;padding-left:3px;"> 
      <div class="pseudoButton <?php echo $simuClass;?>"  style="height:28px; position:relative;top:-5px; z-index:30; width:100px;" >
        <a target="#" href="<?php echo $website;?>" >
          <table style="width:100%">
            <tr>
              <td class="dijitTreeRow" style="position:relative; top:-1px;vertical-align: middle;text-align:center;width:70px;">
                <?php echo "$version";?>
              </td>
              <td  style="width:35px">
                <img id="logoMenuBar" style="height:28px;width:28px;" src="img/logoSmall<?php if (isNewGui()) echo 'White';?>.png" />
              </td>
            </tr>
          </table>
        </a>
      </div>  
    </td>
    <?php if(isNewGui()){ drawSeparator();?>
    <td title="<?php ?>"  style="position:relative;width:55px;padding-left:5px">
      <div dojoType="dijit.layout.ContentPane"  id="menuInterrogation" class="pseudoButton" style="position:relative;overflow:hidden;width:50px; height:28px; min-width:45px;top:-5px;">
        <div dojoType="dijit.form.DropDownButton"  title="<?php echo i18n("menuInterrogationTitle");?>" id="iconMenuInterrogation" style="display: table-cell;vertical-align: middle;position:relative;min-width:40px;top:-3px" >
        <script type="dojo/connect" event="onClick" args="evt">
           loadContent("../view/refreshLastNews.php","getLastNews");       
        </script>
        <table style="width:100%">
    			  <tr>
      				<td style="width:24px;padding-top:2px;">
      				  <div class="iconHelpMenu iconSize22">&nbsp;</div> 
      				</td>
      			  <td style="vertical-align:middle;">&nbsp;</td>
    			  </tr>
			    </table>
			    <div id="drawMenuInterrogation" dojoType="dijit.TooltipDialog"
             style="width:390px !important;">
             <?php include "menuUserInterrogation.php" ?>          
          </div> 
        </div>
      </div>
    </td>
    <?php } drawSeparator();?>
      <td title="<?php ?>"  style="position:relative;width:55px;<?php echo (isNewGui())?'padding-left:5px':'';?>">
      <div dojoType="dijit.layout.ContentPane"  id="menuUserScreenTop" class="pseudoButton" style="position:relative;overflow:hidden;width:50px; height:28px; min-width:55px;top:-5px;">
        <div dojoType="dijit.form.DropDownButton"  title="<?php echo i18n("menuUserScreenTopTitle");?>" id="iconMenuUserScreen" style="display: table-cell;<?php if (!isNewGui()) {?>background-color: #D3D3D3;<?php }?>vertical-align: middle;position:relative;min-width:50px;top:-3px" >
			    <table style="width:100%">
    			  <tr>
      				<td style="width:24px;padding-top:2px;">
      				  <div class="<?php if ($iconClassWithSize) echo 'iconChangeLayout22';?> iconChangeLayout iconSize22">&nbsp;</div> 
      				</td>
      			  <td style="vertical-align:middle;">&nbsp;</td>
    			  </tr>
			    </table>
			    <div id="drawMenuUserScreenOrganization" dojoType="dijit.TooltipDialog"
             style="max-width:600px; overflow-x:hidden; height:450px;  max-height:500px;  width:150px; ">
             <?php include "menuUserScreenOrganization.php" ?>          
          </div> 
		</div>
      </div>
    </td>
    <?php drawSeparator();?>
    <td title="<?php echo i18n('menuUserParameter');?>"  style="position:relative;width:105px;padding-right:5px;">
      <div dojoType="dijit.layout.ContentPane"  id="menuUserParameterTop" class="pseudoButton" style="position:relative;overflow:hidden; height:28px;width:100%; min-width:100px;top:-5px;left:3px;" title="<?php echo i18n('menuUserParameter');?>">
        <div dojoType="dijit.form.DropDownButton"  id="iconMenuUserPhoto" style="display: table-cell;<?php if (!isNewGui()) {?>background-color: #D3D3D3;<?php }?>vertical-align: middle;position:relative;min-width:100px;top:-3px;width:100%" >
			    <table style="width:100%">
    			  <tr>
      			  <?php $user=getSessionUser();
      					 $imgUrl=Affectable::getThumbUrl('User',$user->id, 22,true);
      				if ($imgUrl) {?>  
      				<td style="width:24px;vertical-align:middle;position:relative;">          
      				  <img style="border-radius:13px;height:26px;position:relative;top:1px" src="<?php echo $imgUrl; ?>" />
      				</td>
      			  <?php } else {?>
      				<td id="iconTopMenuUserParameter" style="width:24px;padding-top:3px;">
      				  <div style="height:22px" class="iconTopMenuUserParameter <?php if ($iconClassWithSize) echo 'iconUserParameter22';?> iconUser iconSize22">&nbsp;</div> 
      				</td>
      			   <?php }?>
      			  <td style="vertical-align:middle;">&nbsp;<?php echo ($user->resourceName)?$user->resourceName:$user->name; ?>&nbsp;&nbsp;</td>
    			  </tr>
			    </table>
			    <div id="drawMenuUser" dojoType="dijit.TooltipDialog"
             style="max-width:600px; overflow-x:hidden; height:500px;  max-height:600px;  width:350px;">
             <?php include "menuUserTop.php" ?>          
          </div> 
		    </div>
      </div>
    </td>

  </tr>
</table>
<div class="customMenuAddRemove"  id="customMenuAdd" onClick="customMenuAddItem();"><?php echo i18n('customMenuAdd');?></div>
<div class="customMenuAddRemove"  id="customMenuRemove" onClick="customMenuRemoveItem();"><?php echo i18n('customMenuRemove');?></div>
      
<?php 
function drawSeparator() {
  if (isNewGui()) echo '<td style="position:relative;width: 5px;"><div class="menuBarSeparatorDiv" style=""></div></td>';
}
?>
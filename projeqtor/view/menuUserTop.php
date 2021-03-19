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

//Param
$user=getSessionUser();
$imgUrl=Affectable::getThumbUrl('User',$user->id, 80,true);
$obj=new Parameter();
$listTheme=getThemesList(); // keep 'random' as last value to assure it is not selected via getTheme()
$userLang = getSessionValue('currentLocale');
if (!$userLang and isset($currentLocale)) $userLang=$currentLocale;
$userTheme = getSessionValue('theme');
$startPage = getSessionValue('startPage');
$listStartPage=array();
$listStartPage['welcome.php']=i18n('paramNone');
if (securityCheckDisplayMenu(null,'Today')) {$listStartPage['today.php']=i18n('menuToday');}
if (securityCheckDisplayMenu(null,'DashboardTicket')) {$listStartPage['dashboardTicketMain.php']=i18n('menuDashboardTicket');}
if (securityCheckDisplayMenu(null,'Diary')) {$listStartPage['diaryMain.php']=i18n('menuDiary');}
if (securityCheckDisplayMenu(null,'Imputation')) {$listStartPage['imputationMain.php']=i18n('menuImputation');}
if (securityCheckDisplayMenu(null,'Planning')) {$listStartPage['planningMain.php']=i18n('menuPlanning');}
if (securityCheckDisplayMenu(null,'PortfolioPlanning')) {$listStartPage['portfolioPlanningMain.php']=i18n('menuPortfolioPlanning');}
if (securityCheckDisplayMenu(null,'ResourcePlanning')) {$listStartPage['resourcePlanningMain.php']=i18n('menuResourcePlanning');}
if (securityCheckDisplayMenu(null,'GlobalPlanning')) {$listStartPage['globalPlanningMain.php']=i18n('menuGlobalPlanning');}
if (securityCheckDisplayMenu(null,'Kanban')) {$listStartPage['kanbanViewMain.php']=i18n('menuKanban');}
$arrayItem=array('Project','Document','Ticket','TicketSimple','Activity','Action','Requirement','ProjectExpense','ProductVersion','ComponentVersion','GlobalView');
foreach  ($arrayItem as $item) {
  if (securityCheckDisplayMenu(null,$item)) {$listStartPage['objectMain.php?objectClass='.$item]=i18n('menu'.$item);}
}

$prf=new Profile(getSessionUser()->idProfile);
if ($prf->profileCode=='ADM') {
  $listStartPage['startGuide.php']=i18n('startGuideTitle');
}
$menu=SqlElement::getSingleSqlElementFromCriteria('Menu', array('name'=>'menuUserParameter'));
$showUserParameters=securityCheckDisplayMenu($menu->id,substr($menu->name,4));
?>
<input type="hidden" id="userMenuIdUser" value="<?php echo getCurrentUserId();?>"/>
<table style="width:96%;" id="userMenuPopup">
<?php if (!isNewGui()) {?>
  <tr style="height:40px" class="menuUserTopDetail" >
    <td <?php if ($showUserParameters) echo'rowspan="2"';?> style="white-space:nowrap;vertical-align:middle;text-align:center;position:relative;"><?php if ($imgUrl) { echo '<img style="border-radius:40px;height:80px" src="'.$imgUrl.'" />'; } else { ?>
            <div style="overflow-x:hidden;position: relative; width:80px;height:80px;border-radius:40px; border: 1px solid grey;color: grey;font-size:80%; text-align:center;cursor: pointer;" 
              onClick="addAttachment('file','User','<?php echo getCurrentUserId()?>');" title="<?php echo i18n('addPhoto');?> "><div style="font-size:80%;position:relative;top:32px"><?php echo i18n('addPhoto');?></div></div> 
   <?php } ?></td>
   <td>
    <?php if (Parameter::getGlobalParameter('simuIndex')){?>
     <div class="pseudoButton disconnectTextClass" style="width:120px;height:35px;" title="<?php echo i18n('disconnectMessage');?>" onclick="disconnectDataCloning('welcome','simu');">
        <table style="width:122px;">
          <tr>
            <td> <div class="disconnectClass">&nbsp;</div> </td>
            <td>&nbsp;&nbsp;<?php echo i18n('disconnect');?></td>
          </tr>
        </table>
      </div>
    <?php }else if (SSO::isEnabled()) {?>
     <div class="pseudoButton disconnectTextClass" style="width:120px;height:35px;" title="<?php echo i18n('disconnectMessage');?>" onclick="disconnectSSO('welcome','<?php echo SSO::getCommonName(true);?>');">
        <table style="width:122px;">
          <tr>
            <td> <div class="disconnectClass">&nbsp;</div> </td>
            <td>&nbsp;&nbsp;<?php echo i18n('disconnect');?></td>
          </tr>
        </table>
      </div>
     <div class="pseudoButton disconnectTextClass" style="width:120px;height:35px;" title="<?php echo i18n('ssoDisconnectLoginMessage',array(SSO::getCommonName()));?>" onclick="disconnectSSO('login','<?php echo SSO::getCommonName(true);?>');">
        <table style="width:122px;">
          <tr>
            <td> <div class="disconnectClass">&nbsp;</div> </td>
            <td style="white-space:nowrap">&nbsp;&nbsp;<?php echo i18n('ssoDisconnectLogin');?></td>
          </tr>
        </table>
      </div>
      <?php if (isset($_SESSION['samlNameId'])) {?>
      <div class="pseudoButton disconnectTextClass" style="width:120px;height:30px;" title="<?php echo i18n('ssoDisconnectSSOMessage',array(SSO::getCommonName()));?>" onclick="disconnectSSO('SSO','<?php echo SSO::getCommonName(true);?>');">
        <table style="width:122px;">
          <tr>
            <td> <div class="disconnectClass">&nbsp;</div> </td>
            <td style="white-space:nowrap">&nbsp;&nbsp;<?php echo i18n('ssoDisconnectSSO',array(SSO::getCommonName()));?></td>
          </tr>
        </table>
      </div>
      <?php }?>
    <?php } else { ?>
          <div class="pseudoButton disconnectTextClass" style="width:120px;" title="<?php echo i18n('disconnectMessage');?>" onclick="disconnect(true);">
        <table style="width:122px;">
          <tr>
            <td> <div class="disconnectClass">&nbsp;</div> </td>
            <td>&nbsp;&nbsp;<?php echo i18n('disconnect');?></td>
          </tr>
        </table>
      </div>
     <?php } ?>
    </td>
  </tr>
  <?php }else{?>
  <tr>
    <td style="white-space:nowrap;vertical-align:middle;text-align:center;position:relative;"><?php if ($imgUrl) { echo '<img style="border-radius:40px;height:80px" src="'.$imgUrl.'" />'; } else { ?>
            <div style="overflow-x:hidden;position: relative; width:60px;height:60px;border-radius:40px; border: 1px solid grey;color: grey;font-size:80%; text-align:center;cursor: pointer;" 
              onClick="addAttachment('file','User','<?php echo getCurrentUserId()?>');" title="<?php echo i18n('addPhoto');?> "><div style="left: 19px;position:relative;top: 20px;height:22px;width: 22px;" class="iconAdd iconSize22 imageColorNewGui">&nbsp;</div></div> 
   <?php } ?>
   </td>
   <td style="padding-left: 10px;">
    <table>
      <tr>
        <td style="font-weight:bold;font-size: 12pt;color: var(--color-dark);white-space:nowrap;"><?php echo ucfirst($user->resourceName);?></td>
      </tr>
      <tr>
        <td style="color: var(--color-medium);font-size: 10pt;font-style:italic;white-space:nowrap;"><?php echo $user->name?></td>
      </tr>
      <tr>
        <td style="padding-top:10px;color: var(--color-dark);font-size: 10pt;float: left;"><?php echo i18n('colIdProfile').' :&nbsp';?></td>
        <td style="padding-top:10px;color: var(--color-dark);font-size: 10pt;font-style:italic;white-space:nowrap;float: left;"><?php echo SqlList::getNameFromId('Profile', $user->idProfile);?></td>
      </tr>
      <tr>
        <td style="color: var(--color-dark);font-size: 10pt;float: left;"><?php if($user->idOrganization)echo i18n('colIdOrganization').' :&nbsp';?></td>
        <td style="color: var(--color-dark);font-size: 10pt;font-style:italic;white-space:nowrap;float: left;"><?php if($user->idOrganization)echo SqlList::getNameFromId('Organization', $user->idOrganization);?></td>
      </tr>
      <tr>
        <td style="color: var(--color-dark);font-size: 10pt;padding-bottom:10px;float: left;"><?php if($user->idTeam)echo i18n('colIdTeam').' :&nbsp';?></td>
        <td style="color: var(--color-dark);font-size: 10pt;padding-bottom:10px;font-style:italic;white-space:nowrap;float: left;"><?php if($user->idTeam)echo SqlList::getNameFromId('Team', $user->idTeam);?></td>
      </tr>
    </table>
   </td>
  </tr>
<?php }
if ($showUserParameters) { // Do not give access to user parameters if locked ?>
<?php if(!isNewGui()){?>
  <tr style="height:40px">
    <td style="white-space:nowrap;">
      <div class="pseudoButton"  title="<?php echo i18n('menuUserParameter');?>" onClick="loadMenuBarItem('UserParameter','UserParameter','bar');dijit.byId('iconMenuUserPhoto').closeDropDown();">
        <table style="width:100%">
          <tr>
            <td style="width:24px;padding-top:2px;width:30px;">
              <div class="iconUserParameter22 iconUserParameter iconSize22">&nbsp;</div>
            </td>
            <td style="vertical-align:middle;"><?php echo i18n('menuUserParameter');?>&nbsp;&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
  <?php }?>
  <tr style="height:40px">
    <td width="120px" style="text-align:right"><?php echo i18n("paramLang");?>&nbsp;<?php if (! isNewGui()) echo ":&nbsp;"; ?></td>
    <td>  
      <select dojoType="dijit.form.FilteringSelect" class="input" name="langMenuUserTop" id="langMenuUserTop" 
        <?php echo autoOpenFilteringSelect();?>
        title="<?php echo i18n('helpLang');?>" style="width:225px">
        <script type="dojo/connect" event="onChange" >
          changeLocale(this.value,true);
        </script>
<?php   $listValues=Parameter::getList('lang');
        foreach ($listValues as $value => $valueLabel ) {
          $selected = ($userLang==$value)?'selected':'';
          $value=str_replace(',','#comma#',$value); // Comma sets an isse (not selected) when in value
          echo '<option value="' . $value . '" ' . $selected . '>' . $valueLabel . '</option>';
        }?>
      </select>
    </td>
  </tr>
  <?php if (!isNewGui()) {?>
  <tr style="height:40px">
    <td width="120px" style="text-align:right"><?php echo i18n("paramTheme");?>&nbsp;:&nbsp;</td>
    <td>
      <select dojoType="dijit.form.FilteringSelect" class="input" name="themeMenuUserTop" id="themeMenuUserTop"
        <?php echo autoOpenFilteringSelect();?>
        title="<?php echo i18n('helpTheme');?>" style="width:225px">
<?php   echo $obj->getValidationScript('theme');
        $listValues=$listTheme;
        foreach ($listValues as $value => $valueLabel ) {
          $selected = ($userTheme==$value)?'selected':'';
          $value=str_replace(',','#comma#',$value); // Comma sets an isse (not selected) when in value
          echo '<option value="' . $value . '" ' . $selected . '>' . $valueLabel . '</option>';
        }?>
      </select>
    </td>
  </tr>
<?php } else {?>
  <tr style="height:40px">
    <td width="120px" style="text-align:right"><?php echo i18n("menuMainColor");?>&nbsp;</td>
    <td>
       <input type="color" id="menuUserColorPicker" onInput="setColorTheming(this.value,dojo.byId('menuUserColorPickerBis').value);" onChange="saveDataToSession('newGuiThemeColor',this.value.substr(1),true);setColorTheming(this.value,dojo.byId('menuUserColorPickerBis').value);" value="<?php echo '#'.Parameter::getUserParameter('newGuiThemeColor');?>" style="height: 24px;width: 160px;border-radius: 5px 5px 5px 5px;" />
       <?php drawColorDefaultThemes('menuUserColorPicker','menuUserColorPickerBis',62,170);?>
    </td>
  </tr>  
  <tr style="height:40px">
    <td width="120px" style="text-align:right"><?php echo i18n("menuSecondaryColor");?>&nbsp;</td>
    <td>
       <input type="color" id="menuUserColorPickerBis" onInput="setColorTheming(dojo.byId('menuUserColorPicker').value,this.value);" onChange="saveDataToSession('newGuiThemeColorBis',this.value.substr(1),true);setColorTheming(dojo.byId('menuUserColorPicker').value,this.value);" value="<?php echo '#'.Parameter::getUserParameter('newGuiThemeColorBis');?>" style="height: 24px;width: 160px;border-radius: 5px 5px 5px 5px;" />
    </td>
  </tr>
  <?php 
    $brightness=Parameter::getUserParameter('newGuiThemeBrightness');
    if (!$brightness) $brightness=0;
    ?>
  <tr style="height:10px;"><td colspan="2"></td></tr>
  <tr style="height:40px">
    <td width="120px" style="text-align:right;vertical-align:top;padding-top:4px"><?php echo i18n("menuBrightnessColor");?>&nbsp;</td>
    <td style="">
      <input id="menuUserColorBrightness" value="<?php echo $brightness;?>" type="range" style="width:80%;margin-left:10%"
        data-dojo-type="dijit/form/HorizontalSlider" onChange="setColorThemingBrightness(this.value);"
        data-dojo-props="minimum: 0, maximum: 31, discreteValues: 32, showButtons: false, intermediateChanges: true">
      <ol data-dojo-type="dijit/form/HorizontalRuleLabels" data-dojo-props="container: 'bottomDecoration'" style="height: 1em;width:80%;margin-left:10%">
        <li><?php echo i18n('menuBrightnessClear');?></li>
        <li><?php echo i18n('menuBrightnessDark');?></li>
      </ol>
    </td>
  </tr>
<?php }?>
  <tr style="height:40px">
    <td width="120px" style="text-align:right"><?php echo i18n("menuUserStartPage");?>&nbsp;<?php if (! isNewGui()) echo ":&nbsp;"; ?></td>
    <td>  
      <select dojoType="dijit.form.FilteringSelect" class="input" name="firstPageMenuUserTop" id="firstPageMenuUserTop" 
        <?php echo autoOpenFilteringSelect();?>
        title="<?php echo i18n('menuUserStartPage');?>" style="width:225px">
<?php   echo $obj->getValidationScript('startPage');
        $listValues=$listStartPage;
        foreach ($listValues as $value => $valueLabel ) {
          $selected = ($startPage==$value)?'selected':'';
          $value=str_replace(',','#comma#',$value); // Comma sets an isse (not selected) when in value
          echo '<option value="' . $value . '" ' . $selected . '>' . $valueLabel . '</option>';
        }?>
      </select>
    </td>
  </tr>
  <?php if(!isNewGui()){?>
  <?php if (! isset($lockPassword) or $lockPassword==false) {?>
  <tr style="height:40px">
    <td style="white-space:nowrap;vertical-align:middle;text-align:center;position:relative;"></td>
      <td>
      <div class="pseudoButton"  title="<?php echo i18n('changePassword');?>" onClick="requestPasswordChange();">
        <table style="width:100%">
          <tr>
            <td style="width:24px;padding-top:2px;width:30px;">
              <div class="imageColorNewGui iconLoginPassword22 iconLoginPassword iconSize22">&nbsp;</div>
            </td>
            <td style="vertical-align:middle;"><?php echo ucfirst(i18n('changePassword'));?>&nbsp;&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
  <?php }?>
  <tr style="height:40px">
    <td style="white-space:nowrap;vertical-align:middle;text-align:center;position:relative;"></td>
      <td>
      <div class="pseudoButton"  title="<?php echo i18n('help');?>" onClick="showHelp();">
        <table style="width:100%">
          <tr>
            <td style="width:24px;padding-top:2px;width:30px;">
              <div class="imageColorNewGui iconCatalog22 iconCatalog iconSize22">&nbsp;</div>
            </td>
            <td style="vertical-align:middle;"><?php echo i18n('help');?>&nbsp;&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
  <tr style="height:40px">
    <td style="white-space:nowrap;vertical-align:middle;text-align:center;position:relative;"></td>
    <td>
      <div class="pseudoButton"  title="<?php echo i18n('keyboardShortcuts');?>" onClick="showHelp('ShortCut');">
        <table style="width:100%">
          <tr>
            <td style="width:24px;padding-top:2px;width:30px;">
              <div class="iconShortCut22 iconShortCut iconSize22">&nbsp;</div>
            </td>
            <td style="vertical-align:middle;"><?php echo i18n('keyboardShortcuts');?>&nbsp;&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
  <tr style="height:40px">
    <td style="white-space:nowrap;vertical-align:middle;text-align:center;position:relative;"></td>
    <td>
      <div class="pseudoButton"  title="<?php echo i18n('aboutMessage');?>" onClick="showAbout(aboutMessage);">
        <table style="width:100%">
          <tr>
            <td style="width:24px;padding-top:2px;width:30px;">
              <div class="iconInfo22 iconInfo iconSize22">&nbsp;</div>
            </td>
            <td style="vertical-align:middle;"><?php echo i18n('aboutMessage');?>&nbsp;&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
<?php } 
  } // End of if ($showUserParameters)
  if(isNewGui()){?>
<tr style="height:10px"></tr>
<?php if (! isset($lockPassword) or $lockPassword==false) {?>
<tr style="height:40px">
    <td colspan="2" style="padding-left: 50px;">
      <div class="pseudoButton"  title="<?php echo i18n('changePassword');?>" onClick="requestPasswordChange();">
        <table style="width:100%">
          <tr>
            <td style="padding-left: 10px;width: 30px;vertical-align: middle;">
              <div style="height:22px;width: 22px" class="iconLoginPassword iconSize22 imageColorNewGui">&nbsp;</div>
            </td>
            <td style="vertical-align:middle;font-size:9pt;color: var(--color-dark);"><?php echo i18n('changePassword');?>&nbsp;&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
<?php }?>
<?php if ($showUserParameters) { // Do not give access to user parameters if locked ?>
  <tr style="height:40px">
    <td colspan="2" style="white-space:nowrap;padding-left: 50px;">
      <div class="pseudoButton"  title="<?php echo i18n('menuUserParameter');?>" onClick="loadMenuBarItem('UserParameter','UserParameter','bar');dijit.byId('iconMenuUserPhoto').closeDropDown();">
        <table style="width:100%">
          <tr>
            <td style="padding-left: 10px;width: 30px;">
              <div style="height:22px;width: 22px" class="iconUserParameter iconSize22 imageColorNewGui">&nbsp;</div>
            </td>
            <td style="vertical-align:middle;font-size:9pt;color: var(--color-dark);"><?php echo i18n('menuUserParameter');?>&nbsp;&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
<?php }
  }?>
<?php if(isNewGui()){?>
</table>
<table style="width:100%;" id="userMenuPopupBottom">
<tr style="height:40px;">
  <td style=""></td>
  <td style="width:20%">
    <?php if (Parameter::getGlobalParameter('simuIndex')){?>
     <div class="pseudoButton disconnectTextClass" style="" title="<?php echo i18n('disconnectMessage');?>" onclick="disconnectDataCloning('welcome','simu');">
        <table style="">
          <tr>
            <td> <div class="disconnectClass">&nbsp;</div> </td>
            <td>&nbsp;&nbsp;<?php echo i18n('disconnect');?></td>
          </tr>
        </table>
      </div>
    <?php }else if (SSO::isEnabled()) {?>
     <div class="pseudoButton disconnectTextClass" style="" title="<?php echo i18n('disconnectMessage');?>" onclick="disconnectSSO('welcome','<?php echo SSO::getCommonName(true);?>');">
        <table style="width:122px;">
          <tr>
            <td> <div class="disconnectClass">&nbsp;</div> </td>
            <td>&nbsp;&nbsp;<?php echo i18n('disconnect');?></td>
          </tr>
        </table>
      </div>
     <div class="pseudoButton disconnectTextClass" style="" title="<?php echo i18n('ssoDisconnectLoginMessage',array(SSO::getCommonName()));?>" onclick="disconnectSSO('login','<?php echo SSO::getCommonName(true);?>');">
        <table style="width:122px;">
          <tr>
            <td> <div class="disconnectClass">&nbsp;</div> </td>
            <td style="white-space:nowrap">&nbsp;&nbsp;<?php echo i18n('ssoDisconnectLogin');?></td>
          </tr>
        </table>
      </div>
      <?php if (isset($_SESSION['samlNameId'])) {?>
      <div class="pseudoButton disconnectTextClass" style="" title="<?php echo i18n('ssoDisconnectSSOMessage',array(SSO::getCommonName()));?>" onclick="disconnectSSO('SSO','<?php echo SSO::getCommonName(true);?>');">
        <table style="width:122px;">
          <tr>
            <td> <div class="disconnectClass">&nbsp;</div> </td>
            <td style="white-space:nowrap">&nbsp;&nbsp;<?php echo i18n('ssoDisconnectSSO',array(SSO::getCommonName()));?></td>
          </tr>
        </table>
      </div>
      <?php }?>
    <?php } else { ?>
          <div class="pseudoButton disconnectTextClass" style="" title="<?php echo i18n('disconnectMessage');?>" onclick="disconnect(true);">
        <table style="width:122px;">
          <tr>
            <td> <div class="disconnectClass">&nbsp;</div> </td>
            <td>&nbsp;&nbsp;<?php echo i18n('disconnect');?></td>
          </tr>
        </table>
      </div>
     <?php } ?>
    </td>
    <td style="width:20px;">
  </tr>
<?php }?>
</table>
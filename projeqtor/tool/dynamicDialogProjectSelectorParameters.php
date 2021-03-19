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
$showIdle=(sessionValueExists('projectSelectorShowIdle') and getSessionValue('projectSelectorShowIdle')==1)?1:0;
$showHandlelProject=Parameter::getUserParameter('projectSelectorShowHandlelProject');
$displayMode="standard";
if (sessionValueExists('projectSelectorDisplayMode')) {
  $displayMode=getSessionValue('projectSelectorDisplayMode');
}
?>
<table style="width:100%">
  <tr>
    <td style="text-align: right;width:250px;white-space:nowrap;">
	    <?php echo i18n("labelShowIdle");?>&nbsp;<?php echo (isNewGui())?'':':';?>&nbsp;
	  </td>
	  <td style="text-align: left; vertical-align: middle;width:250px;white-space:nowrap;" title="<?php echo i18n('helpEnterArchiveMode');?>">
	     <div title="<?php echo i18n('showIdleElements');?>" dojoType="dijit.form.CheckBox" type="checkbox"
         <?php if ($showIdle) echo ' checked ';?>">
	       <script type="dojo/method" event="onChange" >
           var callBack = function(){
             //loadContent("../view/menuProjectSelector.php", 'projectSelectorDiv');
             refreshProjectSelectorList();
             if (dojo.byId('objectClass') ) {
               refreshGrid(true);
             }
           }
           saveDataToSession('projectSelectorShowIdle', ((this.checked)?1:0),false,callBack);
           dijit.byId('dialogProjectSelectorParameters').hide();
           <?php if(isNewGui()){?>
              dojo.byId('archiveOn').style.display=(this.checked)?'':'none';
              dojo.byId('archiveOnSeparator').style.display=(this.checked)?'':'none';
              dojo.byId('archiveOnDiv').style.display=(this.checked)?'':'none';
           <?php } ?>
         </script>
	     </div>
	     <?php echo i18n('enterArchiveMode');?>
	  </td>
  </tr>
  <tr>
    <td style="text-align: right;width:250px;white-space:nowrap;">
	    <?php echo i18n("showHandlelProject");?>&nbsp;<?php echo (isNewGui())?'':':';?>&nbsp;
	  </td>
	  <td style="text-align: left; vertical-align: middle;width:280px;white-space:nowrap" title="<?php echo i18n('showHandlelProject');?>">
	     <div title="<?php echo i18n('showHandlelProject');?>" dojoType="dijit.form.CheckBox" type="checkbox"
         <?php if ($showHandlelProject) echo ' checked ';?>">
	       <script type="dojo/method" event="onChange" >
           var callBack = function(){
             refreshProjectSelectorList();
             if (dojo.byId('objectClass') ) {
               refreshGrid(true);
             }
           }
           saveDataToSession('projectSelectorShowHandlelProject', ((this.checked)?1:0),true,callBack);
           dijit.byId('dialogProjectSelectorParameters').hide();
         </script>
	     </div>
	     <?php echo i18n('helpHandledProject');?>
	  </td>
  </tr>
  <tr><td></td><td>&nbsp;</td></tr>
  <tr>
    <td style="text-align: right;width:250px;white-space:nowrap;white-space:nowrap">
      <?php echo i18n("projectListDisplayMode");?>&nbsp;<?php echo (isNewGui())?'':':';?>&nbsp;
    </td>
    <td style="text-align: left; vertical-align: middle;width:250px; word-wrap: none;white-space:nowrap">
      <table><tr><td>
	    <input type="radio" data-dojo-type="dijit/form/RadioButton" name="displayModeCkeckbox"
	     <?php echo ($displayMode=='standard')?'checked':'';?> 
        id="displayModeCkeckboxStandard" value="standard" onClick="changeProjectSelectorType('standard');" />
        </td><td>
        <label class="display" style="background-color: white;<?php echo (isNewGui())?'position:relative;left:6px;top:-2px':'';?>" for="displayModeCkeckboxStandard"><?php echo i18n("displayModeStandard")?></label>
        </td></tr><tr><td>
	    <input type="radio" data-dojo-type="dijit/form/RadioButton" name="displayModeCkeckbox" 
	     <?php echo ($displayMode=='select')?'checked':'';?> 
        id="displayModeCkeckboxSelect" value="select" onClick="changeProjectSelectorType('select');" />
        </td><td>
        <label class="display" style="background-color: white;<?php echo (isNewGui())?'position:relative;left:6px;top:-2px':'';?>" for="displayModeCkeckboxSelect"><?php echo i18n("displayModeSelect")?></label>
        </td></tr><tr><td>
	    <input type="radio" data-dojo-type="dijit/form/RadioButton" name="displayModeCkeckbox" 
	     <?php echo ($displayMode=='search')?'checked':'';?> 
        id="displayModeCkeckboxSearch" value="select" onClick="changeProjectSelectorType('search');" />
        </td><td>
        <label class="display" style="background-color: white;<?php echo (isNewGui())?'position:relative;left:6px;top:-2px':'';?>" for="displayModeCkeckboxSearch"><?php echo i18n("displayModeSearch")?></label>
        </td></tr></table>
    </td>
  </tr>
</table>  
<table style="width:100%">
  <tr style="border-bottom:2px solid #F0F0F0;"><td></td><td>&nbsp;</td></tr>
  <tr style="height:10px;"><td></td><td>&nbsp;</td></tr>
</table>
<table style="width:100%">
	<tr style="height:10px;">
	  <td align="center">
	   <button class="mediumTextButton" dojoType="dijit.form.Button" onclick="dijit.byId('dialogProjectSelectorParameters').hide();">
	     <?php echo i18n("buttonCancel");?>
	   </button>&nbsp;
     <button class="dynamicTextButton" dojoType="dijit.form.Button"
     onclick="refreshProjectSelectorList();">
       <?php echo i18n("buttonRefreshList");?>
     </button>
	  </td>
    <td align="center">
    </td>
	</tr>
	<tr>
	<td colspan="2" style="text-align:center;color:#a0a0a0;"><br/><?php echo i18n("helpRefreshList");?></td>
	</tr>
</table>
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
require_once "../tool/projeqtor.php";
scriptLog('dynamicKpiThreshold.php');

if (! array_key_exists('mode',$_REQUEST)) {
  traceLog("call dynamicKpiThreshold.php without mode paramter");
  exit;
}
$mode=$_REQUEST['mode'];

$idKpiDefinition=null;
if (array_key_exists('idKpiDefinition',$_REQUEST)) {
	$idKpiDefinition=$_REQUEST['idKpiDefinition'];
} 
$idKpiThreshold=null;
if (array_key_exists('idKpiThreshold',$_REQUEST)) {
  $idKpiThreshold=$_REQUEST['idKpiThreshold'];
}

if ($mode=='edit') {
  if (! $idKpiThreshold) {
    traceLog("call dynamicKpiThreshold.php for edit without idKpiThreshold");
    exit;
  }
  $kpiThreshold=new KpiThreshold($idKpiThreshold);
} else if ($mode=='add') {
  if (! $idKpiDefinition) {
    traceLog("call dynamicKpiThreshold.php for add without idKpiDefinition");
    exit;
  }
  $kpiThreshold=new KpiThreshold();
  $kpiThreshold->idKpiDefinition=$idKpiDefinition;
} else {
  traceLog("unexpected value '$mode' for mode");
  exit;
}

?>
  <table>
    <tr>
      <td>
        <form id='dialogKpiThresholdForm' name='dialogKpiThresholdForm' onSubmit="return false;">
      	  <input id="kpiThresholdId" name="kpiThresholdId" type="hidden" value="<?php echo $kpiThreshold->id;?>" />
          <input id="kpiDefinitionId" name="kpiDefinitionId" type="hidden" value="<?php echo $kpiThreshold->idKpiDefinition;?>" />
          <table>
            <tr>
              <td class="dialogLabel" >
               <label for="kpiThresholdName" ><?php echo i18n("colName");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
              </td>
              <td>
		            <input dojoType="dijit.form.ValidationTextBox" 
			           id="kpiThresholdName" name="kpiThresholdName"
			           style="width:300px;"
			           class="input" required
			           value="<?php echo $kpiThreshold->name;?>" />
		          </td>
		        </tr>
		        <tr>
              <td class="dialogLabel" >
                <label for="kpiThresholdValue" ><?php echo i18n("colValue");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
              </td>
              <td>
		            <input dojoType="dijit.form.NumberTextBox" 
			           id="kpiThresholdValue" name="kpiThresholdValue"
			           constraints="{min:0,max:999.99}" 
			           style="width:50px;"
			           class="input" required
			           value="<?php echo $kpiThreshold->thresholdValue;?>" />
		          </td>
		        </tr>
		        <tr>
		          <td class="dialogLabel" >
                <label for="kpiThresholdColor" ><?php echo i18n("colColor");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
              </td>
		          <td>
		            <table>
		              <tr>
		                <?php $color=($kpiThreshold->thresholdColor)?$kpiThreshold->thresholdColor:'transparent';?>
		                <td class="detail">  
                      <input class="colorDisplay" type="text" readonly tabindex="-1" 
                       name='kpiThresholdColor' id='kpiThresholdColor' 
                       value="<?php echo $color;?>"
                       style="border-radius:10px; height:20px; border: 0;width:50px;color:<?php echo $color;?>;background-color:<?php echo $color;?>;" />
                    </td>
                    <td class="detail" style="text-align:left">
                      <div id="kpiThresholdColorButton" dojoType="dijit.form.DropDownButton"
                        style="position:relative;<?php echo (isNewGui())?'border:0 !important;width:60px;':'width:40px';?>; "
                        title="<?php echo i18n('selectColor');?>"
                        showlabel="false" iconClass="colorSelector" class="dropDownNoBorder">
                        <div dojoType="dijit.ColorPalette" style="<?php echo (isNewGui())?'border:0;':'';?>">
                          <script type="dojo/method" event="onChange" >
                            var fld=dojo.byId("kpiThresholdColor");
                            fld.style.color=this.value;
                            fld.style.backgroundColor=this.value;
                            fld.value=this.value;
                          </script>
                        </div>
                      </div>
                    </td>
                    <td>
                      <button id="resetColor" dojoType="dijit.form.Button" showlabel="true"
                        title="<?php echo i18n('helpResetColor');?>" >
                        <span><?php echo i18n('resetColor');?></span>
                        <script type="dojo/connect" event="onClick" args="evt">
                          var fld=dojo.byId("kpiThresholdColor");
                          fld.style.color="transparent";
                          fld.style.backgroundColor="transparent";
                          fld.value="";
                        </script>
                      </button>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
	        </table>    
        </form>
      </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
      <td align="center">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogKpiThreshold').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" id="dialogKpiThresholdSubmit" type="submit" onclick="protectDblClick(this);saveKpiThreshold();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
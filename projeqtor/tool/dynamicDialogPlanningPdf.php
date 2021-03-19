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
$printOrientation=Parameter::getUserParameter("printOrientation");
$printLandscape="";
$printPortrait="";
if($printOrientation=="landscape" || $printOrientation==""){
  $printLandscape='checked="checked"';
}else{
  $printPortrait='checked="checked"';
}

$printZoom=Parameter::getUserParameter("printZoom");

$printRepeat=Parameter::getUserParameter("printRepeat");
if($printRepeat=="repeat" || $printRepeat==""){
  $printRepeat='checked="checked"';
}else{
  $printRepeat="";
}

$printFormat=Parameter::getUserParameter("printFormat");
$printFormatA4="";
$printFormatA3="";
if ($printFormat=="A3") {
  $printFormatA3='checked="checked"';
} else {
  $printFormatA4='checked="checked"';
}
?>
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='planningPdfForm' name='planningPdfForm' onSubmit="return false;">
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="printOrientation" ><?php echo i18n("printOrientation") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <table><tr>
                <td style="text-align:right; width:5%">
                  <input type="radio" dojoType="dijit.form.RadioButton" 
                   name="printOrientation" id="printLandscape" <?php echo $printLandscape;?>
                   value="landscape" style="background-color:white;float:right;"/>
                </td><td style="text-align:left;width:150px;">    
                  <label style="text-align: left;<?php echo (isNewGui())?'position:relative;top:-4px;left:5px;width:100px':'';?>" class="smallRadioLabel" for="printLandscape"><?php echo i18n('printLandscape');?>&nbsp;</label>
                </td>
                <td style="text-align:right; width:5%;margin-left:10px">
                  <input type="radio" dojoType="dijit.form.RadioButton" 
                   name="printOrientation" id="printPortrait" <?php echo $printPortrait;?>
                   value="portrait" style="background-color:white;"/>
                </td><td style="text-align:left;width:150px;"> 
                  <label style="text-align: left;<?php echo (isNewGui())?'position:relative;top:-4px;left:5px;width:100px':'';?>" class="smallRadioLabel" for="printPortrait"><?php echo i18n('printPortrait');?>&nbsp;</label>
                </td>
              </tr></table>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel"  >
               <label for="printFormat" ><?php echo i18n("printFormat") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <table><tr>
                <td style="text-align:right; width:5%">
                  <input type="radio" dojoType="dijit.form.RadioButton" 
                   name="printFormat" id="printFormatA4" <?php echo $printFormatA4;?>
                   value="A4" style="background-color:white;float:right;"/>
                </td><td style="text-align:left;width:150px;">    
                  <label style="text-align: left;<?php echo (isNewGui())?'position:relative;top:-3px;left:5px;width:100px':'';?>" class="smallRadioLabel" for="printFormatA4">A4&nbsp;</label>
                </td>
                <td style="text-align:right; width:5%;">
                  <input type="radio" dojoType="dijit.form.RadioButton" 
                   name="printFormat" id="printFormatA3" <?php echo $printFormatA3;?>
                   value="A3" style="background-color:white;"/>
                </td><td style="text-align:left;width:150px;"> 
                  <label style="text-align: left;<?php echo (isNewGui())?'position:relative;top:-3px;left:5px;width:100px':'';?>" class="smallRadioLabel" for="printFormatA3">A3&nbsp;</label>
                </td>
              </tr></table>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel"  >
               <label for="printZoom" ><?php echo i18n("printZoom") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
               <?php echo autoOpenFilteringSelect();?>
                id="printZoom" name="printZoom" style="width:65px;" required class="input">
                 <?php for ($i=100;$i>=10;$i-=10) {?>
                <option <?php if ($printZoom==$i) { echo 'selected="selected"';}?> value="<?php echo $i;?>"><?php echo $i;?>%</option>
                <?php }?>
               </select>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel"  >
               <label for="printRepeat"><?php echo i18n("printRepeat") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td class="dialogLabel" colspan="2" style="width:100%; text-align: left;">
               
               <div id="printRepeat" name="printRepeat" dojoType="dijit.form.CheckBox" type="checkbox" 
                <?php echo $printRepeat;?>>
               </div>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="planningPdfAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogPlanningPdf').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogPlanningPdfSubmit" onclick="protectDblClick(this);planningToCanvasToPDF();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
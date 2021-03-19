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
$currency=Parameter::getGlobalParameter('currency');
$currencyPosition=Parameter::getGlobalParameter('currencyPosition');
$keyDownEventScript=NumberFormatter52::getKeyDownEvent();
?>
<table>
  <tr>
    <td>
      <form dojoType="dijit.form.Form" id='resourceCostForm' jsid='resourceCostForm' name='resourceCostForm' onSubmit="return false;">
      <input id="resourceCostId" name="resourceCostId" type="hidden" value="" />
      <input id="resourceCostIdResource" name="resourceCostIdResource" type="hidden" value="" />
      <input id="resourceCostFunctionList" name="resourceCostFunctionList" type="hidden" value="" />
    <table>
      <tr>
        <td class="dialogLabel" >
          <label for="resourceCostIdRole" ><?php echo i18n("colIdRole");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
        </td>
         <td>
          <select dojoType="dijit.form.FilteringSelect" 
          <?php echo autoOpenFilteringSelect();?>
            id="resourceCostIdRole" name="resourceCostIdRole"
            class="input" value=""
            onChange="resourceCostUpdateRole();"
            missingMessage="<?php echo i18n('messageMandatory',array(i18n('colIdRole')));?>" >
             <?php htmlDrawOptionForReference('idRole', null, null, true);?>
           </select>  
         </td>
      </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="resourceCostValue" ><?php echo i18n("colCost");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td><span class="nobr">
               <?php echo ($currencyPosition=='before')?$currency:''; ?>
               <div id="resourceCostValue" name="resourceCostValue" value="" 
                 dojoType="dijit.form.NumberTextBox" 
                 constraints="{min:0}" 
                 style="width:97px; text-align: right;" 
                 missingMessage="<?php echo i18n('messageMandatory',array(i18n('colCost')));?>" 
                 required="true" >
                 <?php echo $keyDownEventScript;?>
                 </div>
               <?php echo ($currencyPosition=='after')?$currency:'';
                     echo " / ";
                     echo i18n('shortDay'); ?>
               </span>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="resourceCostStartDate" ><?php echo i18n("colStartDate");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <div id="resourceCostStartDate" name="resourceCostStartDate" value="" 
                 dojoType="dijit.form.DateTextBox" 
                 constraints="{datePattern:browserLocaleDateFormatJs}"
                 style="width:100px" class="input"
                 hasDownArrow="true"
               >
               </div>
             </td>    
           </tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogResourceCostAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogResourceCost').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogResourceCostSubmit" onclick="protectDblClick(this);saveResourceCost();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
</table>

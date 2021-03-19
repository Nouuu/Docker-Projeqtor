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
 * List of parameter specific to a user.
 * Every user may change these parameters (for his own user only !).
 */
  require_once "../tool/projeqtor.php";
  require_once "../tool/formatter.php";
  scriptLog('   ->/view/dynamicDialogReplaceAffectation.php');
  if (! array_key_exists('idAffectation',$_REQUEST)) {
    throwError('idAffectation parameter not found in REQUEST');
  }
  $affId=$_REQUEST['idAffectation'];
  $aff=new Affectation($affId);
  $res=new Resource($aff->idResource);
  $obj=SqlElement::getCurrentObject(null,null,true,false) ;
  ?>
<div>
  <form dojoType="dijit.form.Form" id='replaceAffectationForm' name='replaceAffectationForm' onSubmit="return false;">
  <input type="hidden" name="replaceAffectationIdAffectation" value="<?php echo $aff->id;?>" />
  <input type="hidden" id="replaceAffectationExistingResource" value="<?php echo $aff->idResource;?>" />
  <table style="width:100%;">
    <tr>
      <td></td>
      <td class="section" style="width:200px;"><?php echo i18n('currentAffectation');?></td>
      <td>&nbsp;&nbsp;</td>
      <td class="section" style="width:200px;"><?php echo i18n('targetAffectation');?></td>
    </tr>
    <tr><td colspan="4">&nbsp;</td></tr>
    <tr>
      <td class="dialogLabel"><label><?php echo i18n('colIdResource');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label></td>
      <td><input dojoType="dijit.form.TextBox" class="input" readonly style="width:200px;" value="<?php echo htmlEncode($res->name);?>"/></td>
      <td>&nbsp;&nbsp;</td>
      <td><select dojoType="dijit.form.FilteringSelect" id="replaceAffectationResource" name="replaceAffectationResource" 
                <?php echo autoOpenFilteringSelect();?>
                onChange="replaceAffectationChangeResource();" 
                class="input required" value="" required="required" style="width:200px;">
           <?php htmlDrawOptionForReference('idResourceAll', null, null, true);?>
          </select></td>
    </tr>
    <tr>
      <td class="dialogLabel"><label><?php echo i18n('colCapacity');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label></td>
      <td><input dojoType="dijit.form.NumberTextBox" class="input" readonly style="width:30px;" 
           value="<?php echo htmlEncode($res->capacity);?>"/></td>
      <td>&nbsp;&nbsp;</td>
      <td><input dojoType="dijit.form.NumberTextBox" class="input" readonly style="width:30px;" 
           name="replaceAffectationCapacity" id="replaceAffectationCapacity" 
           value="" /></td>
    </tr>
    <tr>
      <td class="dialogLabel"><label><?php echo i18n('colIdProfile');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label></td>
      <td><input dojoType="dijit.form.TextBox" class="input" readonly style="width:200px;" value="<?php echo htmlEncode(SqlList::getNameFromId('Profile',$aff->idProfile));?>"/></td>
      <td>&nbsp;&nbsp;</td>
      <td><select dojoType="dijit.form.FilteringSelect" id="replaceAffectationProfile" name="replaceAffectationProfile"
                <?php echo autoOpenFilteringSelect();?> 
                class="input required" value="<?php echo $aff->idProfile?>" required="required" style="width:200px;">
           <?php htmlDrawOptionForReference('idProfile', $aff->idProfile, $obj, true);?>
          </select></td>
    </tr>
    <tr>
      <td class="dialogLabel"><label><?php echo i18n('colRate');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label></td>
      <td><input dojoType="dijit.form.TextBox" class="input" readonly style="width:30px;" value="<?php echo htmlEncode($aff->rate);?>"/> %</td>
      <td>&nbsp;&nbsp;</td>
      <td><div id="replaceAffectationRate" name="replaceAffectationRate" value="<?php echo htmlEncode($aff->rate);?>" 
                 dojoType="dijit.form.NumberTextBox" style="width:30px" class="input required">
               </div> %</td>
    </tr>
    <tr>
      <td class="dialogLabel"><label><?php echo i18n('colStartDate');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label></td>
      <td><input value="<?php echo $aff->startDate;?>" dojoType="dijit.form.DateTextBox" class="input" readonly 
                 id="replaceAffectationExistingStartDate"
			           constraints="{datePattern:browserLocaleDateFormatJs}"  style="width:100px" /></td>
      <td>&nbsp;&nbsp;</td>
      <?php $start="";
      if ($aff->endDate) $start=addWorkDaysToDate($aff->endDate, 2);
      else if ($aff->startDate) $start=$aff->startDate;
      else $start="";
      ?>
      <td><input value="<?php echo $start;?>" dojoType="dijit.form.DateTextBox" class="input"
                 id="replaceAffectationStartDate" name="replaceAffectationStartDate"
			           constraints="{datePattern:browserLocaleDateFormatJs}" style="width:100px" /></td>
    </tr>
    <tr>
      <td class="dialogLabel"><label><?php echo i18n('colEndDate');?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label></td>
      <td><input value="<?php echo $aff->endDate;?>" dojoType="dijit.form.DateTextBox" class="input" readonly 
                 id="replaceAffectationExistingEndDate"
			           constraints="{datePattern:browserLocaleDateFormatJs}"  style="width:100px" /></td>
      <td>&nbsp;&nbsp;</td>
      <td><input value="" dojoType="dijit.form.DateTextBox" class="input"
                 id="replaceAffectationEndDate" name="replaceAffectationEndDate"
			           constraints="{datePattern:browserLocaleDateFormatJs}" style="width:100px" /></td>
    </tr>
    <tr><td colspan="4">&nbsp;</td></tr>
    <tr>
      <td colspan="4" align="center">
        <button class="mediumTextButton"  dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogReplaceAffectation').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton"  id="dialogReplaceAffectationSubmit" dojoType="dijit.form.Button" type="submit" onclick="protectDblClick(this);replaceAffectationSave();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
  </form>
</div>
<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2016 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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
include_once ("../tool/projeqtor.php");
$keyDownEventScript=NumberFormatter52::getKeyDownEvent();
$mode = RequestHandler::getValue('mode',false,null);
$idResource=RequestHandler::getValue('idResource',false,null);
$idAffectation=RequestHandler::getValue('id',false,null);
?>
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='affectationResourceTeamForm' name='affectationResourceTeamForm' onSubmit="return false;">
        <input id="idResourceTeam" name="idResourceTeam" type="hidden" value="<?php echo $idResource;?>" />
        <input id="mode" name="mode" type="hidden" value="<?php echo $mode;?>" />
        <input id="idAffectation" name="idAffectation" type="hidden" value="<?php echo $idAffectation;?>" />
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="affectationResource" ><?php echo i18n("colIdResource") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
               <?php echo autoOpenFilteringSelect();?>
                id="affectationResourceTeam" name="affectationResourceTeam" 
                class="input required" required=required >
                <?php 
                      htmlDrawOptionForReference('idResource',null, null,false);
                ?>
               </select> 
             </td>
             <td style="vertical-align: top">
                 <button id="affectationDetailButton2" dojoType="dijit.form.Button" showlabel="false"
                   title="<?php echo i18n('showDetail')?>"
                   iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                   <script type="dojo/connect" event="onClick" args="evt">
                    var canCreate=("<?php echo securityGetAccessRightYesNo('menuResource','create');?>"=="YES")?1:0;
                    showDetail('affectationResourceTeam', canCreate , 'Resource', false);
                   </script>
                 </button>
               </td>     
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="affectationRate" ><?php echo i18n("colRate");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="affectationRateResourceTeam" name="affectationRateResourceTeam" value="<?php echo '100'; ?>" 
                 dojoType="dijit.form.NumberTextBox"
                 constraints="{min:0,max:100}" 
                 style="width:100px" class="input"
                 hasDownArrow="true"
               >
               <?php echo $keyDownEventScript;?>
               </div>
             </td>    
           </tr>
           <tr>
             <td colspan="2">
               <table>
                 <tr>
                   <td class="dialogLabel" >
                     <label for="affectationStartDate" ><?php echo i18n("colStartDate");?>&nbsp;:&nbsp;</label>
                   </td>
                   <td>
                     <input id="affectationStartDateResourceTeam" name="affectationStartDateResourceTeam" value=""  
			                 dojoType="dijit.form.DateTextBox" 
			                 constraints="{datePattern:browserLocaleDateFormatJs}"
                       onChange=" var end=dijit.byId('affectationEndDateResourceTeam');end.set('dropDownDefaultValue',this.value);
                       var start = dijit.byId('affectationStartDateResourceTeam').get('value');end.constraints.min=start;"
			                 style="width:100px" />
                   </td>
                   <td class="dialogLabel" >
                     <label for="affectationEndDate" ><?php echo i18n("colEndDate");?>&nbsp;:&nbsp;</label>
                   </td>
                   <td>
                   <input id="affectationEndDateResourceTeam" name="affectationEndDateResourceTeam" value=""  
		                 dojoType="dijit.form.DateTextBox" 
		                 constraints="{datePattern:browserLocaleDateFormatJs}"
		                 style="width:100px" />
                   </td>
                 </tr>
               </table>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="affectationDescription" ><?php echo i18n("colDescription");?>&nbsp;:&nbsp;</label>
             </td>
             <td> 
               <textarea dojoType="dijit.form.Textarea" 
                id="affectationDescriptionResourceTeam" name="affectationDescriptionResourceTeam"
                style="width:400px;"
                maxlength="4000"
                class="input"></textarea>   
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="affectationIdle" ><?php echo i18n("colIdle");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="affectationIdleResourceTeam" name="affectationIdleResourceTeam"
                 dojoType="dijit.form.CheckBox" type="checkbox" >
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
        <input type="hidden" id="affectationResourceTeamAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogAffectationResourceTeam').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogAffectationResourceTeamSubmit" onclick="protectDblClick(this);saveAffectationResourceTeam();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>

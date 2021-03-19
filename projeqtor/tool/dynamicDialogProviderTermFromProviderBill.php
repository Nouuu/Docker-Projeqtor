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
$providerBillId = RequestHandler::getId('providerBillId');
$provBill = new ProviderBill($providerBillId);
$obj=new ProviderTerm();
$critFld=array();
$critVal=array();
$critFld[] ='idProviderBill';
$critVal[] = null;
$critFld[] ='idProject';
$critVal[] = $provBill->idProject;
if($provBill->taxPct > 0 ){
  $critFld[]='taxPct';
  $critVal[]=$provBill->taxPct;
}

?>
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='providerTermFromProviderBillForm' name='providerTermFromProviderBillForm' onSubmit="return false;">
         <input type="hidden" id="ProviderBillId" name="ProviderBillId" value="<?php echo $providerBillId ;?>" />
	         <table>
	            <tr>
             <td class="dialogLabel"  >
               <label for="linkRef2TypeProviderTerm" ><?php echo i18n("colIdProviderTerm") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <div id="linkProviderTermDiv">
                 <select xdojoType="dijit.form.FilteringSelect" id="linkProviderTerm" name="linkProviderTerm[]"
                 <?php echo autoOpenFilteringSelect();?>
                  size="14" multiple class="selectList" 
                  ondblclick="saveProviderTermFromProviderBill();"
                  value="">
                   <?php htmlDrawOptionForReference('idProviderTerm', null, $obj, true,$critFld,$critVal);?>
                 </select>
               </div>
             </td>
             <td style="vertical-align:top">
               <button id="providerTermDetailButton" dojoType="dijit.form.Button" showlabel="false"
                 title="<?php echo i18n('showDetail')?>"
                 iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                 <script type="dojo/connect" event="onClick" args="evt">
                    showDetail('linkProviderTerm', false, 'ProviderTerm', true);
                 </script>
               </button>
             </td>
           </tr>
	         </table>
        </form>
      </td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr>
      <td align="center">
        <input type="hidden" id="ProviderTermFromProviderBillAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogProviderTermFromProviderBill').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogProviderTermFromProviderBillSubmit" onclick="protectDblClick(this);saveProviderTermFromProviderBill();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>

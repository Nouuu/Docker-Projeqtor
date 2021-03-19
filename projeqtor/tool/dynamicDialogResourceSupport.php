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
$idResource=RequestHandler::getValue('idResource');
$idSupport=RequestHandler::getId('idSupport');
$supportRate = 100;
$mode = '';
$description = '';
if($idSupport){
  $sup = new ResourceSupport($idSupport);
  $supportRate = $sup->rate;
  $idResource = $sup->idSupport;
  $description = $sup->description;
  $mode = 'edit';
}
?>
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='resourceSupportForm' name='resourceSupportForm' onSubmit="return false;">
        <input id="idResource" name="idResource" type="hidden" value="<?php echo $idResource;?>" />
        <input id="idSupport" name="idSupport" type="hidden" value="<?php echo $idSupport;?>" />
         <table>
           <tr>
             <td class="dialogLabel" >
               <label for="resourceSupport" style="white-space:nowrap;width:200px;"><?php echo i18n("colIdResourceSupport");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
            <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" 
                  name="resourceSupport" id="resourceSupport"
                  <?php if ($idSupport) echo ' readonly '?>
                  <?php echo autoOpenFilteringSelect();?>
                  value="<?php if(sessionValueExists('resourceSupport')){
                                $resourceSupport = getSessionValue('resourceSupport');
                                echo $resourceSupport;
                               }else{
                                 echo $idResource;
                               }
                               ?>">
                  <script type="dojo/method" event="onChange" >
                    saveDataToSession("resourceSupport",this.value,false);
                  </script>
                  <?php 
                   $specific='imputation';
                   include '../tool/drawResourceListForSpecificAccess.php';?>  
                </select>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="SupportingRate" style="white-space:nowrap;width:200px;"><?php echo i18n("colRate");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <div id="SupportingRate" name="SupportingRate" value="<?php echo $supportRate;?>" 
                 dojoType="dijit.form.NumberTextBox"  constraints="{min:0.01,max:100}"
                 style="width:100px" class="input required" required
                 hasDownArrow="true">
               <?php echo $keyDownEventScript;?>
               </div>
             </td>    
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="resourceSupportDescription" style="white-space:nowrap;width:200px;"><?php echo i18n("colDescription");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td> 
               <textarea dojoType="dijit.form.Textarea"  style="width:411px"
                id="resourceSupportDescription" name="resourceSupportDescription"
                maxlength="4000"
                class="input"><?php echo $description;?></textarea>   
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogResourceSupport').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogResourceSupportSubmit" onclick="protectDblClick(this);saveResourceSupport('<?php echo $mode;?>');return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
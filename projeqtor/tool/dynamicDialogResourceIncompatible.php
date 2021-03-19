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
?>
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='resourceIncompatibleForm' name='resourceIncompatibleForm' onSubmit="return false;">
        <input id="idResource" name="idResource" type="hidden" value="<?php echo $idResource;?>" />
         <table>
           <tr>
             <td class="dialogLabel" >
               <label for="resourceIncompatible" style="white-space:nowrap;width:200px;"><?php echo i18n("colIdResourceIncompatible");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
            <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" 
                  name="resourceIncompatible" id="resourceIncompatible"
                  <?php echo autoOpenFilteringSelect();?>
                  value="<?php if(sessionValueExists('resourceIncompatible')){
                                $resourceIncompatible = getSessionValue('resourceIncompatible');
                                echo $resourceIncompatible;
                               }else{
                                  echo $idResource;
                                }
                               ?>">
                  <script type="dojo/method" event="onChange" >
                    saveDataToSession("resourceIncompatible",this.value,false);
                  </script>
                  <?php 
                   $specific='imputation';
                   include '../tool/drawResourceListForSpecificAccess.php';?>  
                </select>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="resourceIncompatibleDescription" style="white-space:nowrap;width:200px;"><?php echo i18n("colDescription");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td> 
               <textarea dojoType="dijit.form.Textarea" style="width:411px"
                id="resourceIncompatibleDescription" name="resourceIncompatibleDescription"
                maxlength="4000"
                class="input"></textarea>   
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogResourceIncompatible').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogResourceIncompatibleSubmit" onclick="protectDblClick(this);saveResourceIncompatible();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>

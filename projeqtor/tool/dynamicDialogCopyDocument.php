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

$document=RequestHandler::getClass('objectClass');
$objectId = RequestHandler::getId('objectId');
$obj=new Document($objectId);

?>
  <table>
  <tr>
      <td>
        <form dojoType="dijit.form.Form" id='copyDocumentForm' name='copyDocumentForm' onSubmit="return false;">
            
             <input id="copyId" name="copyId" type="hidden" value="<?php echo $objectId?>" /> 
             <table>
                <tr>
                  <td class="dialogLabel"  >
                    <label for="copyToName" ><?php echo i18n("copyToName") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
                  </td>
                    <td colspan=2> 
                       <div id="copyToNameDoc" name="copyToNameDoc" dojoType="dijit.form.ValidationTextBox"
                        required="required"
                        style="width: 400px;"
                        trim="true" maxlength="100" class="input"
                        value="<?php echo str_replace('"', '&quot;', $obj->name);?>">
                       </div>    
                   </td> 
                </tr>
                <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr>
                  <td class="dialogLabel"  >
                    <label for="copyToType" ><?php echo i18n("alsoCopyDocument") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
                  </td>
                  <td> <input type="radio" data-dojo-type="dijit/form/RadioButton" name="copyOption" id="copyOption1"  checked value="none"/> </td> 
                  <td> <label style="text-align:left;" for="copyOption1"><?php echo i18n("noneVersion");?></label> </td>
                 </tr>
                   <td></td>
                   <td>  <input type="radio" data-dojo-type="dijit/form/RadioButton" name="copyOption" id="copyOption2"   value="lastVersion"/> </td>
                   <td> <label style="text-align:left;" for="copyOption2"><?php echo i18n("lastVersion");?></label>  </td>   
                 <tr>
                 </tr>
                   <td></td>
                   <td>  <input type="radio" data-dojo-type="dijit/form/RadioButton" name="copyOption" id="copyOption3"   value="lastVersionRef"/> </td>
                   <td> <label style="text-align:left; width:300px;" for="copyOption3"><?php echo i18n("lastVersionRef");?></label>  </td>   
                 <tr>
                   <td></td>
                   <td> <input type="radio" data-dojo-type="dijit/form/RadioButton" name="copyOption" id="copyOption4"   value="allVersion"/> </td>
                   <td> <label style="text-align:left;" for="copyOption4"><?php echo i18n("allVersion");?></label> </td>
                 </tr>
                 
      
              
          <tr><td>&nbsp;</td><td >&nbsp;</td></tr>
             
        </table>
       </form>
       </td> 
      </tr> 
       
      <tr>
      <td align="center">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogCopyDocument').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogCopyDocumentSubmit" onclick="protectDblClick(this);copyDocumentToSubmit();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>  
  </table>

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

scriptLog('dynamicDialogConfirmDisapprove.php');

$approverId=RequestHandler::getId('approverId');
?>
<form dojoType="dijit.form.Form" id='confirmDisapproveForm' name='confirmDisapproveForm' onSubmit="return false;">
  <table>
    <tr>
      <td class="dialogLabel">
       <label style="white-space:nowrap;padding-right: 5px;" for="disapproveDescription" ><?php echo i18n("colDisapproveDescription");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;&nbsp;</label>
      </td>
      <td> 
       <textarea dojoType="dijit.form.Textarea" 
        id="disapproveDescription" name="disapproveDescription"
        style="width: 350px;"
        maxlength="400"
        class="input" oninput="enableConfirmDisapproveSubmit();"></textarea>   
      </td>
    </tr>
    <tr>
     <br/>
    </tr>
    <tr>
      <td colspan="2" align="center">
        <input type="hidden" id="dialogConfirmDisapproveAction">
        <button dojoType="dijit.form.Button" type="button" id="dialogConfirmDisapproveCancel" onclick="dijit.byId('dialogConfirmDisapprove').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button id="dialogConfirmDisapproveSubmit" dojoType="dijit.form.Button" type="submit" disabled onclick="protectDblClick(this);approveItem('<?php echo $approverId;?>', 'disapproved');dijit.byId('dialogConfirmDisapprove').hide();">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</form>
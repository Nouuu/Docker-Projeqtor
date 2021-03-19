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

scriptLog('dynamicDialogAttachment.php');
$isIE=false;
if (array_key_exists('isIE',$_REQUEST)) {
	$isIE=$_REQUEST['isIE'];
} 
?>
  <form id='attachmentForm' name='attachmentForm' 
  ENCTYPE="multipart/form-data" method="POST"
<?php if ($isIE and $isIE<=9) {?>
  action="../tool/saveAttachment.php?isIE=<?php echo ($isIE?$isIE:'');?>"
  target="resultPost"
  onSubmit="return saveAttachment();"
<?php }?> 
  >
    <input id="attachmentId" name="attachmentId" type="hidden" value="" />
    <input id="attachmentRefType" name="attachmentRefType" type="hidden" value="" />
    <input id="attachmentRefId" name="attachmentRefId" type="hidden" value="" />
    <input id="attachmentType" name="attachmentType" type="hidden" value="" />
    <div id="dialogAttachmentFileDiv">
      <table>
        <tr height="30px">
          <td class="dialogLabel" style="vertical:align:top">
           <label for="attachmentFile" ><?php echo i18n("colFile");?>&nbsp;<?php if (! isNewGui()) echo ':';?>&nbsp;</label>
          </td>
          <td style="position:relative">
           <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Parameter::getGlobalParameter('paramAttachmentMaxSize');?>" />
          <?php  if (! isHtml5()) {?>
           <input MAX_FILE_SIZE="<?php echo Parameter::getGlobalParameter('paramAttachmentMaxSize');?>"
            dojoType="dojox.form.FileInput" type="file"
            name="attachmentFile" id="attachmentFile"
            cancelText="<?php echo i18n("buttonReset");?>"
            label="<?php echo i18n("buttonBrowse");?>"
            title="<?php echo i18n("helpSelectFile");?>" />
          <?php } else {?>
          <span id="attachmentFileDropArea" >  
           <input MAX_FILE_SIZE="<?php echo Parameter::getGlobalParameter('paramAttachmentMaxSize');?>"
            dojoType="dojox.form.Uploader" type="file" 
            url="../tool/saveAttachment.php"
            <?php if (! $isIE) {?>
            style="padding:0px;margin:0px;<?php echo (isNewGui())?'width:350px':'z-index: 50;width:340px; border: 3px dotted #EEEEEE;';?>"
            <?php } else {?>
            style="overflow: hidden; border: 0px"
            <?php }?>
            name="attachmentFile" id="attachmentFile" 
            cancelText="<?php echo i18n("buttonReset");?>"
            multiple="true" 
            uploadOnSelect="false"
            onBegin="saveAttachment();"
            onChange="changeAttachment(this.getFileList());"
            onError="dojo.style(dojo.byId('downloadProgress'), {display:'none'});"
            label="<?php echo i18n("buttonBrowse");?>"
            title="<?php echo i18n("helpSelectFile");?>"  />
            <span style="font-style:italic;position: absolute; z-index: 49; top: 8px; left: 120px; color: #AAAAAA; width:230px"><?php echo i18n("dragAndDrop");?></span>
            </span>
          <?php }?>
          
          <div style="font-style:italic;position: relative; left:10px; border-left: 2px solid #EEEEEE; padding-left:5px;" name="attachmentFileName" id="attachmentFileName"></div>     
          </td>
        </tr>
      </table>
    </div>
    <div id="dialogAttachmentLinkDiv">
      <table>
        <tr height="30px">
          <td class="dialogLabel" >
            <label for="attachmentLink" ><?php echo i18n("colHyperlink");?>&nbsp;<?php if (!isNewGui()) echo ':';?>&nbsp;</label>
          </td>
          <td>
            <div id="attachmentLink" name="attachmentLink" dojoType="dijit.form.ValidationTextBox"
               style="width: <?php echo (isNewGui())?'338':'350';?>px;"
               trim="true" maxlength="400" class="input"
               value="">
            </div>
          </td>
        </tr>
      </table>
    </div>
    <table>
      <tr>
        <td class="dialogLabel" >
         <label for="attachmentDescription" ><?php echo i18n("colDescription");?>&nbsp;<?php if (!isNewGui()) echo ':';?>&nbsp;</label>
        </td>
        <td> 
         <textarea dojoType="dijit.form.Textarea" 
          id="attachmentDescription" name="attachmentDescription"
          style="width: 350px;"
          maxlength="4000"
          class="input"></textarea>   
        </td>
      </tr>
      <tr><td colspan="2">
       <table width="100%"><tr height="25px">
            <td width="33%" class="smallTabLabel" >
              <label class="smallTabLabelRight" for="attachmentPrivacyPublic"><?php echo i18n('public');?>&nbsp;</label>
              <input type="radio" data-dojo-type="dijit/form/RadioButton" name="attachmentPrivacy" id="attachmentPrivacyPublic" value="1" />
            </td>
            <td width="34%" class="smallTabLabel" >
             <?php $res=new Resource(getSessionUser()->id);
                    $hasTeam=($res->id and $res->idTeam)?true:false;
              ?>
              <label class="smallTabLabelRight" for="attachmentPrivacyTeam"><?php echo i18n('team');?>&nbsp;</label>
              <input type="radio" data-dojo-type="dijit/form/RadioButton" name="attachmentPrivacy" id="attachmentPrivacyTeam" <?php if (!$hasTeam) echo ' disabled ';?>value="2" />
            </td>
            <td width="33%" class="smallTabLabel" >
              <label class="smallTabLabelRight" for="attachmentPrivacyPrivate"><?php echo i18n('private');?>&nbsp;</label>
              <input type="radio" data-dojo-type="dijit/form/RadioButton" name="attachmentPrivacy" id="attachmentPrivacyPrivate" value="3" />
            </td>
          </tr></table>
      </td></tr>
      <tr>
        <td colspan="2" align="center">
          <input type="hidden" id="dialogAttachmentAction">
          <button dojoType="dijit.form.Button" type="button" id="dialogAttachmentCancel" onclick="dijit.byId('dialogAttachment').hide();" class="mediumTextButton">
            <?php echo i18n("buttonCancel");?>
          </button>
          <button id="dialogAttachmentSubmit" dojoType="dijit.form.Button" type="submit" class="mediumTextButton"
          <?php if ($isIE and $isIE<=9) {?>onclick="protectDblClick(this);saveAttachment();"<?php }?> >
            <?php echo i18n("buttonOK");?>
          </button>
        </td>
      </tr>
      <tr>
        <td colspan="2" align="center">  
         <div style="display:none">
           <iframe name="resultPost" id="resultPost"></iframe>
         </div>
        </td>
      </tr>
    </table>
    </form>
    
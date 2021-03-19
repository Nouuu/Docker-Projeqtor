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
if (! array_key_exists('objectClass',$_REQUEST)) {
  throwError('Parameter objectClass not found in REQUEST');
}
$objectClass=$_REQUEST['objectClass'];
Security::checkValidClass($objectClass);

if (! array_key_exists('objectId',$_REQUEST)) {
  throwError('Parameter objectId not found in REQUEST');
}
$objectId=$_REQUEST['objectId'];
Security::checkValidId($objectId);

$reply=RequestHandler::getBoolean('reply');
$idParentNote=RequestHandler::getId('idParentNote');

$noteId=null;
if (array_key_exists('noteId',$_REQUEST)) {
  $noteId=$_REQUEST['noteId'];
  Security::checkValidId($noteId);
}

$subNotePrivacy = array();
$canChangeStatus = true;
$subHasTeam = false;

if ($noteId) {
  $note=new Note($noteId);
  if($reply){
  	$note->idNote=$idParentNote;
  }
  if($note->idNote){
    $idParentNote = $note->idNote;
  }
  if($note->idUser != getSessionUser()->id){
    $canChangeStatus = false;
  }
  $subNoteList = $note->getSqlElementsFromCriteria(array('idNote'=>$note->id));
  if(count($subNoteList) >0){
    foreach ($subNoteList as $id=>$obj){
      if($obj->idPrivacy == 2){
      	$subHasTeam = true;
      }
      if($note->idPrivacy == $obj->idPrivacy){
        $subNotePrivacy[$obj->idPrivacy] = $obj->idPrivacy;
      }
    }
  }
} else {
  $note=new Note();
  $note->refType=$objectClass;
  $note->refId=$objectId;
  $note->idPrivacy=1;
  if($reply){
    $note->idNote=$idParentNote;
  }
}
$parentNote = new Note($idParentNote);
$detailHeight=600;
$detailWidth=1010;
if (sessionValueExists('screenWidth') and getSessionValue('screenWidth')) {
  $detailWidth = round(getSessionValue('screenWidth') * 0.60);
}
if (sessionValueExists('screenHeight')) {
  $detailHeight=round(getSessionValue('screenHeight')*0.60);
}
$privacy=($note->id)?$note->idPrivacy:$parentNote->idPrivacy;
if (!$privacy) $privacy=1;
?>
<div >
  <table style="width:100%;">
    <tr><td>
      <div <?php if (!$noteId) echo 'style="padding-bottom:7px"';?> id="dialogNotePredefinedDiv" dojoType="dijit.layout.ContentPane" region="center">
      <?php if (!$noteId) include "../tool/dynamicListPredefinedText.php";?>
      </div></td></tr>
    <tr>
      <td>
       <form id='noteForm' name='noteForm' onSubmit="return false;" >
         <input id="noteId" name="noteId" type="hidden" value="<?php echo $note->id;?>" />
         <input id="noteRefType" name="noteRefType" type="hidden" value="<?php echo $note->refType;?>" />
         <input id="noteRefId" name="noteRefId" type="hidden" value="<?php echo $note->refId;?>" />
         <input id="noteIdParent" name="noteIdParent" type="hidden" value="<?php echo $note->idNote;?>" />
         <input id="noteReply" name="noteReply" type="hidden" value="<?php echo $reply;?>" />
         <input id="noteEditorType" name="noteEditorType" type="hidden" value="<?php echo getEditorType();?>" />
         <?php if (getEditorType()=="CK" or getEditorType()=="CKInline") {?> 
          <textarea style="width:<?php echo $detailWidth;?>px; height:<?php echo $detailHeight;?>px"
          name="noteNote" id="noteNote"><?php
          if (!isTextFieldHtmlFormatted($note->note)) {
          	echo formatPlainTextForHtmlEditing($note->note);
          } else {
          	echo htmlspecialchars($note->note);
          } ?></textarea>
        <?php } else if (getEditorType()=="text"){
        	if (isTextFieldHtmlFormatted($note->note)) {
          	$text=new Html2Text($note->note);
          	$val=$text->getText();
          } else {
            $val=str_replace(array("\n",'<br>','<br/>','<br />'),array("","\n","\n","\n"),$note->note);
          }?>
          <textarea dojoType="dijit.form.Textarea" 
          id="noteNote" name="noteNote"
          style="max-width:<?php echo $detailWidth;?>px;height:<?php echo $detailHeight;?>px;max-height:<?php echo $detailHeight;?>px"
          maxlength="4000"
          class="input"
          onClick="dijit.byId('noteNote').setAttribute('class','');"><?php echo $val;?></textarea>
        <?php } else {?>
          <textarea dojoType="dijit.form.Textarea" type="hidden"
           id="noteNote" name="noteNote"
           style="display:none;"><?php echo htmlspecialchars($note->note);?></textarea>    
           <div data-dojo-type="dijit.Editor" id="noteNoteEditor"
             data-dojo-props="onChange:function(){window.top.dojo.byId('noteNote').value=arguments[0];}
              ,plugins:['removeFormat','bold','italic','underline','|', 'indent', 'outdent', 'justifyLeft', 'justifyCenter', 
                        'justifyRight', 'justifyFull','|','insertOrderedList','insertUnorderedList','|']
              ,onKeyDown:function(event){window.top.onKeyDownFunction(event,'noteNoteEditor',this);}
              ,onBlur:function(event){window.top.editorBlur('noteNoteEditor',this);}
              ,extraPlugins:['dijit._editor.plugins.AlwaysShowToolbar','foreColor','hiliteColor']"
              style="color:#606060 !important; background:none;padding:3px 0px 3px 3px;margin-right:2px;width:<?php echo $detailWidth;?>px;overflow:auto;"
              class="input"><?php 
                if (!isTextFieldHtmlFormatted($note->note)) {
			          	echo formatPlainTextForHtmlEditing($note->note,'single');
			          } else {
			          	echo $note->note;
			          }?></div>
        <?php }?>
          <table width="100%"><tr height="25px">
            <td width="33%" class="smallTabLabel" >
              <label class="smallTabLabelRight" for="notePrivacyPublic"><?php echo i18n('public');?>&nbsp;</label>
              <input type="radio" data-dojo-type="dijit/form/RadioButton" name="notePrivacy" id="notePrivacyPublic" value="1" <?php if ($privacy==1) echo "checked"; if (!$canChangeStatus or $parentNote->idPrivacy >= 2) echo ' disabled ';?> />
            </td>
            <td width="34%" class="smallTabLabel" >
            <?php $res=new Resource(getSessionUser()->id);
                  $hasTeam=($res->id and $res->idTeam)?true:false;?>
              <label class="smallTabLabelRight" for="notePrivacyTeam"><?php echo i18n('team');?>&nbsp;</label>
              <input type="radio" data-dojo-type="dijit/form/RadioButton" name="notePrivacy" id="notePrivacyTeam" value="2" <?php if ($privacy==2) echo "checked"; if (!$canChangeStatus or !$hasTeam or ($privacy==1 and isset($subNotePrivacy['1'])) or $parentNote->idPrivacy == 3) echo ' disabled ';?> />
            </td>
            <td width="33%" class="smallTabLabel" >
              <label class="smallTabLabelRight" for="notePrivacyPrivate"><?php echo i18n('private');?>&nbsp;</label>
              <input type="radio" data-dojo-type="dijit/form/RadioButton" name="notePrivacy" id="notePrivacyPrivate" value="3" <?php if ($privacy==3) echo "checked";if (!$canChangeStatus or $subHasTeam or ($privacy==1 and isset($subNotePrivacy['1'])) or ($privacy==2 and isset($subNotePrivacy['2']))) echo ' disabled ';?> />
            </td>
          </tr></table>

       </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogNoteAction">
        <button class="mediumTextButton"  dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogNote').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton"  id="dialogNoteSubmit" dojoType="dijit.form.Button" type="submit" onclick="protectDblClick(this);saveNote();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>
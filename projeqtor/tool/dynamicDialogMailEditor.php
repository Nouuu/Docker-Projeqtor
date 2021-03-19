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

$testMessage = Parameter::getGlobalParameter("mailerTestMessage");

$detailHeight=600;
$detailWidth=1010;
if (sessionValueExists('screenWidth') and getSessionValue('screenWidth')) {
	$detailWidth = round(getSessionValue('screenWidth') * 0.60);
}
if (sessionValueExists('screenHeight')) {
	$detailHeight=round(getSessionValue('screenHeight')*0.60);
}
?>
<div >
  <table style="width:100%;">
    <tr>
      <td>
       <form id='mailForm' name='mailForm' onSubmit="return false;" >
         <input id="mailEditorType" name="mailEditorType" type="hidden" value="<?php echo getEditorType();?>" />
         <input id="codeParam" name="codeParam" type="hidden" value="<?php echo "toto";?>" />
         <?php if (getEditorType()=="CK" or getEditorType()=="CKInline") {?> 
          <textarea style="width:<?php echo $detailWidth;?>px; height:<?php echo $detailHeight;?>px"
          name="mailEditor" id="mailEditor"><?php
          if (!isTextFieldHtmlFormatted($testMessage)) {
          	echo $testMessage;
          } else {
          	echo htmlspecialchars($testMessage);
          } ?></textarea>
        <?php } else if (getEditorType()=="text"){
        	if (isTextFieldHtmlFormatted($testMessage)) {
          	$text=new Html2Text($testMessage);
          	$val=$text->getText();
          } else {
            $val=str_replace(array("\n",'<br>','<br/>','<br />'),array("","\n","\n","\n"),$text);
          }?>
          <textarea dojoType="dijit.form.Textarea" 
          id="mailEditor" name="mailEditor"
          style="max-width:<?php echo $detailWidth;?>px;height:<?php echo $detailHeight;?>px;max-height:<?php echo $detailHeight;?>px"
          maxlength="4000" class="input"
          onClick="dijit.byId('mailEditorType').setAttribute('class','');"><?php echo $val;?></textarea>
        <?php } else {?>
          <textarea dojoType="dijit.form.Textarea" type="hidden" id="mailEditor" name="mailEditor" style="display:none;">
            <?php echo htmlspecialchars($testMessage); ?></textarea>
             <div data-dojo-type="dijit.Editor" id="messageMailEditor"
             data-dojo-props="onChange:function(){window.top.dojo.byId('mailEditorType').value=arguments[0];}
             ,plugins:['removeFormat','bold','italic','underline','|', 'indent', 'outdent', 'justifyLeft', 'justifyCenter', 
             'justifyRight', 'justifyFull','|','insertOrderedList','insertUnorderedList','|']
             ,onKeyDown:function(event){window.top.onKeyDownFunction(event,'messageMailEditor',this);}
             ,onBlur:function(event){window.top.editorBlur('messageMailEditor',this);}
             ,extraPlugins:['dijit._editor.plugins.AlwaysShowToolbar','foreColor','hiliteColor']"
             style="color:#606060 !important; background:none;padding:3px 0px 3px 3px;margin-right:2px;width:<?php echo $detailWidth;?>px;overflow:auto;"
             class="input"><?php 
             if (!isTextFieldHtmlFormatted($testMessage)) {
			         echo formatPlainTextForHtmlEditing($testMessage,'single');
			       } else {
			         echo $testMessage;
			       }?></div>
     <?php }?>
       </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogMailAction">
        <button class="mediumTextButton"  dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogMailEditor').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton"  id="dialogMailEditorSubmit" dojoType="dijit.form.Button" type="submit" onclick="protectDblClick(this);saveMailMessage();">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>
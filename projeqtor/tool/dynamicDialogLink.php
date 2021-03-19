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
$class=RequestHandler::getClass('objectClass');
$id=RequestHandler::getId('objectId');
if ($class and $id) {
	$obj=new $class($id);
} else {
  $obj=null;
}

?>
<table>
    <tr>
      <td>
       <form id='linkForm' name='linkForm' onSubmit="return false;">
         <input id="linkFixedClass" name="linkFixedClass" type="hidden" value="" />
         <input id="linkId" name="linkId" type="hidden" value="" />
         <input id="linkRef1Type" name="linkRef1Type" type="hidden" value="" />
         <input id="linkRef1Id" name="linkRef1Id" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="linkRef2Type" ><?php echo i18n("linkType") ?>&nbsp;<?php echo (isNewGui())?'':':';?>&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" id="linkRef2Type" name="linkRef2Type" onchange="refreshLinkList();"
               <?php if (isNewGui()) {?>  style="width:388px"<?php }?> 
               <?php echo autoOpenFilteringSelect();?>
                class="input" value="">
                 <?php htmlDrawOptionForReference('idLinkable', null, $obj, true);?>
               </select>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel" >
               <label for="linkRef2Id" ><?php echo i18n("linkElement") ?>&nbsp;<?php echo (isNewGui())?'':':';?>&nbsp;</label>
             </td>
             <td>
               <table><tr><td>
               <div id="dialogLinkList" dojoType="dijit.layout.ContentPane" region="center">
                 <input id="linkRef2Id" name="linkRef2Id" type="hidden" value="" />
               </div>
               </td><td style="vertical-align: top">
               <button id="linkDetailButton" dojoType="dijit.form.Button" showlabel="false"
                 title="<?php echo i18n('showDetail')?>" class="notButton notButtonRounded"
                 iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui">
                 <script type="dojo/connect" event="onClick" args="evt">
                    showDetailLink();
                 </script>
               </button>
               </td></tr></table>
             </td>
           </tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           </table>
           <div id="linkDocumentVersionDiv" style="display:none">
           <table>
           <tr>
               <td class="dialogLabel" >
                   <label for="linkRef2Type" ><?php echo i18n("colIdVersion") ?>&nbsp;<?php echo (isNewGui())?'':':';?>&nbsp;</label>
               </td>
               <td>
                  <select dojoType="dijit.form.FilteringSelect" 
                  <?php echo autoOpenFilteringSelect();?>
                    id="linkDocumentVersion" name="linkDocumentVersion" 
                    onchange=""
                    class="input" value="" >
                  </select>
                  <?php if (0) { //Desactivated?>                
                  <img src="css/images/smallButtonAdd.png" onClick="addDocumentVersion();" title="<?php echo i18n('addDocumentVersion');?>" class="smallButton"/>
                  <?php }?>
               </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
         </div>
         <table>  
           <tr>
               <td class="dialogLabel" >
                   <label for="linkRef2Type" ><?php echo i18n("colComment") ?>&nbsp;<?php echo (isNewGui())?'':':';?>&nbsp;</label>
               </td>
               <td>
                   <textarea dojoType="dijit.form.Textarea"
                             id="linkComment" name="linkComment"
                             style="width: 400px;"
                             maxlength="4000"
                             class="input"></textarea>
               </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
   
            <td class="dialogLabel"  >
               
             </td>     
          <td>
            <?php if (isNewGui()) {?>
            <div  id="copyLinksofLinkedSwitch" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" 
          	   value="off" leftLabel="" rightLabel="" style="width:10px;position:relative; left:0px;top:2px;z-index:99;" >
          	  <script type="dojo/method" event="onStateChanged" >
	             dijit.byId("copyLinksofLinked").set("checked",(this.value=="on")?true:false);
	            </script>
          	</div>
          	<?php }?>
             <input dojoType="dijit.form.CheckBox" name="copyLinksofLinked" id="copyLinksofLinked" checked=false <?php if (isNewGui()) {?>style="display:none;"<?php }?>/>
             <label style="float:none" for="copyLinksofLinked" ><?php echo i18n("copyLinkFromOriginalElement") ?></label>
          </td>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>         
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogLinkAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogLink').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogLinkSubmit" onclick="protectDblClick(this);saveLink();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>

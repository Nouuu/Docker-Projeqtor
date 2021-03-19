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
$idProject=RequestHandler::getId('idProject',false,null);
$class=RequestHandler::getClass('objectClass',false,null);
$idResource=RequestHandler::getId('idResource',false,null);
$affectationIdTeam=RequestHandler::getId('affectationIdTeam',false,null);
$affectationIdOrganization=RequestHandler::getId('affectationIdOrganization',false,null);
$type=RequestHandler::getValue('type',false,null);
$mode = RequestHandler::getValue('mode',false,null);
$idAffectation = RequestHandler::getId('id',false,null);
$resource = new ResourceAll($idResource);
$affectation = new Affectation($idAffectation);
$project = new Project();
$proj=null;
if (sessionValueExists('project')){
  $proj=getSessionValue('project');
  if(strpos($proj, ",")){
  	$proj="*";
  }
}
if ($proj=="*" or !$proj){
  $proj=null;
}
$contact = new Contact($idResource);
$user = new User($idResource);
$obj=SqlElement::getCurrentObject(null,null,true,false) ;
$objTeam=($obj)?get_class($obj):'';
?>

  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='affectationForm' name='affectationForm' onSubmit="return false;">
         <input id="affectationId" name="affectationId" type="hidden" value="<?php echo $idAffectation;?>" />
         <input id="affectationIdTeam" name="affectationIdTeam" type="hidden" value="<?php echo $affectationIdTeam ;?>" />
         <input id="affectationIdOrganization" name="affectationIdOrganization" type="hidden" value="<?php echo $affectationIdOrganization ;?>" />
         <table>
           <tr>
             <td class="dialogLabel" >
               <label for="affectationProject" ><?php echo i18n("colIdProject") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
               <?php echo autoOpenFilteringSelect();?>
                id="affectationProject" name="affectationProject"  style="border-left:3px solid red !important;"
                value="<?php if($class=="Project"){echo $idProject;}else if($class!="Project" && $mode=="edit"){echo $affectation->idProject;}else{echo $proj;}?>" class="input" required="required" <?php echo ($class=="Project")?"readonly=readonly":"";?>>
                 <?php 
                 if($class=="Project"){
                   htmlDrawOptionForReference('idProject', $idProject, null, true);
                 } else {
                   htmlDrawOptionForReference('idProject', $proj,null,true);
                 }
                 ?>
               </select>
             </td>
           <?php if($class!="Project"){?>
               <td style="vertical-align: top">
                 <button id="affectationDetailButton" dojoType="dijit.form.Button" showlabel="false"
                   title="<?php echo i18n('showDetail')?>"
                   iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                   <script type="dojo/connect" event="onClick" args="evt">
                    var canCreate=("<?php echo securityGetAccessRightYesNo('menuProject','create');?>"=="YES")?1:0;
                    showDetail('affectationProject', canCreate , 'Project', false);
                   </script>
                 </button>
               </td>  
            <?php };?>  
           </tr>
           <tr>
             <td class="dialogLabel"  >
               <label for="affectationResource" ><?php 
                 if ($type=="Contact") echo i18n("colIdContact");
                 else if ($type=="User") echo i18n("colIdUser");
                 else echo i18n("colIdResource") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
               <?php echo autoOpenFilteringSelect();?>
                id="affectationResource" name="affectationResource" 
                onChange="affectationChangeResource();"
                class="input <?php echo ($objTeam=="Team" or $objTeam=="Organization")?'':'required';?>" value="<?php if($class=="Project" && $type=="Resource"){ echo $affectation->idResource;}else if($class=="Project" && $type=="Contact"){ echo $affectation->idContact;}else{ echo $idResource;}?>" 
                <?php echo ($objTeam=="Team" or $objTeam=="Organization")?"required=false":"";?> <?php echo ($class!="Project")?"readonly=readonly":"";?>>
                 <?php if ($type=="Contact") htmlDrawOptionForReference('idContact', $idResource, null, true);
                       else if ($type=="User") htmlDrawOptionForReference('idUser', $idResource, null, true);
                       else htmlDrawOptionForReference('idResourceAll', $idResource, null,true);
                 ?>
               </select> 
             </td>
             <?php if($class=="Project"){?>
               <td style="vertical-align: top">
                 <button id="affectationDetailButton" dojoType="dijit.form.Button" showlabel="false"
                   title="<?php echo i18n('showDetail')?>"
                   class="notButton notButtonRounded"
	                 iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui">
                   <script type="dojo/connect" event="onClick" args="evt">
                    var canCreate=("<?php echo securityGetAccessRightYesNo('menuResource','create');?>"=="YES")?1:0;
                    <?php if ($type=="ResourceAll"){?>
                      showDetail('affectationResource', canCreate , 'Resource', false);
                    <?php } else if ($type=="Contact"){?>
                      showDetail('affectationResource', canCreate , 'Contact', false);
                    <?php } ?>
                   </script>
                 </button>
               </td>  
             <?php };?>           
           </tr>
           <tr id="affectationToProfile" name="affectationToProfile"  >
             <td class="dialogLabel" >
               <label for="affectationProfile" ><?php echo i18n("colIdProfile");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
               <?php echo autoOpenFilteringSelect();?>
                id="affectationProfile" name="affectationProfile" 
                class="input <?php echo ($objTeam=="Team" or $objTeam=="Organization")?'':'required';?>"  value="<?php 
                  if($mode=="edit"){ echo ($affectation->idProfile)?$affectation->idProfile:' ';}
                  else if($mode=="add" && $class=="Resource"){echo $resource->idProfile;}
                  else if($mode=="add" && $class=="Contact"){echo $contact->idProfile;}
                  else if($mode=="add" && $class=="User"){echo $user->idProfile;}?>" 
               <?php echo ($objTeam=="Team" or $objTeam=="Organization")?"required=false":"";?> <?php echo ($objTeam=="ResourceTeam")?"required=false":"";?> <?php echo ($objTeam=="Team" or $objTeam=="Organization")?"readonly=readonly":"";?>>
                 <?php htmlDrawOptionForReference('idProfile', null, $obj, true);?>
               </select>
               </div>
             </td>    
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="affectationRate" ><?php echo i18n("colRate");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <div id="affectationRate" name="affectationRate" value="<?php echo $affectation->rate;?>" 
                 dojoType="dijit.form.NumberTextBox"  constraints="{min:0,max:100}"
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
                     <label for="affectationStartDate" ><?php echo i18n("colStartDate");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
                   </td>
                   <td>
                     <input id="affectationStartDate" name="affectationStartDate" value=""  
			                 dojoType="dijit.form.DateTextBox" 
			                 constraints="{datePattern:browserLocaleDateFormatJs}"
                       onChange=" var end=dijit.byId('affectationEndDate');end.set('dropDownDefaultValue',this.value);
                       var start = dijit.byId('affectationStartDate').get('value');end.constraints.min=start;"
			                 style="width:100px" />
                   </td>
                   <td class="dialogLabel" >
                     <label for="affectationEndDate" ><?php echo i18n("colEndDate");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
                   </td>
                   <td>
                   <input id="affectationEndDate" name="affectationEndDate" value=""  
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
               <label for="affectationDescription" ><?php echo i18n("colDescription");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td> 
               <textarea dojoType="dijit.form.Textarea" 
                id="affectationDescription" name="affectationDescription"
                style="width:415px;"
                maxlength="4000"
                class="input"></textarea>   
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="affectationIdle" ><?php echo i18n("colIdle");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <div id="affectationIdle" name="affectationIdle"
                 dojoType="dijit.form.CheckBox" type="checkbox" <?php echo ($objTeam=="Team" or $objTeam=="Organization")?"readonly=readonly":"";?>>
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
        <input type="hidden" id="affectationAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogAffectation').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogAffectationSubmit" onclick="protectDblClick(this);saveAffectation();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>

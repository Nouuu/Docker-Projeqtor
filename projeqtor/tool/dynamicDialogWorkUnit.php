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
$mode = RequestHandler::getValue('mode',false,null);
$id = RequestHandler::getId('id');
$idCatalog=RequestHandler::getValue('idCatalog',false,null);
$workUnits = new WorkUnit($id);
$detailHeight=50;
$detailWidth=800;
$complexity = new Complexity();
$listComplexity = $complexity->getSqlElementsFromCriteria(array('idCatalogUO'=>$idCatalog));
$nbComplexities = count($listComplexity);
if(!$nbComplexities)$nbComplexities=1;
$tdWitdh = (85/$nbComplexities);
if($tdWitdh>10)$tdWitdh=10;
$tabCompValues = array();
?>
<div>
  <table style="width:100%;">
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='workUnitForm' name='workUnitForm' onSubmit="return false;">
        <input id="idCatalog" name="idCatalog" type="hidden" value="<?php echo $idCatalog;?>" />
        <input id="mode" name="mode" type="hidden" value="<?php echo $mode;?>" />
         <input id="idWorkUnit" name="idWorkUnit" type="hidden" value="<?php echo $id;?>" />
         <table style="width:1000px;">
         <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          <tr>
             <td style="width:100px;" class="dialogLabel" >
               <label for="WUReference" ><?php echo i18n("colReference");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <textarea dojoType="dijit.form.Textarea" 
                id="WUReferences" name="WUReferences"
                style="width:852px;border-left: 3px solid rgb(255, 0, 0);"
                maxlength="4000" 
                class="input"><?php echo htmlspecialchars($workUnits->reference);?></textarea>   
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td style="vertical-align: top;width:100px;" class="dialogLabel" >
               <label for="WUDescription" ><?php echo i18n("colDescription");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
					     <input id="WUDescription" name="WUDescription" type="hidden" value=""/>
                    <textarea  style="width:<?php echo $detailWidth;?>px; height:<?php echo $detailHeight;?>px"
                               name="WUDescriptions" id="WUDescriptions"><?php echo htmlspecialchars($workUnits->description);?></textarea>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td style="width:100px;vertical-align: top;" class="dialogLabel" >
               <label for="WUIncoming" ><?php echo i18n("colIncoming");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
              <input id="WUIncoming" name="WUIncoming" type="hidden" value=""/>
                    <textarea  style="width:<?php echo $detailWidth;?>px; height:<?php echo $detailHeight;?>px"
                    name="WUIncomings" id="WUIncomings"><?php echo htmlspecialchars($workUnits->entering);?></textarea>
             </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          <tr>
             <td style="width:100px;vertical-align: top;" class="dialogLabel" >
               <label for="WULivrable" ><?php echo i18n("colLivrable");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <input id="WULivrable" name="WULivrable" type="hidden" value=""/>
                    <textarea style="width:<?php echo $detailWidth;?>px; height:<?php echo $detailHeight;?>px"
                    name="WULivrables" id="WULivrables"><?php echo htmlspecialchars($workUnits->deliverable);?></textarea>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          <tr>
             <td  class="dialogLabel" >
               <label for="ValidityDateWU" ><?php echo i18n("colValidityDate");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <div id="ValidityDateWU" name="ValidityDateWU"
                dojoType="dijit.form.DateTextBox"  hasDownArrow="false"   
                constraints="{datePattern:browserLocaleDateFormatJs}"
                type="text" maxlength="10"  style="width:100px; text-align: center;" class="input" value="">
               </div>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr> <td></td><td>
           <table style="width:98%">
              <tr> 
                <td  style="width:15%" class="assignHeader"><?php echo i18n("colComplexity");?></td>
                <?php foreach ($listComplexity as $comp){ 
                  $actPl = new ActivityPlanningElement();
                        if($mode =='edit'){
                          $lstCompVal=SqlElement::getSingleSqlElementFromCriteria('ComplexityValues', array('idCatalogUO'=>$idCatalog,'idComplexity'=>$comp->id,'idWorkUnit'=>$id));
                          foreach ($lstCompVal as $idLib=>$val){
                            if($idLib=='charge')$tabCompValues[$comp->id]['charge'] = $val; 
                            if($idLib=='price')$tabCompValues[$comp->id]['price'] = $val;
                            if($idLib=='duration')$tabCompValues[$comp->id]['duration'] = $val;
                          }
                         }?>
                <td style="width:<?php echo $tdWitdh;?>%" class="assignHeader" > <?php echo $comp->name; ?> </td>
                <?php } ?>
              </tr>
              <tr>
                <td style="width:10%;text-align:center;" class="assignData" > <?php  echo i18n('charge').'  ('.Work::displayShortWorkUnit().')';?> </td>
                <?php foreach ($listComplexity as $comp){
                  $isReadOnly = "";
                  if($mode=='edit'){
                    $readOnly = $actPl->countSqlElementsFromCriteria(array('idComplexity'=>$comp->id,'idWorkUnit'=>$id));
                    if($readOnly)$isReadOnly= "readOnly" ;
                  }?>
                <td style="width:<?php echo $tdWitdh;?>%"  class="assignData">
                <input dojoType="dijit.form.NumberTextBox" <?php echo $isReadOnly; ?> id="charge<?php echo $comp->id;?>" name="charge<?php echo $comp->id;?>" type="text" style="padding:0 !important;margin:0 !important; height:100% !important;width:100% !important;" class="input"  value="<?php if($mode=='edit'){echo $tabCompValues[$comp->id]['charge'];} ?>" />
                </td>
                <?php } ?>
              </tr>
              <tr>
                <td style="width:10%;text-align:center;" class="assignData"> <?php echo i18n('price').'  ('.Parameter::getGlobalParameter('currency').')';?></td>
                <?php foreach ($listComplexity as $comp){ 
                  $isReadOnly = "";
                  if($mode=='edit'){
                    $readOnly = $actPl->countSqlElementsFromCriteria(array('idComplexity'=>$comp->id,'idWorkUnit'=>$id));
                    if($readOnly)$isReadOnly= "readOnly";
                  }?>
                <td style="width:<?php echo $tdWitdh;?>%" class="assignData">
                <input dojoType="dijit.form.NumberTextBox" <?php echo $isReadOnly; ?> id="price<?php echo $comp->id;?>" name="price<?php echo $comp->id;?>" type="text" style="padding:0 !important;margin:0 !important; height:100% !important;width:100% !important;" class="input"  value="<?php if($mode=='edit'){echo $tabCompValues[$comp->id]['price'];} ?>" />
                </td>
                <?php } ?>
              </tr>
              <tr>
                <td style="width:10%;text-align:center;" class="assignData"> <?php echo i18n('duration').'  ('.i18n('shortDay').')';?> </td>
                <?php foreach ($listComplexity as $comp){
                  $isReadOnly = "";
                  if($mode=='edit'){
                    $readOnly = $actPl->countSqlElementsFromCriteria(array('idComplexity'=>$comp->id,'idWorkUnit'=>$id));
                    if($readOnly)$isReadOnly= "readOnly";
                  }?>
                <td style="width:<?php echo $tdWitdh;?>%" class="assignData"> 
                <input dojoType="dijit.form.NumberTextBox" <?php echo $isReadOnly; ?> id="duration<?php echo $comp->id;?>" name="duration<?php echo $comp->id;?>" type="text" style="padding:0 !important;margin:0 !important; height:100% !important;width:100% !important;" class="input"  value="<?php if($mode=='edit'){echo $tabCompValues[$comp->id]['duration'];} ?>" />
                 </td>
                <?php } ?>
             </tr>
            </table>
            </td>
            </tr>
            </table>
        </form>
      </td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr>
      <td align="center">
        <input type="hidden" id="workUnitAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogWorkUnit').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogWorkUnitSubmit" onclick="protectDblClick(this);saveWorkUnit();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
  </table>
</div>
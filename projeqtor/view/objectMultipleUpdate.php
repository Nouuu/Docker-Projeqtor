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

/* ============================================================================
 * Presents the action buttons of an object.
 * 
 */ 
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/objectMultipleUpdate.php');

  $displayWidth='98%';
  $spaceWidth='33%';
  $helpWidth=25;
  $labelWidth=250;
  if (isNewGui()) {
    $labelWidth=300;
    $helpWidth=60;
  }
  $labelSelect=i18n("selectedItemsCount");
  $layout=(Parameter::getUserParameter('paramScreen')=='left')?'vertical':'horizontal';
  if (array_key_exists('destinationWidth',$_REQUEST)) {
    $width=$_REQUEST['destinationWidth'];
    if ($layout=='vertical') $displayWidth=intval($width)-30;
    else $displayWidth=floor($width*0.6);
    if ($displayWidth<650) $labelSelect=i18n("selectedItemsCountShort");
    //if (isNewGui()) $labelWidth=max(300,($displayWidth/2)-15);
    $fieldWidth=$displayWidth-$labelWidth-15-15;
    $spaceWidth=$displayWidth-775;
    if ($spaceWidth<0) $spaceWidth=0;
    //else $spaceWidth=$spaceWidth.'px';
    $spaceWidth=$spaceWidth.'px';
    if ($fieldWidth<150) {
      $labelWidth=$labelWidth-150+$fieldWidth;
      $fieldWidth=150;
    }
  } 
  
  $objectClass=$_REQUEST['objectClass'];
  Security::checkValidClass($objectClass);
  $obj=new $objectClass();

?>
<div dojoType="dijit.layout.BorderContainer" class="background" id="objetMultipleUpdate">
  <input hidden id="showActicityStream" name="showActicityStream" value="<?php echo getSessionValue('showActicityStream');?>">
  <div id="buttonDiv" dojoType="dijit.layout.ContentPane" region="top">
    <div dojoType="dijit.layout.BorderContainer" >
      <div id="buttonDivContainer" dojoType="dijit.layout.ContentPane" region="left">
        <table width="100%" class="listTitle" >
          <tr valign="middle" height="32px"> 
            <td width="50px" align="center" class="iconHighlight" >
<!--               //krowry debug doesn't work in size 22 -->
            <?php if (isNewGui()) {?>
             <div style="position: absolute; top: 3px; left: 5px">
              <?php echo formatIcon($objectClass, 22,null,false);?> 
              </div>
              <div style="position: absolute; top: 10px; left: 20px">
              <?php echo formatIcon($objectClass, 22,null,false);?> 
              </div>
            <?php } else {?>
              <div style="position: absolute; top: 0px; left: 2px">
              <?php echo formatIcon($objectClass, 22,null,false);?> 
              </div>
              <div style="position: absolute; top: 5px; left: 14px">
              <?php echo formatIcon($objectClass, 22,null,false);?> 
              </div>
              <div style="position: absolute; top: 10px; left: 26px">
              <?php echo formatIcon($objectClass, 22,null,false);?> 
              </div>    
            <?php }?>
            </td>
            <td valign="middle"><span class="title"><?php echo i18n('labelMultipleMode');?></span></td>
            <td width="50px">&nbsp;</td>
            <td style="white-space;nowrap"">
              <?php echo $labelSelect;?> :
              <input type="text" id="selectedCount"
                style="font-weight: bold;background: transparent;border: 0px;width:10%;min-width:35px;<?php if (! isNewGui()) echo 'color: white;';?>" 
                value="0" readOnly />
            </td>
            <td width="15px">&nbsp;</td>
            <td><span class="nobr">
            <button id="selectAllButton" dojoType="dijit.form.Button" showlabel="false" 
               title="<?php echo i18n('buttonSelectAll');?>"
               iconClass="iconSelectAll" class="detailButton" >
                <script type="dojo/connect" event="onClick" args="evt">
                   selectAllRows('objectGrid');
                   updateSelectedCountMultiple();
                </script>
              </button>    
              <button id="unselectAllButton" dojoType="dijit.form.Button" showlabel="false" 
               title="<?php echo i18n('buttonUnselectAll');?>"
               iconClass="iconUnselectAll" class="detailButton" >
                <script type="dojo/connect" event="onClick" args="evt">
                   unselectAllRows('objectGrid');
                   updateSelectedCountMultiple();
                </script>
              </button>    
            </span></td>
            <td style="width:<?php echo $spaceWidth;?>">&nbsp;</td>
            <td><span class="nobr">
             
              <button id="saveButtonMultiple" dojoType="dijit.form.Button" showlabel="false"
               title="<?php echo i18n('buttonSaveMultiple');?>"
               iconClass="dijitButtonIcon dijitButtonIconSave" class="detailButton" >
                <script type="dojo/connect" event="onClick" args="evt">
                  saveMultipleUpdateMode("<?php echo $objectClass;?>");  
                </script>
              </button>
              <button id="deleteButtonMultiple" dojoType="dijit.form.Button" showlabel="false"
               title="<?php echo i18n('buttonDeleteMultiple');?>"
               iconClass="dijitButtonIcon dijitButtonIconDelete" class="detailButton" >
                <script type="dojo/connect" event="onClick" args="evt">
                  deleteMultipleUpdateMode("<?php echo $objectClass;?>");  
                </script>
              </button>
              <?php 
              $paramRightDiv=Parameter::getUserParameter('paramRightDiv');
              $showActivityStream=false;
              $currentScreen=getSessionValue('currentScreen');
              if ($currentScreen=='Object') $currentScreen=$objectClass;
              if($paramRightDiv=="bottom"){
                $activityStreamSize=getHeightLaoutActivityStream($currentScreen);
                $activityStreamDefaultSize=getDefaultLayoutSize('contentPaneRightDetailDivHeight');
              }else{
                $activityStreamSize=getWidthLayoutActivityStream($currentScreen);
                $activityStreamDefaultSize=getDefaultLayoutSize('contentPaneRightDetailDivWidth');
              }
              
              ?>
              <button id="undoButtonMultiple" dojoType="dijit.form.Button" showlabel="false"
               title="<?php echo i18n('buttonQuitMultiple');?>"
               iconClass="dijitButtonIcon dijitButtonIconExit" class="detailButton" >
                <script type="dojo/connect" event="onClick" args="evt">
                  if(dojo.byId('showActicityStream').value=='show'){
                    saveDataToSession('showActicityStream','hide');
                    hideStreamMode('true','<?php echo $paramRightDiv;?>','<?php echo $activityStreamDefaultSize;?>',false);
                  }
                  dojo.byId("undoButtonMultiple").blur();
                  endMultipleUpdateMode("<?php echo $objectClass;?>");
                </script>
              </button>    
            </span></td>

          </tr>
        </table>

    </div>
   <div id="detailBarShow" class="dijitAccordionTitle" onMouseover="hideList('mouse');" onClick="hideList('click');">
     <div id="detailBarIcon" align="center">
   </div>
      </div>
      <div dojoType="dijit.layout.ContentPane" region="center" 
       style="z-index: 3; height: 35px; position: absolute !important; overflow: visible !important;">
      </div>
    </div>
  </div>
  <div dojoType="dijit.layout.ContentPane" region="center">
    <div dojoType="dijit.layout.BorderContainer" class="background">
      <div dojoType="dijit.layout.ContentPane" region="center" style="overflow-y: auto">
        <form dojoType="dijit.form.Form" id="objectFormMultiple" jsId="objectFormMultiple" 
          name="objectFormMultiple" encType="multipart/form-data" action="" method="">
          <script type="dojo/method" event="onSubmit">
            return false;        
          </script>
          <input type="hidden" id="selection" name="selection" value=""/>
     <?php $displayWidth=($labelWidth+$fieldWidth+15)."px"; 
     //if ($layout=='vertical') $displayWidth="100%";
     $collapsedList=Collapsed::getCollaspedList();
     $titlePane=get_class($obj)."_MultipleDescription";?>
     <br/>
     <div style="width: <?php  echo $displayWidth;?>;<?php if (1 or isNewGui()) echo 'margin-left: 15px;';?>" dojoType="dijit.TitlePane" 
     title="<?php echo i18n('sectionDescription');?>"
     open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
     id="<?php echo $titlePane;?>" 
     onHide="saveCollapsed('<?php echo $titlePane;?>');"
     onShow="saveExpanded('<?php echo $titlePane;?>');" >
          <table>
            <?php
      // Project
             if (isDisplayable($obj,'idProject')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeProject',array($obj->getColCaption('idProject')));?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idProject" name="idProject">
                 <?php htmlDrawOptionForReference('idProject', null, null, false);?>
                </select>
                <button id="projectButton_multiple" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                    showDetail("idProject",0); 
                  </script>
                </button>
              </td>
            </tr>
            <?php }
      // Type
            $type='id'.get_class($obj).'Type';
// MTY - LEAVE SYSTEM            
//            if (isDisplayable($obj,$type)) {
            if (isDisplayable($obj,$type) and get_class($obj)!="Leave") {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeType');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idType" name="idType">
                 <?php htmlDrawOptionForReference($type, null, null, false);?>
                </select>
                <button id="typeButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                    showDetail($type,0); 
                  </script>
                </button>
              </td>
            </tr>
            <?php }
      // Issuer
            if (isDisplayable($obj,'idUser')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeIssuer',array($obj->getColCaption('idUser')));?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idUser" name="idUser">
                 <?php htmlDrawOptionForReference('idUser', null, null, false);?>
                </select>
                <button id="userButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                    showDetail("idUser",0); 
                  </script>
                </button>
              </td>
            </tr>
             <?php }
      // Requestor
             if (isDisplayable($obj,'idContact')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeRequestor',array($obj->getColCaption('idContact')));?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idContact" name="idContact">
                 <?php htmlDrawOptionForReference('idContact', null, null, false);?>
                </select>
                <button id="contactButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                    showDetail("idContact",0); 
                  </script>
                </button>
              </td>
            </tr>
            <?php }
            // Customer
              if (isDisplayable($obj,'idClient')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeCustomer');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idClient" name="idClient">
                 <?php htmlDrawOptionForReference('idClient', null, null, false);?>
                </select>
                <button id="clientButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                                showDetail("idClient",0); 
                              </script>
                </button>
              </td>
            </tr>
             <?php }
      // Business Feature
             $paramDisplayBusinessFeature=Parameter::getGlobalParameter('displayBusinessFeature');
             if ( $paramDisplayBusinessFeature == "YES"){
             if (isDisplayable($obj,'idBusinessFeature')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeBusinessFeature');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idBusinessFeature" name="idBusinessFeature">
                 <?php htmlDrawOptionForReference('idBusinessFeature', null, null, false);?>
                </select>
                <button id="businessFeatureButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                                showDetail("idBusinessFeature",0); 
                              </script>
                </button>
              </td>
            </tr>
             <?php }}
       // fix planning, fixPerimeter, under construction
             $arrayCheckbox=array("fixPlanning","fixPerimeter","isUnderConstruction");
             foreach($arrayCheckbox as $checkField) {
             if(get_class($obj)=='Activity' and $checkField=='fixPlanning')continue;
             if (isDisplayable($obj,$checkField)) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeRequestor',array($obj->getColCaption($checkField)));?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="<?php echo $checkField;?>" name="<?php echo $checkField;?>">
                 <option value=""> </option>
                 <option value="ON"><?php echo i18n("checkBox");?></option>
                 <option value="OFF"><?php echo i18n("uncheckedBox");?></option>
                </select>
              </td>
            </tr>
            <?php }
                }
      // Description 
            if (isDisplayable($obj, 'description') ) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colAddToDescription');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <textarea dojoType="dijit.form.Textarea" name="description" id="description"
                 rows="2" style="width:<?php echo $fieldWidth;?>px;" maxlength="4000" maxSize="4" class="input" ></textarea>
              </td>
            </tr>
            <?php }?>
        </table>
     </div>
     <?php $titlePane=get_class($obj)."_MultipleResult";?>
     <br/>
     <div style="width: <?php echo $displayWidth;?>;<?php if (1 or isNewGui()) echo 'margin-left: 15px;';?>" dojoType="dijit.TitlePane" 
     title="<?php echo i18n('sectionTreatment');?>"
     open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
     id="<?php echo $titlePane;?>" 
     onHide="saveCollapsed('<?php echo $titlePane;?>');"
     onShow="saveExpanded('<?php echo $titlePane;?>');" >
          <table>
            <?php 
      // Status      
            if (isDisplayable($obj,'idActivity')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo (SqlElement::is_a($obj,'Ticket'))?i18n('colChangePlanningActivity'):i18n('colChangeParentActivity');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idActivity" name="idActivity">
                 <?php 
                 $critFld=null;
                 $critVal=null;
                 if (sessionValueExists('project') and getSessionValue('project') and getSessionValue('project')!='*' and strpos(getSessionValue('project'), ",") === null) {
                 	$critFld='idProject';
                 	$critVal=getSessionValue('project');
                 }
                 htmlDrawOptionForReference('idActivity', null, null, false,$critFld,$critVal);?>
                </select>
                <button id="activityButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                                showDetail("idActivity",0); 
                              </script>
                </button>
              </td>
            </tr>
            <?php } 
            if (isDisplayable($obj,'idStatus')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeStatus');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idStatus" name="idStatus">
                 <option value=''></option>
                 <?php 
                 $list=SqlList::getStatusList($objectClass);
                 foreach ($list as $idS=>$nameS) {
                 	 echo "<option value='$idS'>$nameS</option>";
                 }
                 ?>
                </select>
                <button id="statusButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                    showDetail("idStatus",0); 
                  </script>
                </button>
              </td>
            </tr>
            <?php }

          //gautier #asset
            if(property_exists($obj, 'warantyDurationM')){?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colWarantyDurationM');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <div dojoType="dijit.form.NumberTextBox" name="changeWarantyDurationM" id="changeWarantyDurationM"
                 style="width:60px;" maxlength="10" maxSize="4" class="input" ></div>
                 <?php echo i18n('shortMonth');?>
              </td>
            </tr>
            <?php }
            if(property_exists($obj, 'warantyEndDate')){?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeWarantyEndDate');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <div dojoType="dijit.form.DateTextBox" name="changeWarantyEndDate" id="changeWarantyEndDate"
                <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
										echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
									}?>
                 style="width:100px;" class="input" value="" ></div>
              </td>
            </tr>
            <?php }
            if(property_exists($obj, 'depreciationDurationY')){?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colDepreciationDurationY');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <div dojoType="dijit.form.NumberTextBox" name="changeDepreciationDurationY" id="changeDepreciationDurationY"
                 style="width:60px;" maxlength="10" maxSize="4" class="input" ></div>
                 <?php echo i18n('shortYear');?>
              </td>
            </tr>
            <?php }
            $currency=Parameter::getGlobalParameter('currency');
            $currencyPosition=Parameter::getGlobalParameter('currencyPosition');
            if(property_exists($obj, 'purchaseValueHTAmount')){?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colUntaxedAmount');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <?php if ($currencyPosition=='before') echo $currency;?>
                <div dojoType="dijit.form.NumberTextBox" name="changePurchaseValueHTAmount" id="changePurchaseValueHTAmount"
                 style="width:60px;" maxlength="10" maxSize="4" class="input" ></div>
                 <?php if ($currencyPosition=='after') echo $currency;?>
              </td>
            </tr>
           <?php }
           if(property_exists($obj, 'purchaseValueTTCAmount')){?>
             <tr class="detail">
               <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colFullAmount');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
               <td>
                 <?php if ($currencyPosition=='before') echo $currency;?>
                 <div dojoType="dijit.form.NumberTextBox" name="changePurchaseValueTTCAmount" id="changePurchaseValueTTCAmount"
                  style="width:60px;" maxlength="10" maxSize="4" class="input" ></div>
                  <?php if ($currencyPosition=='after') echo $currency;?>
               </td>
             </tr>
            <?php }
          if(property_exists($obj, 'needInsurance')){?>
          <tr class="detail">
            <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeNeedInsurance');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
            <td>
              <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
              <?php echo autoOpenFilteringSelect();?>
               id="changeNeedInsurance" name="changeNeedInsurance">
                <option value=""></option>
                <option value="true"><?php echo i18n('checkBox');?></option>
                <option value="false"><?php echo i18n('uncheckedBox');?></option>
              </select>
            </td>
          </tr>
         <?php }
         //end gautier #asset
      // Resolution
            if (isDisplayable($obj,'idResolution')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeResolution');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idResolution" name="idResolution">
                 <?php htmlDrawOptionForReference('idResolution', null, null, false);?>
                </select>
                <button id="resolutionButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                                showDetail("idResolution",0); 
                  </script>
                </button>
              </td>
            </tr>
            <?php }
      // Responsable
            if (isDisplayable($obj,'idResource')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeResponsible',array($obj->getColCaption('idResource')));?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idResource" name="idResource">
                 <?php htmlDrawOptionForReference('idResource', null, null, false);?>
                </select>
                <button id="responsibleButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                    showDetail("idResource",0); 
                  </script>
                </button>
              </td>
            </tr>
            <?php }
      // Target Version
             if (isDisplayable($obj,'idTargetVersion')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeTargetVersion');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idTargetVersion" name="idTargetVersion">
                 <?php htmlDrawOptionForReference('idTargetVersion', null, null, false);?>
                </select>
                <button id="targetVersionButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                    showDetail("idTargetVersion",0); 
                  </script>
                </button>
              </td>
            </tr>
            <?php }
            //Product
            if (isDisplayable($obj,'idProduct')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeProduct');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idProduct" name="idProduct">
                 <?php htmlDrawOptionForReference('idProduct', null, null, false);?>
                </select>
                <button id="productButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                    showDetail("idProduct",0); 
                  </script>
                </button>
              </td>
            </tr>
             <?php }
         // Product Target Version
             if (isDisplayable($obj,'idTargetProductVersion')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeTargetProductVersion');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idTargetProductVersion" name="idTargetProductVersion">
                 <?php htmlDrawOptionForReference('idTargetProductVersion', null, null, false);?>
                </select>
                <button id="targetProductVersionButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                    showDetail("idTargetProductVersion",0); 
                  </script>
                </button>
              </td>
            </tr>
            <?php }
            //targetMilestone
            if (isDisplayable($obj,'idMilestone')) {?>
              <tr class="detail">
                <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeMilestone');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
                <td>
                  <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                  <?php echo autoOpenFilteringSelect();?>
                   id="idMilestone" name="idMilestone">
                   <?php htmlDrawOptionForReference('idMilestone', null, null, false);?>
                  </select>
                  <button id="milestone" dojoType="dijit.form.Button" showlabel="false"
                    title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                    <script type="dojo/connect" event="onClick" args="evt">
                      showDetail("idMilestone",0); 
                    </script>
                  </button>
                </td>
              </tr>
               <?php }
            //component
            if (isDisplayable($obj,'idComponent')) {?>
              <tr class="detail">
                <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeComponent');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
                <td>
                  <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                  <?php echo autoOpenFilteringSelect();?>
                   id="idComponent" name="idComponent">
                   <?php htmlDrawOptionForReference('idComponent', null, null, false);?>
                  </select>
                  <button id="idComponentBis" dojoType="dijit.form.Button" showlabel="false"
                    title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                    <script type="dojo/connect" event="onClick" args="evt">
                      showDetail("idComponent",0); 
                    </script>
                  </button>
                </td>
              </tr>
               <?php }
             //ComponentVersion
             if (isDisplayable($obj,'idTargetComponentVersion')) {?>
               
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeTargetComponentVersion');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idTargetComponentVersion" name="idTargetComponentVersion">
                 <?php htmlDrawOptionForReference('idComponentVersion', null, null, false);?>
                </select>
                <button id="targetComponentVersion" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                    showDetail("idComponentVersion",0); 
                  </script>
                </button>
              </td>
            </tr>
             <?php }
             
       // Initial Due Date
            if (isDisplayable($obj,'initialDueDate')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeInitialDueDate');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <div dojoType="dijit.form.DateTextBox" name="initialDueDate" id="initialDueDate"
                	<?php if (sessionValueExists('browserLocaleDateFormatJs')) {
										echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
									}?>
                 style="width:100px;" class="input" value="" ></div>
              </td>
            </tr>
            <?php }
      // Actual due date
            if (isDisplayable($obj,'actualDueDate')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeActualDueDate');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <div dojoType="dijit.form.DateTextBox" name="actualDueDate" id="actualDueDate"
                <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
										echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
									}?>
                 style="width:100px;" class="input" value="" ></div>
              </td>
            </tr>
            <?php } 
      // Initial End Date
						if (isDisplayable($obj,'initialEndDate')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeInitialEndDate');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <div dojoType="dijit.form.DateTextBox" name="initialEndDate" id="initialEndDate"
                <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
										echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
									}?>
                 style="width:100px;" class="input" value="" ></div>
              </td>
            </tr>
            <?php }
      // Actual End Date
            if (isDisplayable($obj,'actualEndDate')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeActualEndDate');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <div dojoType="dijit.form.DateTextBox" name="actualEndDate" id="actualEndDate"
                <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
										echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
									}?>
                 style="width:100px;" class="input" value="" ></div>
              </td>
            </tr>
            <?php }
      // Initial Due DateTime 
            if (isDisplayable($obj,'initialDueDateTime')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeInitialDueDateTime');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <div dojoType="dijit.form.DateTextBox" name="initialDueDate" id="initialDueDate"
                <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
										echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
									}?>
                 style="width:100px;" class="input" value="" ></div>
                <div dojoType="dijit.form.TimeTextBox" name="initialDueTime" id="initialDueTime"
                 style="width:100px;" class="input" value="" ></div>
              </td>
            </tr>
            <?php }
      // Actual Due Datetime
            if (isDisplayable($obj,'actualDueDateTime')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeActualDueDateTime');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <div dojoType="dijit.form.DateTextBox" name="actualDueDate" id="actualDueDate"
                <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
										echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
									}?>
                 style="width:100px;" class="input" value="" ></div>
                <div dojoType="dijit.form.TimeTextBox" name="actualDueTime" id="actualDueTime"
                 style="width:100px;" class="input" value="" ></div>
              </td>
            </tr>
            <?php }
      // Validate Start Date
            $pe=get_class($obj).'PlanningElement';
            if (isDisplayable($obj,'validatedStartDate', true)) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeValidatedStartDate');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <div dojoType="dijit.form.DateTextBox" name="<?php echo $pe;?>_validatedStartDate" id="<?php echo $pe;?>_validatedStartDate"
                <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
										echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
									}?>
                 style="width:100px;" class="input" value="" ></div>
              </td>
            </tr>
            <?php }
      // Validated End Date
            $pe=get_class($obj).'PlanningElement';
            if (isDisplayable($obj,'validatedEndDate', true)) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeValidatedEndDate');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <div dojoType="dijit.form.DateTextBox" name="<?php echo $pe;?>_validatedEndDate" id="<?php echo $pe;?>_validatedEndDate"
                <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
										echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
									}?>
                 style="width:100px;" class="input" value="" ></div>
              </td>
            </tr>
            <?php }
     // validatedCost and validatedWork krowry debug
            $pe=get_class($obj).'PlanningElement';
            if (isDisplayable($obj,'validatedWork', true)) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colValidatedWork');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <div dojoType="dijit.form.NumberTextBox" name="<?php echo $pe;?>_validatedWork" id="<?php echo $pe;?>_validatedWork"
                 style="width:60px;" maxlength="10" maxSize="4" class="input" ></div>
                 <?php echo Work::displayShortWorkUnit();?>
              </td>
            </tr>
            <?php }
     // validatedCost and validatedWork krowry debug
            $currency=Parameter::getGlobalParameter('currency');
            $currencyPosition=Parameter::getGlobalParameter('currencyPosition');
            $pe=get_class($obj).'PlanningElement';
            if (isDisplayable($obj,'validatedCost', true)) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colValidatedCost');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <?php if ($currencyPosition=='before') echo $currency;?>
                <div dojoType="dijit.form.NumberTextBox" name="<?php echo $pe;?>_validatedCost" id="<?php echo $pe;?>_validatedCost"
                 style="width:60px;" maxlength="10" maxSize="4" class="input" ></div>
                 <?php if ($currencyPosition=='after') echo $currency;?>
              </td>
            </tr>
     
            <?php }
     // weight type  and Progress type 
            $pe=get_class($obj).'PlanningElement';
            if(Parameter::getGlobalParameter('technicalProgress')=='YES' and $pe=="ActivityPlanningElement"){
            ?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colIdProgressMode');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="<?php echo $pe.'_idProgressMode';?>" name="<?php echo  $pe.'_idProgressMode';?>">
                 <?php htmlDrawOptionForReference('idProgressMode', null, null, false);?>
                </select>
              </td>
            </tr>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colIdWeightMode');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="<?php echo $pe.'_idWeightMode';?>" name="<?php echo $pe.'_idWeightMode';?>">
                 <?php htmlDrawOptionForReference('idWeightMode', null, null, false);?>
                </select>
              </td>
            </tr>
            <?php  }
      // Planning Mode
            $pe=get_class($obj).'PlanningElement';
            $pm='id'.get_class($obj).'PlanningMode';
            if (isDisplayable($obj,$pm, true)) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changePlanningMode');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="<?php echo $pe.'_'.$pm;?>" name="<?php echo $pe.'_'.$pm;?>">
                 <?php htmlDrawOptionForReference($pm, null, null, false);?>
                </select>
              </td>
            </tr>
            <?php }
      // Priority
            $pe=get_class($obj).'PlanningElement';
            $pm='id'.get_class($obj).'PlanningMode';
            if (isDisplayable($obj,'priority', true)) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeRequestor',array(i18n('colPriority')));?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <input dojoType="dijit.form.TextBox" class="input" style="width:<?php echo $fieldWidth;?>px;" 
                 id="<?php echo $pe.'_priority';?>" name="<?php echo $pe.'_priority';?>" value="">
              </td>
            </tr>
            <?php }
            
       // fix planning, fixPerimeter, under construction
             if (isDisplayable($obj,'fixPlanning') and get_class($obj)=='Activity') {?>
              <tr class="detail">
                <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeResponsible',array($obj->getColCaption('fixPlanning')));?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
                <td>
                  <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                  <?php echo autoOpenFilteringSelect();?>
                   id="fixPlanning" name="fixPlanning">
                   <option value=""> </option>
                   <option value="ON"><?php echo i18n("checkBox");?></option>
                   <option value="OFF"><?php echo i18n("uncheckedBox");?></option>
                  </select>
                </td>
              </tr>
            <?php }
            
       // result
            if (isDisplayable($obj,'result')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colAddToResult');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <textarea dojoType="dijit.form.Textarea" name="result" id="result"
                 rows="2" style="width:<?php echo $fieldWidth;?>px;" maxlength="4000" maxSize="4" class="input" ></textarea>
              </td>
            </tr>
            <?php }
// MTY - LEAVE SYSTEM
        $lsClass = get_class($obj);        
       // startDate
            if (isDisplayable($obj, 'startDate')) {
                if ($lsClass=="EmployeeLeaveEarned") {
                    ?>
                    <tr class="detail">
                      <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeStartDate');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
                      <td>
                        <div dojoType="dijit.form.DateTextBox" name="startDate" id="startDate"
                        <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
                                echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
                              }?>
                         style="width:100px;" class="input" value="" ></div>
                      </td>
                    </tr>
                    <?php                 
                }
            }
       // endDate
            if (isDisplayable($obj, 'endDate')) {
                if ($lsClass=="EmployeeLeaveEarned") {
                ?>
                    <tr class="detail">
                      <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeEndDate');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
                      <td>
                        <div dojoType="dijit.form.DateTextBox" name="endDate" id="endDate"
                        <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
                                echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
                              }?>
                         style="width:100px;" class="input" value="" ></div>
                      </td>
                    </tr>
                    <?php                 
                }
            }
       // quantity
            if (isDisplayable($obj,'quantity', true)) {
                if ($lsClass=="EmployeeLeaveEarned") {
                ?>
                    <tr class="detail">
                      <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('ChangeQuantity');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
                      <td>
                        <textarea dojoType="dijit.form.Textarea" name="quantity" id="quantity"
                         rows="2" style="width:60px;" maxlength="4000" maxSize="4" class="input" ></textarea>
                         <?php echo i18n("days");?>
                      </td>
                    </tr>
                    <?php 
                }
            }
       // isEmployee
            if (isDisplayable($obj,'isEmployee', true)) {
                if ($lsClass=="Resource") {
                ?>
                    <tr class="detail">
                      <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeIsEmployee');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
                      <td>
                        <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                        <?php echo autoOpenFilteringSelect();?>
                         id="isEmployee" name="isEmployee">
                         <option value=""> </option>
                         <option value="ON"><?php echo i18n("checkBox");?></option>
                         <option value="OFF"><?php echo i18n("uncheckedBox");?></option>
                        </select>
                      </td>
                    </tr>
                    <?php 
                }
            }
            // workflow
            $isType=get_class($obj);
            if (property_exists($obj,'idWorkflow') and  strpos($isType,'Type')==(strlen($isType)-4)) {
            ?>
                <tr class="detail">
                  <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('changeWorkFlow');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
                  <td>
                    <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                    <?php echo autoOpenFilteringSelect();?>
                    id="changerWorkFlow" name="changerWorkFlow">
                    <?php htmlDrawOptionForReference('idWorkflow',null,false,false);?>
                    </select>
                  </td>
                </tr>
           <?php 
           }
// MTY - LEAVE SYSTEM
            ?>          </table>
      </div>
      <?php $titlePane=get_class($obj)."_MultipleOthers";?>
      <br/>
      <div style="width: <?php echo $displayWidth;?>;<?php if (1 or isNewGui()) echo 'margin-left: 15px;';?>" dojoType="dijit.TitlePane" 
           title="<?php echo i18n('sectionMiscellaneous');?>"
           open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
           id="<?php echo $titlePane;?>" 
           onHide="saveCollapsed('<?php echo $titlePane;?>');"
           onShow="saveExpanded('<?php echo $titlePane;?>');" >
          <table>
            <?php
      // Notes
            if (isDisplayable($obj,'_Note')) {?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colAddNote');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <textarea dojoType="dijit.form.Textarea" name="note" id="note"
                 rows="2" style="width:<?php echo $fieldWidth;?>px;" maxlength="4000" maxSize="4" class="input" ></textarea>
              </td>
            </tr>
            <?php } if(get_class($obj)=='Affectation'){ ?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeProfile',array($obj->getColCaption('idProfile')));?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idProfile_multiple" name="idProfile_multiple">
                 <?php htmlDrawOptionForReference('idProfile', null, null, false);?>
                </select>
                <button id="profileButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                    showDetail("idProfile",0); 
                  </script>
                </button>
              </td>
            </tr>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeRate');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <textarea dojoType="dijit.form.NumberTextBox"  constraints="{min:0,max:100}" name="rate_multiple" id="rate_multiple"
                 rows="2" style="width:60px;" maxlength="4000" maxSize="4" class="input" ></textarea>
              </td>
            </tr>
            <?php } if(get_class($obj)=='Resource'){?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeTeam');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="idTeam_multiple" name="idTeam_multiple">
                 <?php htmlDrawOptionForReference('idTeam', null, null, false);?>
                </select>
                <button id="teamButton" dojoType="dijit.form.Button" showlabel="false"
                  title="<?php echo i18n('showDetail');?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                  <script type="dojo/connect" event="onClick" args="evt">
                    showDetail("idTeam",0); 
                  </script>
                </button>
              </td>
            </tr>
            <?php }
            // gautier #3802
            if(get_class($obj)=='TicketSimple'){
              $type='idTicketType';
            }else{
              $type='id'.get_class($obj).'Type';
            }
            
            if( !property_exists($obj, 'idProject') or !property_exists($obj,$type) or !property_exists($obj,'idStatus')){?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeStatusIdle');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="changeStatusIdle" name="changeStatusIdle">
                  <option value=""></option>
                  <option value="true"><?php echo i18n('checkBox');?></option>
                  <option value="false"><?php echo i18n('uncheckedBox');?></option>
                </select>
              </td>
            </tr>
            
            <?php  }?>
            <?php if(property_exists(get_class($obj), 'isLdap')){?>
            <tr class="detail">
              <td class="labelMultiple" style="width:<?php echo $labelWidth;?>px;"><?php echo i18n('colChangeIsLDAP');?>&nbsp;<?php if (!isNewGui()) echo':&nbsp;';?></td>
              <td>
                <select dojoType="dijit.form.FilteringSelect" class="input" style="width:<?php echo $fieldWidth-$helpWidth;?>px;" 
                <?php echo autoOpenFilteringSelect();?>
                 id="changeIsLDAP" name="changeIsLDAP">
                  <option value=""></option>
                  <option value="true"><?php echo i18n('checkBox');?></option>
                  <option value="false"><?php echo i18n('uncheckedBox');?></option>
                </select>
              </td>
            </tr>
            <?php  }?>
          
         <?php  //gautier #533
          if(property_exists(get_class($obj), 'password') and securityGetAccessRightYesNo('menu'.get_class($obj), 'update', $obj)=='YES'){?>
           <tr class="detail">
              <td></td>
              <td>
                <button id="resetPassword" dojoType="dijit.form.Button" showlabel="true"'
                        class="generalColClass" title="<?php echo i18n('resetPassword');?>" >
                  <span><?php echo i18n('resetPassword');?></span>
                  <script type="dojo/connect" event="onClick" args="evt">
                    multipleUpdateResetPwd("<?php echo $objectClass;?>");
                  </script>
                </button>
              </td>
            </tr>
          <?php  } ?>  
          </table>
          <br/>
          </div>
        </form>
      </div>
      <div dojoType="dijit.layout.ContentPane" id="resultDivMultiple" 
      region="<?php echo ($layout=='vertical')?'bottom':'right';?>" class="listTitle multipleResultDiv" 
      style="<?php echo ($layout=='vertical')?'height:38%;border-top:1px solid var(--color-detail-header-border);':'width:38%;border-left:1px solid var(--color-detail-header-border);';?>">
         <span class="labelMessageEmptyArea" style=""><?php echo i18n('resultArea');?></span>
      </div>
    </div>
  </div> 
</div>

<?php 
function isDisplayable($obj, $field, $fromPlanningElement=false) {
  global $extraHiddenFields, $extraReadonlyFields, $peExtraHiddenFields, $peExtraReadonlyFields;
  if (!$extraHiddenFields) $extraHiddenFields = $obj->getExtraHiddenFields ( null, null, getSessionUser ()->getProfile () );
  if (!$extraReadonlyFields) $extraReadonlyFields = $obj->getExtraReadonlyFields ( null, null, getSessionUser ()->getProfile () );
  if ( property_exists($obj,$field) 
  and ! $obj->isAttributeSetToField($field,'readonly') 
  and ! $obj->isAttributeSetToField($field,'hidden') 
  and ! in_array($field,$extraHiddenFields) and ! in_array($field,$extraReadonlyFields)) {
    return true;
  } else {
    $pe=get_class($obj).'PlanningElement';
    if ($fromPlanningElement and property_exists($obj,$pe) and is_object($obj->$pe) and property_exists($obj->$pe,$field)) {
      $peObj=$obj->$pe;
      $peObj->setVisibility();
      $workVisibility=$peObj->_workVisibility;
      $costVisibility=$peObj->_costVisibility;
      if ( (substr($field,-4,4)=='Cost' and $costVisibility!='ALL') or (substr($field,-4, 4)=='Work' and $workVisibility!='ALL') ) {
        return false;
      }
      if (!$peExtraHiddenFields) $peExtraHiddenFields = $peObj->getExtraHiddenFields ( null, null, getSessionUser ()->getProfile () );
      if (!$peExtraReadonlyFields) $peExtraReadonlyFields = $peObj->getExtraReadonlyFields ( null, null, getSessionUser ()->getProfile () );
      if (! $peObj->isAttributeSetToField($field,'readonly')
      and ! $peObj->isAttributeSetToField($field,'hidden')
      and ! in_array($field,$peExtraHiddenFields) and ! in_array($field,$peExtraReadonlyFields)     ) {
        return true;
      } else {
        return false;
      }      
    } else {
      return false;
    }
  }         
}
?>
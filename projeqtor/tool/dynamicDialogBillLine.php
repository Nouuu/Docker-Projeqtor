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
scriptLog('dynamicDialogBillLine.php');
$id=null;
if (array_key_exists('id',$_REQUEST)) {
	$id=$_REQUEST['id'];
} 
$refType="";
if (array_key_exists("refType", $_REQUEST)) {
	$refType=$_REQUEST['refType'];
	Security::checkValidClass($refType);
}
$refId="";
if (array_key_exists("refId", $_REQUEST)) {
  $refId=$_REQUEST['refId'];
}
if (!$refType or !$refId) {
  traceLog("call dynamicDialogBillLine.php without refType and refId");
  exit;
}

$obj=new $refType($refId); // Note: $refId is checked in base SqlElement constructor to be numeric value
$line=new BillLine($id);   // Note: $id is checked in base SqlElement constructor to be numeric value
if ($line->quantity==null) $line->quantity=0;
if ($line->amount==null) $line->amount=0;
$divTermStyle='';
$divResourceStyle='';
$divDescriptionStyle='';
$divQuantityStyle='';
$divUnitStyle='';
$divExtraStyle='display:none;';
$billingType='M';
if (array_key_exists("billingType", $_REQUEST)) {
  $billingType=$_REQUEST["billingType"];
  Security::checkValidAlphanumeric($billingType);
} else if (property_exists($obj, 'billingType')) {
  $billingType=$obj->billingType;
}
$currency=Parameter::getGlobalParameter('currency');
$currencyPosition=Parameter::getGlobalParameter('currencyPosition');
$keyDownEventScript=NumberFormatter52::getKeyDownEvent();
$readOnly=array();
if ($billingType == 'E') {
    if (!$line->quantity) $line->quantity=1;
    //$divTermStyle='display:block;';
    $divResourceStyle='display:none;';
    $divDescriptionStyle='display:none;';
    $divQuantityStyle='display:none;';
    $divUnitStyle='display:none;';
    $readOnly['price']=true;
    $readOnly['quantity']=true;
    if ($line->id) {
      $readOnly['term']=true;
      $divDescriptionStyle='display:block;';
    }
  } else if ($billingType == 'R' or $billingType == 'P') {
    $divTermStyle='display:none;';
    //$divResourceStyle='display:block;';
    //$divDescriptionStyle='display:block;';
    $readOnly['price']=true;
    $readOnly['quantity']=true;
    $readOnly['unit']=true;
    $line->idMeasureUnit=3;
    if (!$line->id) { // add
      $divDescriptionStyle='display:none;';
    } else { // edit
      $readOnly['resource']=true;
      $readOnly['startDate']=true;
      $readOnly['endDate']=true;
    }
  } else if ($billingType == 'M') {
    if (!$line->quantity) $line->quantity=1;
    $divTermStyle='display:none;';
    $divResourceStyle='display:none;';
    $divDescriptionStyle='display:block;';
    if ($refType=='Command') {
      $divExtraStyle='';
    }
  } else if ($billingType == 'N') {
    echo (i18n('billingTypeN'));
  } else {
    errorLog('error : unknown billing type');
    exit;
  } 

if ($line->line) {
  $numLine=$line->line;
} else {
  $cpt=$line->countSqlElementsFromCriteria(array('refType'=>$refType, 'refId'=>$refId));
  $numLine=$cpt+1;
}
if ($billingType == 'M' and $refType == 'ProviderOrder' and $line->id ) {
  $providerTerm = new ProviderTerm();
  $listProvTerm = $providerTerm->getSqlElementsFromCriteria(array("idProviderOrder"=>$obj->id));
  $billLineTerm = new BillLine();
  $hide = false;
  foreach ($listProvTerm as $providerTerms){
    $billLineList=$billLineTerm->getSqlElementsFromCriteria(array("refType"=>"ProviderTerm","refId"=>$providerTerms->id));
    foreach ($billLineList as $billLineNew){
      if($billLineNew->idBillLine == $line->id and $billLineNew->rate > 0){
        $hide = true;
      }
    }
  }
  if($hide == true){
   $readOnly['price']=true;
   $readOnly['quantity']=true;
  }
}

?>
  <table>
    <tr>
      <td>
       <form id='billLineForm' name='billLineForm' onSubmit="return false;">
      	 <input id="billLineId" name="billLineId" type="hidden" value="<?php echo $line->id;?>" />
         <input id="billLineRefType" name="billLineRefType" type="hidden" value="<?php echo htmlEncode($refType);?>" />
         <input id="billLineRefId" name="billLineRefId" type="hidden" value="<?php echo htmlEncode($refId);?>" />
         <input id="billLineBillingType" name="billLineBillingType" type="hidden" value="<?php echo htmlEncode($billingType);?>" />
       	 <table>
       	 <?php if ($billingType == 'M' &&  $refType != 'Tender' and $refType != 'ProviderOrder' and $refType != 'ProviderBill') {?>
       	     <tr>
             <td class="dialogLabel"  >
               <label for="billLineIdCatalog" ><?php echo i18n("colIdCatalog") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect"
              <?php echo autoOpenFilteringSelect();?>
                id="billLineIdCatalog" name="billLineIdCatalog"
                class="input" 
                onChange="billLineChangeCatalog();" 
                missingMessage="<?php echo i18n('messageMandatory',array(i18n('colIdCatalog')));?>" >
                 <?php htmlDrawOptionForReference('idCatalog', $line->idCatalog, null, false); ?>
               </select> 
             </td>
           </tr>  
         <?php } else {?>
           <input type='hidden' id="billLineIdCatalog" name="billLineIdCatalog" value="" />
         <?php } ?>  	      	 
           <tr>
             <td class="dialogLabel" >
              <label for="billLineLine" ><?php echo i18n("colLineNumber");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
		           <input dojoType="dijit.form.NumberTextBox" 
			          id="billLineLine" name="billLineLine"
			          style="width:100px;"
			          class="input" readonly
			          value="<?php echo $numLine;?>" />
		         </td>
		       </tr>
          </table>
          <div style='<?php echo $divExtraStyle;?>'>
          <table>
           <tr>
             <td class="dialogLabel" >
              <label for="billLineLine" >&nbsp;</label>
             </td>
             <td>
		           <div dojoType="dijit.form.CheckBox" type="checkbox"
		            <?php if ($line->extra) echo "checked";?>
			          id="billLineExtra" name="billLineExtra"></div>
			          <?php echo i18n("colAdd");?>
		         </td>
		       </tr>
          </table>
          </div>
          <div id='billLineFrameTerm' style='<?php echo $divTermStyle;?>'>
          <table>
		       <tr>
             <td class="dialogLabel" >
               <label for="billLineIdTerm" ><?php echo i18n("colIdTerm");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
               <?php echo autoOpenFilteringSelect();?>
                id="billLineIdTerm" name="billLineIdTerm"
                <?php if (isset($readOnly['term'])) echo " readonly ";?>
                missingMessage="<?php echo i18n('mandatory');?>"
                class="input" value="<?php echo $line->idTerm;?>" >
                <?php 
                   htmlDrawOptionForReference('idTerm', $line->idTerm,null,false, 'idProject',$obj->idProject);
                   // no use : will be updated on dialog opening;
                 ?>
               </select>  
             </td>
           </tr>
           </table>
           </div> 
           <div id='billLineFrameResource'  style='<?php echo $divResourceStyle;?>'>
           <table>
           <tr>
             <td class="dialogLabel" >
               <label for="billLineIdResource" ><?php echo i18n("colIdResource");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
               <?php echo autoOpenFilteringSelect();?>
                id="billLineIdResource" name="billLineIdResource"
                <?php if (isset($readOnly['resource'])) echo " readonly ";?>
                missingMessage="<?php echo i18n('mandatory');?>"
                class="input" value="<?php echo $line->idResource;?>" >
                <?php 
                   htmlDrawOptionForReference('idResource', $line->idResource,null,false,'idProject',$obj->idProject);
                 ?>
               </select>  
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="billLineIdActivityPrice" ><?php echo i18n("colIdActivityPrice");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
               <?php echo autoOpenFilteringSelect();?>
                id="billLineIdActivityPrice" name="billLineIdActivityPrice"
                missingMessage="<?php echo i18n('mandatory');?>"
                class="input" value="" onChange="billLineChangeCatalog" >
                <?php 
                   htmlDrawOptionForReference('idActivityPrice', $line->idActivityPrice,null,false,'idProject',$obj->idProject)
                   // no use : will be updated on dialog opening;
                 ?>
               </select>  
             </td>
           </tr>
		       <tr>
             <td class="dialogLabel" >
               <label for="billLineStartDate" ><?php echo i18n("colStartDate");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <div id="billLineStartDate" name="billLineStartDate"
                dojoType="dijit.form.DateTextBox" required="true" hasDownArrow="false"   
                constraints="{datePattern:browserLocaleDateFormatJs}"
                <?php if (isset($readOnly['startDate'])) echo " readonly ";?>
                type="text" maxlength="10"  style="width:100px; text-align: center;" class="input"
                missingMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
                invalidMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
                value="<?php echo $line->startDate;?>">
               </div>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="billLineEndDate" ><?php echo i18n("colEndDate");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <div id="billLineEndDate" name="billLineEndDate"
                dojoType="dijit.form.DateTextBox" required="true" hasDownArrow="false"   
                constraints="{datePattern:browserLocaleDateFormatJs}"
                <?php if (isset($readOnly['endDate'])) echo " readonly ";?>
                type="text" maxlength="10"  style="width:100px; text-align: center;" class="input"
                missingMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
                invalidMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
                value="<?php echo $line->endDate;?>">
               </div>
             </td>
           </tr>
           </table>
           </div> 
           <div id='billLineFrameDescription'  style='<?php echo $divDescriptionStyle;?>'>
           <table>
           <tr>
             <td class="dialogLabel" >
              <label for="billLineDescription" ><?php echo i18n("colDescription");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
              <textarea dojoType="dijit.form.Textarea" 
	          id="billLineDescription" name="billLineDescription"
	          style="width: 500px;<?php if (isNewGui()) echo 'min-height:32px;max-height:153px';?>"
	          maxlength="200"
	          class="input"><?php echo $line->description;?></textarea>
	         </td>
	        </tr>
            <tr>
             <td class="dialogLabel" >
              <label for="billLineDetail" ><?php echo i18n("colDetail");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <textarea dojoType="dijit.form.Textarea" 
	          id="billLineDetail" name="billLineDetail"
	          style="width: 500px;<?php if (isNewGui()) echo 'min-height:32px;max-height:153px';?>"
	          maxlength="200"
	          class="input"><?php echo $line->detail;?></textarea>  
	         </td>
	        </tr>
          </table>
          </div>
          <table>
            <tr>
             <td class="dialogLabel" >
              <label for="billLinePrice" ><?php echo i18n("colPrice");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
              <?php if ($currencyPosition=='before') echo $currency;?>
              <div dojoType="dijit.form.NumberTextBox" 
	          id="billLinePrice" name="billLinePrice"
	          style="width: 100px;"
	          <?php if (isset($readOnly['price'])) echo " readonly ";?>
	          class="input"
	          onChange="billLineUpdateAmount();"
	          value="<?php echo $line->price;?>">
	          <?php echo $keyDownEventScript;?>
	          </div>
	          <?php if ($currencyPosition=='after') echo $currency;?>
	          </td>
	          <td>
	           <div style="display:inline;<?php echo $divUnitStyle;?>">&nbsp;/&nbsp;
                    <select dojoType="dijit.form.FilteringSelect" 
                      <?php echo autoOpenFilteringSelect();?>
                      id="billLineUnit" name="billLineUnit" 
                      onChange="billLineUpdateNumberDays();"
                      style="width: 100px;"
                      <?php if (isset($readOnly['unit'])) echo " readonly ";?>
                      class="input" value="<?php if ($line->idMeasureUnit) echo $line->idMeasureUnit; else echo ' ';?>" >
                      <?php htmlDrawOptionForReference('idMeasureUnit', null, null, false);?>
                    </select>
            </div>
           </td>
	        </tr>
	      </table>	      
         <div style="<?php echo $divQuantityStyle;?>">
         <table>
	        <tr>
             <td class="dialogLabel" >
               <label for="billLineQuantity" ><?php echo i18n("colQuantity");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <div dojoType="dijit.form.NumberTextBox" 
                id="billLineQuantity" name="billLineQuantity"
                <?php if (isset($readOnly['quantity'])) echo " readonly ";?>
                style="width:100px;"
                onChange="billLineUpdateAmount();billLineUpdateNumberDays();"
                class="input"  value="<?php echo $line->quantity;?>">
                <?php echo $keyDownEventScript;?>  
               </div>
               <?php if ($line->idMeasureUnit) echo SqlList::getFieldFromId('MeasureUnit', $line->idMeasureUnit, ($line->quantity>1)?'pluralName':'name');?> 
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="billLineAmount" ><?php echo i18n("colAmount");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
             <?php if ($currencyPosition=='before') echo $currency;?>
               <input dojoType="dijit.form.NumberTextBox" 
                id="billLineAmount" name="billLineAmount"
                readonly 
                style="width:100px;"
                class="input"  value="<?php echo $line->amount;?>">  
               </input> 
               <?php if ($currencyPosition=='after') echo $currency;?>
             </td>
           </tr>
           <?php if($refType=="Quotation" or $refType=="Command"){?>
           <tr>
             <td class="dialogLabel" >
               <label for="billLineNumberDays" ><?php echo i18n("numberOfDays");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
             </td>
             <td>
               <div dojoType="dijit.form.NumberTextBox" 
                id="billLineNumberDays" name="billLineNumberDays"
                style="width:100px;"
                class="input"  value="<?php echo $line->numberDays;?>">
                <?php echo $keyDownEventScript;?>  
               </div> 
             </td>
           </tr>
           <?php }?>
	      </table>
	      </div>     
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogBillLineAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogBillLine').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" id="dialogBillLineSubmit" type="submit" onclick="protectDblClick(this);saveBillLine();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
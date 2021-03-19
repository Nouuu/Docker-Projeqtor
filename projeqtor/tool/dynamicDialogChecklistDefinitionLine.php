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

if (! array_key_exists('checkId',$_REQUEST)) {
  throwError('objectClass checkId not found in REQUEST');
}
$checkId=null;
if ( array_key_exists('checkId',$_REQUEST) ) {
  $checkId=$_REQUEST['checkId'];
}
$lineId=0;
if ( array_key_exists('lineId',$_REQUEST)) {
	$lineId=$_REQUEST['lineId'];
}
$line=new ChecklistDefinitionLine($lineId); // Note: $lineId is checked in base SqlElement constructor to be numeric value.
if ($line->id) {
	$checkId=$line->idChecklistDefinition;
} else {
	$line->exclusive=1;
	$all=$line->getSqlElementsFromCriteria(array('idChecklistDefinition'=>$checkId),false,null,'sortOrder desc');
	if (count($all)) {
	  $l=array_shift($all);
	  if ($l and $l->id) {
	    $line->sortOrder=$l->sortOrder+10;
	  }	    
	} else {
	  $line->sortOrder=10;
	}
}

?>
<form id="dialogChecklistDefinitionLineForm" name="dialogChecklistDefinitionLineForm" action="">
<input type="hidden" name="checklistDefinitionLineId" value="<?php echo $line->id;?>" />
<input type="hidden" name="checklistDefinitionId" value="<?php echo htmlEncode($checkId);?>" />
<table style="width: 100%;">
  <tr>
    <td class="dialogLabel" ><label><?php echo i18n('colName').Tool::getDoublePoint();?></label></td>
    <td><input type="text" dojoType="dijit.form.TextBox" 
      id="dialogChecklistDefinitionLineName" 
      name="dialogChecklistDefinitionLineName"
      value="<?php echo $line->name;?>"
      style="width: 300px;" maxlength="100" class="input" />
    </td>
  </tr>
  <tr>
    <td class="dialogLabel" ><label><i>(<?php echo i18n('tooltip');?>)&nbsp;&nbsp;</i><label></td>
    <td><textarea dojoType="dijit.form.Textarea" 
          id="dialogChecklistDefinitionLineTitle" name="dialogChecklistDefinitionLineTitle"
          style="width: 300px;"
          maxlength="1000"
          value="<?php echo $line->title;?>"
          title="<?php echo i18n('helpTitle');?>"
          class="input"></textarea>
    </td>
  </tr>
  <tr>
    <td class="dialogLabel" ><label><?php echo i18n('colSortOrder').Tool::getDoublePoint();?></label></td>
    <td><input type="text" dojoType="dijit.form.NumberTextBox" 
      id="dialogChecklistDefinitionLineSortOrder" 
      name="dialogChecklistDefinitionLineSortOrder"
      value="<?php echo $line->sortOrder;?>"
      style="width: 30px;" maxlength="3" class="input" />
    </td>
  </tr>
<?php
$cpVar=0;
 for ($i=1;$i<=5;$i++) {?>
  <tr>
    <td class="dialogLabel" ><label><?php echo i18n('colChoice') . ' #'.$i.Tool::getDoublePoint();?></label></td>
    <td><input type="text" dojoType="dijit.form.TextBox" 
      id="dialogChecklistDefinitionLineChoice_<?php echo $i?>" 
      name="dialogChecklistDefinitionLineChoice_<?php echo $i?>"
      value="<?php $var="check0$i";echo $line->$var;if($line->$var!='')$cpVar++?>"
      style="width: 300px;" maxlength="100" class="input" onchange="displayCheckBoxDefinitionLine();"/>
    </td>  
  </tr>
  <tr>
    <td class="dialogLabel" ><label><i>(<?php echo i18n('tooltip');?>)&nbsp;&nbsp;</i><label></td>
    <td><textarea dojoType="dijit.form.Textarea" 
          id="dialogChecklistDefinitionLineTitle_<?php echo $i?>" 
          name="dialogChecklistDefinitionLineTitle_<?php echo $i?>"
          style="width: 300px;"
          maxlength="1000"
          title="<?php echo i18n('helpTitle');?>"
          class="input"><?php $vart="title0$i";echo $line->$vart; ?></textarea>
    </td>
  </tr>
<?php }?>
  <tr id="tr_dialogChecklistDefinitionLineExclusive" style="visibility:<?php echo ($cpVar==0)?'hidden':'visible';?>;">
    <td class="dialogLabel" ><label><?php echo i18n('colExclusive').Tool::getDoublePoint();?></label></td>
    <td> 
      <input dojoType="dijit.form.CheckBox" 
       name="dialogChecklistDefinitionLineExclusive" 
       id="dialogChecklistDefinitionLineExclusive"
       <?php echo ($line->exclusive)?' checked="checked" ':'';?>
       value="" style="background-color:white;" />
   </td>
 </tr>
 <tr id="tr_dialogChecklistDefinitionLineRequired" style="visibility:<?php echo ($cpVar==0)?'hidden':'visible';?>;">
   <td class="dialogLabel" ><label><?php echo i18n('colRequired').Tool::getDoublePoint();?></label></td>
   <td> 
      <input dojoType="dijit.form.CheckBox" 
       name="dialogChecklistDefinitionLineRequired" 
       id="dialogChecklistDefinitionLineRequired"
       <?php echo ($line->required)?' checked="checked" ':'';?>
       value="" style="background-color:white;" />
   </td>
 </tr>
 <tr><td colspan="2">&nbsp;</td></tr>
 <tr>
   <td colspan="2" align="center">
     <button dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogChecklistDefinitionLine').hide();">
       <?php echo i18n("buttonCancel");?>
     </button>
     <button id="dialogChecklistDefinitionLineSubmit" dojoType="dijit.form.Button" type="submit" 
       onclick="protectDblClick(this);saveChecklistDefinitionLine();return false;" >
       <?php echo i18n("buttonOK");?>
     </button>
   </td>
 </tr>      
</table>
</form>

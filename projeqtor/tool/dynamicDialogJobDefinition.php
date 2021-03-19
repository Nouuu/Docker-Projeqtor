<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Julien PAPASIAN
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

$checkId=RequestHandler::getId('checkId',true,null);
$lineId=RequestHandler::getId('lineId',null,0);

$line=new JobDefinition($lineId);
if ($line->id) {
	$checkId=$line->idJoblistDefinition;
} else {
	$all=$line->getSqlElementsFromCriteria(array('idJoblistDefinition'=>$checkId),false,null,'sortOrder desc');
	if (count($all)) {
	  $l=array_shift($all);
	  if ($l and $l->id) {
	    $line->sortOrder=$l->sortOrder+10;
	  }
	} else {
	  $line->sortOrder=10;
	}
  $line->daysBeforeWarning = 0;
}

?>
<form id="dialogJobDefinitionForm" name="dialogJobDefinitionForm" action="">
<input type="hidden" name="jobDefinitionId" value="<?php echo $line->id;?>" />
<input type="hidden" name="joblistDefinitionId" value="<?php echo $checkId;?>" />
<table style="width: 100%;">
  <tr>
    <td class="dialogLabel" ><label style="width:250px"><?php echo i18n('colName').Tool::getDoublePoint();?></label></td>
    <td><input type="text" dojoType="dijit.form.TextBox"
      id="dialogJobDefinitionName"
      name="dialogJobDefinitionName"
      value="<?php echo $line->name;?>"
      style="width: 300px;" maxlength="100" class="input" />
    </td>
  </tr>
  <tr>
    <td class="dialogLabel" style="text-align:right"><i>(<?php echo i18n('tooltip');?>)&nbsp;&nbsp;</i></td>
    <td><textarea dojoType="dijit.form.Textarea"
          id="dialogJobDefinitionTitle" name="dialogJobDefinitionTitle"
          style="width: 300px;"
          maxlength="1000"
          value="<?php echo $line->title;?>"
          title="<?php echo i18n('helpTitle');?>"
          class="input"></textarea>
    </td>
  </tr>
  <tr>
    <td class="dialogLabel" ><label style="width:250px"><?php echo i18n('colSortOrder').Tool::getDoublePoint();?></label></td>
    <td><input type="text" dojoType="dijit.form.TextBox"
      id="dialogJobDefinitionSortOrder"
      name="dialogJobDefinitionSortOrder"
      value="<?php echo $line->sortOrder;?>"
      style="width: 30px;" maxlength="3" class="input" />
    </td>
  </tr>
  <tr>
    <td class="dialogLabel" ><label style="width:250px"><?php echo i18n('colDaysBeforeWarning').Tool::getDoublePoint();?></label></td>
    <td><input type="text" dojoType="dijit.form.TextBox"
      id="dialogJobDefinitionDaysBeforeWarning"
      name="dialogJobDefinitionDaysBeforeWarning"
      value="<?php echo $line->daysBeforeWarning;?>"
      style="width: 30px;" maxlength="3" class="input" />&nbsp;<?php echo i18n("shortDay");?>
    </td>
  </tr>
 <tr><td colspan="2">&nbsp;</td></tr>
 <tr>
   <td colspan="2" align="center">
     <button dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogJobDefinition').hide();">
       <?php echo i18n("buttonCancel");?>
     </button>
     <button id="dialogJobDefinitionSubmit" dojoType="dijit.form.Button" type="submit"
       onclick="protectDblClick(this);saveJobDefinition();return false;" >
       <?php echo i18n("buttonOK");?>
     </button>
   </td>
 </tr>
</table>
</form>
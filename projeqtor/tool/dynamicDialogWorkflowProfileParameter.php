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
$id=trim($_REQUEST['idWorkflow']);
$id=Security::checkValidId($id);
$profileList=SqlList::getList('Profile');
?>
<form id="dialogWorkflowProfileParameterForm"
	name="dialogWorkflowProfileParameterForm" action="">
	<input type="hidden" name="workflowId" value="<?php echo $id;?>" />
	<td style="width: 100%;">
		<table width="100%;">
<?php

foreach ($profileList as $idProfile=>$profile) {
  $canUpdate=true;
  $checked=true;
  $ws=new WorkflowStatus();
  $cptWs=$ws->countSqlElementsFromCriteria(null, "idWorkflow=$id and idProfile=$idProfile");
  if ($cptWs>0) {
    $canUpdate=false;
  }

  $wp=SqlElement::getSingleSqlElementFromCriteria('WorkflowProfile', array('idWorkflow'=>$id, 'idProfile'=>$idProfile));
  if (!$wp->id) {
    $wp->checked=1;
    $wp->idWorkflow=$id;
    $wp->idProfile=$idProfile;
//  $wp->createSqlRow(); // Direct Query banned
    $wp->save();
  }
  $checked=$wp->getCheckedInfo();
  ?>
<tr style="height: 10px;">
				<td style="width: 350px">&nbsp;&nbsp;
					<div dojoType="dijit.form.CheckBox" type="checkbox"
						name="dialogWorkflowParameterCheckProfileId_<?php echo $idProfile;?>"
						id="dialogWorkflowProfileParameterCheckProfileId_<?php echo $idProfile;?>"
						<?php if (! $canUpdate) {echo ' readonly';} else {echo ' class="workflowProfileParameterCheckbox"';}?>
						<?php if ($checked) { echo ' checked'; }?>></div> <span
					style="cursor: pointer;"
					onClick="dojo.byId('dialogWorkflowParameterCheckProfileId_<?php echo $idProfile;?>').click();">
<?php echo $profile;?>
</span>
				</td>
			</tr>
<?php }?>
<tr>
				<td>
					<button dojoType="dijit.form.Button" type="button"
						onclick="dialogWorkflowProfileParameterUncheckAll();">
<?php echo i18n("checkUncheckAll");?>
</button>
					<button dojoType="dijit.form.Button" type="button"
						onclick="dijit.byId('dialogWorkflowProfileParameter').hide();">
<?php echo i18n("buttonCancel");?>
</button>
					<button id="dialogWorkflowProfileParameterSubmit"
						dojoType="dijit.form.Button" type="submit"
						onclick="protectDblClick(this);saveWorkflowProfileParameter();return false;">
<?php echo i18n("buttonOK");?>
</button>
				</td>
			</tr>
		</table>

</form>
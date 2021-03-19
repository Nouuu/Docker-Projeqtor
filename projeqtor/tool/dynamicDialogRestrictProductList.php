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
$id=trim($_REQUEST['idProfile']);
$id=Security::checkValidId($id);
$canUpdate=(securityGetAccessRightYesNo('menuProfile', 'update', new Profile($id)) == 'YES');

?>
<form id="dialogRestrictProductListForm"
	name="dialogRestrictProductListForm" action="">
<input type="hidden" name="idProfile" value="<?php echo $id;?>" />

<table width="100%;">
<table>
<?php
$rl=SqlElement::getSingleSqlElementFromCriteria('RestrictList', array('idProfile'=>$id));
if (!$rl->id) {
  $rl->idProfile=$id;
  $rl->showAll=1;
  $rl->showStarted=0; 
  $rl->showDelivered=0; 
  $rl->showInService=0;
  // $rl->createSqlRow(); // Direct Sql banned
  $rl->save();
}
$checkedArr=$rl->getCheckedInfo();

echo "<br/>".i18n("instructionsRestrictList")."<br/>"."<br/>";
foreach ($checkedArr as $status=>$isChecked) {
  echo "<tr style='height:20px;'><td style='width:20px;vertical-align:top;'><input dojoType='dijit/form/RadioButton' type='radio' name='dialogRestrictListCheckProfileId_".$id."' value='".$status."' id='dialogRestrictListCheckProfileId_".$id."_Status_".$status."' "." style='margin-left:2em;' ".(($isChecked)?' checked ':'');
  echo ' '.(!$canUpdate?"readonly":"").'></td><td style="vertical-align:top">&nbsp;'.i18n($status).'</td></tr>';
}
?>
</table>
<table style="width:100%">
<tr style="height: 10px;">
</tr>
<tr>
	<td style="text-align:center">
		<button dojoType="dijit.form.Button" type="button"
						onclick="dijit.byId('dialogRestrictProductList').hide();">
<?php echo i18n(($canUpdate)?"buttonCancel":"comboCloseButton");?>
		</button>
		<?php if ($canUpdate) {?>
		<button id="dialogRestrictProductListSubmit"
						dojoType="dijit.form.Button" type="submit"
						onclick="protectDblClick(this);saveRestrictProductList();return false;">
<?php echo i18n("buttonOK");?>
		</button>
		<?php }?>
	</td>
</tr>

</table>
</form>
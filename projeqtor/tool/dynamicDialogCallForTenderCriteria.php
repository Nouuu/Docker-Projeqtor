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
include_once ("../tool/projeqtor.php");

if (! array_key_exists ( 'mode', $_REQUEST )) {
  throwError ( 'Parameter mode not found in REQUEST' );
}
$mode = $_REQUEST ['mode'];

if ($mode=='add') {
  if (! array_key_exists ( 'callForTenderId', $_REQUEST )) {
    throwError ( 'Parameter callForTenderId not found in REQUEST' );
  }
  $callForTenderId = $_REQUEST ['callForTenderId'];
  Security::checkValidId ( $callForTenderId );
  $criteria=new TenderEvaluationCriteria();
  $criteria->idCallForTender=$callForTenderId;
  $criteria->criteriaCoef=1;
  $criteria->criteriaMaxValue=10;
} else if ($mode=='edit') {
  if (! array_key_exists ( 'criteriaId', $_REQUEST )) {
    throwError ( 'Parameter criteriaId not found in REQUEST' );
  }
  $criteriaId = $_REQUEST ['criteriaId'];
  Security::checkValidId ( $criteriaId );
  $criteria=new TenderEvaluationCriteria($criteriaId);
} else {
  throwError ( 'Parameter mode has not an expected value' );
}


?>
	<table>
		<tr>
			<td>
				<form dojoType="dijit.form.Form" id='dialogTenderCriteriaForm'
					name='dialogTenderCriteriaForm' onSubmit="return false;">
					<input id="dialogTenderCriteriaCallForTenderId" name="dialogTenderCriteriaCallForTenderId" type="hidden"
						value="<?php echo $criteria->idCallForTender;?>" />
						<input id="dialogTenderCriteriaId" name="dialogTenderCriteriaId" type="hidden"
						value="<?php echo $criteria->id;?>" />
					<table>
						<tr>
							<td class="dialogLabel"><label for="dialogTenderCriteriaName"><?php echo i18n("colName") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td>
							  <input type="text" dojoType="dijit.form.ValidationTextBox" 
                  id="dialogTenderCriteriaName" required="true"
                  name="dialogTenderCriteriaName"
                  value="<?php echo $criteria->criteriaName;?>"
                  style="width: 300px;" maxlength="100" class="input" />
						  </td>
						</tr>
						<tr>
							<td class="dialogLabel"><label for="dialogTenderCriteriaMaxValue"><?php echo i18n("colEvaluationMaxValue") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td>
							  <div dojoType="dijit.form.NumberTextBox" 
      	          id="dialogTenderCriteriaMaxValue" name="dialogTenderCriteriaMaxValue" 
      	          constraints="{min:1,max:999}"
      	          style="width: 100px;" class="input" required="true"
      	          value="<?php echo $criteria->criteriaMaxValue;?>">
						  </td>
						</tr>
						<tr>
							<td class="dialogLabel"><label for="dialogTenderCriteriaCoef"><?php echo i18n("colCoefficient") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td>
							  <div dojoType="dijit.form.NumberTextBox" 
      	          id="dialogTenderCriteriaCoef" name="dialogTenderCriteriaCoef" 
      	          constraints="{min:1,max:999}"
      	          style="width: 100px;" class="input" required="true"
      	          value="<?php echo $criteria->criteriaCoef;?>">
						  </td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
					</table>
				</form>
			</td>
		</tr>
		<tr>
			<td align="center">
				<button class="mediumTextButton" dojoType="dijit.form.Button"
					type="button" onclick="dijit.byId('dialogCallForTenderCriteria').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
				<button class="mediumTextButton" dojoType="dijit.form.Button"
					type="submit" id="dialogCallForTenderCriteriaSubmit"
					onclick="protectDblClick(this);saveTenderEvaluationCriteria();return false;">
          <?php echo i18n("buttonOK");?>
        </button></td>
		</tr>
	</table>
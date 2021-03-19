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
  $tender=new Tender();
  $tender->idCallForTender=$callForTenderId;
} else if ($mode=='edit') {
  if (! array_key_exists ( 'tenderId', $_REQUEST )) {
    throwError ( 'Parameter tenderId not found in REQUEST' );
  }
  $tenderId = $_REQUEST ['tenderId'];
  Security::checkValidId ( $tenderId );
  $tender=new Tender($tenderId);
} else {
  throwError ( 'Parameter mode has not an expected value' );
}
$callForTender=new CallForTender($tender->idCallForTender);
if (!$tender->requestDateTime) $tender->requestDateTime=$callForTender->sendDateTime;
if (!$tender->expectedTenderDateTime) $tender->expectedTenderDateTime=$callForTender->expectedTenderDateTime;
?>
	<table>
		<tr>
			<td>
				<form dojoType="dijit.form.Form" id='dialogTenderSubmissionForm'
					name='dialogCallForTenderSubmissionForm' onSubmit="return false;">
					<input id="dialogCallForTenderSubmissionCallForTenderId" name="dialogCallForTenderSubmissionCallForTenderId" type="hidden"
						value="<?php echo $tender->idCallForTender;?>" />
				  <input id="dialogCallForTenderSubmissionTenderId" name="dialogCallForTenderSubmissionTenderId" type="hidden"
						value="<?php echo $tender->id;?>" />
					<table>
					  <tr>
					    <td class="dialogLabel"><label for="dialogCallForTenderSubmissionProvider"><?php echo i18n("colIdProvider") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td><select dojoType="dijit.form.FilteringSelect"
								<?php echo autoOpenFilteringSelect();?>
								id="dialogCallForTenderSubmissionProvider" name="dialogCallForTenderSubmissionProvider"
								onChange="refreshList('idContact', 'idProvider', this.value, dijit.byId('dialogCallForTenderSubmissionContact').get('value'),'dialogCallForTenderSubmissionContact', false);"
								class="input" required="required">
                 <?php htmlDrawOptionForReference('idProvider', $tender->idProvider, null, false);?>
               </select>
								<button id="dialogCallForTenderSubmissionProviderDetailButton"
									dojoType="dijit.form.Button" showlabel="false"
									title="<?php echo i18n('showDetail')?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
									<script type="dojo/connect" event="onClick" args="evt">
                    <?php $canCreate=securityGetAccessRightYesNo('menuProvider', 'create')=="YES";?>
                    showDetail('dialogCallForTenderSubmissionProvider', <?php echo ($canCreate)?1:0;?>, 'Provider', true); 
                 </script>
								</button>
						  </td>
						</tr>
						<tr>
					    <td class="dialogLabel"><label for="dialogCallForTenderSubmissionContact"><?php echo i18n("colIdContact") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td><select dojoType="dijit.form.FilteringSelect"
								<?php echo autoOpenFilteringSelect();?>
								id="dialogCallForTenderSubmissionContact" name="dialogCallForTenderSubmissionContact"
								class="input" >
                 <?php if ($tender->idProvider) htmlDrawOptionForReference('idContact', $tender->idContact, null, false,'idProvider',$tender->idProvider);
                       else htmlDrawOptionForReference('idContact', $tender->idContact, null, false);?>
               </select>
								<button id="dialogCallForTenderSubmissionContactDetailButton"
									dojoType="dijit.form.Button" showlabel="false"
									title="<?php echo i18n('showDetail')?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
									<script type="dojo/connect" event="onClick" args="evt">
                    showDetail('dialogCallForTenderSubmissionContact', 0, 'Contact', true); 
                 </script>
								</button>
						  </td>
						</tr>
						<tr>
					    <td class="dialogLabel"><label for="dialogCallForTenderSubmissionRequestDateTime"><?php echo i18n("colRequestDate") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td>
  							<div id="dialogCallForTenderSubmissionRequestDate" name="dialogCallForTenderSubmissionRequestDate"
                  dojoType="dijit.form.DateTextBox" hasDownArrow="false"   
                  constraints="{datePattern:browserLocaleDateFormatJs}"
                  type="text" maxlength="10"  style="width:100px; text-align: center;" class="input"
                  invalidMessage="<?php echo i18n('messageInvalidDate',array('colRequestDate'));?>" 
                  value="<?php if ($tender->requestDateTime) echo substr($tender->requestDateTime,0,10);?>">
                 </div>
                 <div id="dialogCallForTenderSubmissionRequestTime" name="dialogCallForTenderSubmissionRequestTime"
                  dojoType="dijit.form.TimeTextBox" hasDownArrow="false"   
                  type="text" maxlength="5"  style="width:60px; text-align: center;" class="input"
                  invalidMessage="<?php echo i18n('messageInvalidTime',array('colRequestDate'));?>" 
                  value="T<?php echo substr($tender->requestDateTime,11,5);?>">
                 </div>
						  </td>
						</tr>
						<tr>
					    <td class="dialogLabel"><label for="dialogCallForTenderSubmissionExpectedTenderDate"><?php echo i18n("colExpectedTenderDate") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td>
  							<div id="dialogCallForTenderSubmissionExpectedTenderDate" name="dialogCallForTenderSubmissionExpectedTenderDate"
                  dojoType="dijit.form.DateTextBox" hasDownArrow="false"   
                  constraints="{datePattern:browserLocaleDateFormatJs}"
                  type="text" maxlength="10"  style="width:100px; text-align: center;" class="input"
                  invalidMessage="<?php echo i18n('messageInvalidDate',array('colExpectedTenderDate'));?>" 
                  value="<?php if ($tender->expectedTenderDateTime) echo substr($tender->expectedTenderDateTime,0,10);?>">
                 </div>
                 <div id="dialogCallForTenderSubmissionExpectedTenderTime" name="dialogCallForTenderSubmissionExpectedTenderTime"
                  dojoType="dijit.form.TimeTextBox" hasDownArrow="false"   
                  type="text" maxlength="5"  style="width:60px; text-align: center;" class="input"
                  invalidMessage="<?php echo i18n('messageInvalidTime',array('colExpectedTenderDate'));?>" 
                  value="T<?php echo substr($tender->expectedTenderDateTime,11,5);?>">
                 </div>
						  </td>
						</tr>
						<tr>
					    <td class="dialogLabel"><label for="dialogCallForTenderSubmissionStatus"><?php echo i18n("colIdTenderStatus") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td><select dojoType="dijit.form.FilteringSelect"
								<?php echo autoOpenFilteringSelect();?>
								id="dialogCallForTenderSubmissionStatus" name="dialogCallForTenderSubmissionStatus"
								class="input" required="required">
                 <?php htmlDrawOptionForReference('idTenderStatus', $tender->idTenderStatus, null, true);?>
               </select>
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
					type="button" onclick="dijit.byId('dialogCallForTenderSubmission').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
				<button class="mediumTextButton" dojoType="dijit.form.Button"
					type="submit" id="dialogCallForTenderSubmissionSubmit"
					onclick="protectDblClick(this);saveTenderSubmission();return false;">
          <?php echo i18n("buttonOK");?>
        </button></td>
		</tr>
	</table>
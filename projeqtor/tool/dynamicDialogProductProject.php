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

if (! array_key_exists ( 'idProductProject', $_REQUEST )) {
  throwError ( 'Parameter idProductProject not found in REQUEST' );
}
$idProductProject = $_REQUEST ['idProductProject'];
Security::checkValidId ( $idProductProject );

if (! array_key_exists ( 'idProduct', $_REQUEST )) {
  throwError ( 'Parameter idProduct not found in REQUEST' );
}
$idProduct = $_REQUEST ['idProduct'];
Security::checkValidId ( $idProduct );

if (! array_key_exists ( 'idProject', $_REQUEST )) {
  throwError ( 'Parameter idProject not found in REQUEST' );
}
$idProject = $_REQUEST ['idProject'];
Security::checkValidId ( $idProject );

$pp=new ProductProject($idProductProject);

?>
	<table>
		<tr>
			<td>
				<form dojoType="dijit.form.Form" id='productProjectForm'
					name='productProjectForm' onSubmit="return false;">
					<input id="productProjectId" name="productProjectId" type="hidden"
						value="<?php echo $idProductProject;?>" />
					<table>
						<tr>
							<td class="dialogLabel"><label for="productProjectProject"><?php echo i18n("colIdProject") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td><select dojoType="dijit.form.FilteringSelect"
								<?php echo autoOpenFilteringSelect();?>
								id="productProjectProject" name="productProjectProject"
								<?php if ($idProject) echo ' readonly="readonly" ';?>
								class="input" value="<?php echo $idProject;?>" required="required">
                 <?php htmlDrawOptionForReference('idProject', $idProject, null, true);?>
               </select>
               <?php if (! $idProject) { ?>
								<button id="productProjectProjectDetailButton"
									dojoType="dijit.form.Button" showlabel="false"
									title="<?php echo i18n('showDetail')?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
									<script type="dojo/connect" event="onClick" args="evt">
                    showDetail('productProjectProject', 0, 'Project', false); // should not create project here 
                 </script>
								</button>
							  <?php }?>	
						  </td>
						</tr>
						<tr>
							<td class="dialogLabel"><label for="productProjectProduct"><?php echo i18n("colIdProduct") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td><select dojoType="dijit.form.FilteringSelect"
								<?php echo autoOpenFilteringSelect();?>
								id="productProjectProduct" name="productProjectProduct"
								<?php if ($idProduct) echo ' readonly="readonly" ';?>
								class="input" value="<?php echo $idProduct;?>" required="required">
                 <?php htmlDrawOptionForReference('idProduct', $idProduct, null, true);?>
               </select>
               <?php if (!$idProduct) {?>
								<button id="productProjectProductDetailButton"
									dojoType="dijit.form.Button" showlabel="false"
									title="<?php echo i18n('showDetail')?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
									<script type="dojo/connect" event="onClick" args="evt">
                    var canCreate=("<?php echo securityGetAccessRightYesNo('menuProduct','create');?>"=="YES")?1:0;
                    showDetail('productProjectProduct', canCreate, 'Product', false);
                 </script>
								</button>
								<?php }?>
								</td>
						</tr>
						<tr>
							<td class="dialogLabel"><label for="productProjectStartDate"><?php echo i18n("colStartDate");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td>
								<div id="productProjectStartDate" name="productProjectStartDate"
									value="<?php echo $pp->startDate;?>" dojoType="dijit.form.DateTextBox"
									constraints="{datePattern:browserLocaleDateFormatJs}"
									style="width: 100px" class="input" hasDownArrow="true"></div>
							</td>
						</tr>
						<tr>
							<td class="dialogLabel"><label for="productProjectEndDate"><?php echo i18n("colEndDate");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td>
								<div id="productProjectEndDate" name="productProjectEndDate"
									value="<?php echo $pp->endDate;?>" dojoType="dijit.form.DateTextBox"
									constraints="{datePattern:browserLocaleDateFormatJs}"
									style="width: 100px" class="input" hasDownArrow="true"></div>
							</td>
						</tr>
						<tr>
							<td class="dialogLabel"><label for="productProjectIdle"><?php echo i18n("colIdle");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td>
								<div id="productProjectIdle" name="productProjectIdle"
									dojoType="dijit.form.CheckBox" type="checkbox" <?php if ($pp->idle) echo ' checked="checked" ';?> ></div>
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
			<td align="center"><input type="hidden" id="productProjectAction">
				<button class="mediumTextButton" dojoType="dijit.form.Button"
					type="button" onclick="dijit.byId('dialogProductProject').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
				<button class="mediumTextButton" dojoType="dijit.form.Button"
					type="submit" id="dialogProductProjectSubmit"
					onclick="protectDblClick(this);saveProductProject();return false;">
          <?php echo i18n("buttonOK");?>
        </button></td>
		</tr>
	</table>
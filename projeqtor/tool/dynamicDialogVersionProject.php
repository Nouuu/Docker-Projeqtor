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

if (! array_key_exists ( 'idVersionProject', $_REQUEST )) {
  throwError ( 'Parameter idVersionProject not found in REQUEST' );
}
$idVersionProject = $_REQUEST ['idVersionProject'];
Security::checkValidId ( $idVersionProject );

if (! array_key_exists ( 'idVersion', $_REQUEST )) {
  throwError ( 'Parameter idVersion not found in REQUEST' );
}
$idVersion = $_REQUEST ['idVersion'];
Security::checkValidId ( $idVersion );

if (! array_key_exists ( 'idProject', $_REQUEST )) {
  throwError ( 'Parameter idProject not found in REQUEST' );
}
$idProject = $_REQUEST ['idProject'];
Security::checkValidId ( $idProject );

$vp=new VersionProject($idVersionProject);

$vers=new Version($idVersion,true);
$idProduct=$vers->idProduct;

?>
	<table>
		<tr>
			<td>
				<form dojoType="dijit.form.Form" id='versionProjectForm'
					name='versionProjectForm' onSubmit="return false;">
					<input id="versionProjectId" name="versionProjectId" type="hidden"
						value="<?php echo $idVersionProject;?>" />
					<table>
						<tr>
							<td class="dialogLabel"><label for="versionProjectProject"><?php echo i18n("colIdProject") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td><select dojoType="dijit.form.FilteringSelect"
								<?php echo autoOpenFilteringSelect();?>
								id="versionProjectProject" name="versionProjectProject"
								<?php if ($idProject) echo ' readonly="readonly" ';?>
								class="input" value="<?php echo $idProject;?>" required="required">
                 <?php htmlDrawOptionForReference('idProject', $idProject, null, true);?>
               </select>
               <?php if (! $idProject) { ?>
								<button id="versionProjectProjectDetailButton"
									dojoType="dijit.form.Button" showlabel="false"
									title="<?php echo i18n('showDetail')?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
									<script type="dojo/connect" event="onClick" args="evt">
                    showDetail('versionProjectProject', 0, 'Project', false); // should not create project here 
                 </script>
								</button>
							  <?php }?>	
						  </td>
						</tr>
						<tr>
							<td class="dialogLabel"><label for="versionProjectProduct"><?php echo i18n("colIdProduct") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td><select dojoType="dijit.form.FilteringSelect"
								<?php echo autoOpenFilteringSelect();?>
								id="versionProjectProduct" name="versionProjectProduct"
								<?php if ($idVersion) echo ' readonly="readonly" ';?>
								class="input" >
									<script type="dojo/connect" event="onChange" args="evt">
                   dijit.byId('versionProjectVersion').set('value',null);
                   if (trim(this.value)) {
                    refreshList('idProductVersion', 'idProduct', this.value, null, 'versionProjectVersion', true,null,null,'ProjectVersion'); // Set object class to ProjectVersion to list all versions
                   } else {
                    refreshList('idProductVersion', null, null, null, 'versionProjectVersion', true,null,null,'ProjectVersion'); // Set object class to ProjectVersion to list all versions
                   }
                </script>
                 <?php htmlDrawOptionForReference('idProduct', $idProduct, null, false);?>
               </select>
               <?php if (!$idVersion) {?>
								<button id="versionProjectProductDetailButton"
									dojoType="dijit.form.Button" showlabel="false"
									title="<?php echo i18n('showDetail')?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
									<script type="dojo/connect" event="onClick" args="evt">
                    showDetail('versionProjectProduct', 0, 'Product', false); // should not create product here 
                 </script>
								</button>
								<?php }?>
							</td>
						</tr>
						<tr>
							<td class="dialogLabel"><label for="versionProjectVersion"><?php echo i18n("colIdVersion") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td><select dojoType="dijit.form.FilteringSelect"
								<?php echo autoOpenFilteringSelect();?>
								id="versionProjectVersion" name="versionProjectVersion"
								<?php if ($idVersion) echo ' readonly="readonly" ';?>
								class="input" value="<?php echo $idVersion;?>" required="required">
                 <?php htmlDrawOptionForReference('idProductVersion', $idVersion, null, true);?>
               </select>
               <?php if (!$idVersion) {?>
								<button id="versionProjectVersionDetailButton"
									dojoType="dijit.form.Button" showlabel="false"
									title="<?php echo i18n('showDetail')?>" iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
									<script type="dojo/connect" event="onClick" args="evt">
                    var canCreate=0;
                    if (canCreateArray['ProductVersion'] == "YES") {
                      canCreate=1;
                    }
                    showDetail('versionProjectVersion', canCreate, 'ProductVersion', false);
                 </script>
								</button>
								<?php }?>
								</td>
						</tr>
						<?php if (Parameter::getGlobalParameter('displayMilestonesStartDelivery')!='YES') { //ADD qCazelles - Ticket #119 ?>
						<tr>
							<td class="dialogLabel"><label for="versionProjectStartDate"><?php echo i18n("colStartDate");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td>
								<div id="versionProjectStartDate" name="versionProjectStartDate"
									value="<?php echo $vp->startDate;?>" dojoType="dijit.form.DateTextBox"
									constraints="{datePattern:browserLocaleDateFormatJs}"
									style="width: 100px" class="input" hasDownArrow="true"></div>
							</td>
						</tr>
						<tr>
							<td class="dialogLabel"><label for="versionProjectEndDate"><?php echo i18n("colEndDate");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td>
								<div id="versionProjectEndDate" name="versionProjectEndDate"
									value="<?php echo $vp->endDate;?>" dojoType="dijit.form.DateTextBox"
									constraints="{datePattern:browserLocaleDateFormatJs}"
									style="width: 100px" class="input" hasDownArrow="true"></div>
							</td>
						</tr>
												<?php //ADD qCazelles - Ticket #119
 						  } else {
 						    echo '<input type="hidden" name="versionProjectStartDate" />';
 						    echo '<input type="hidden" name="versionProjectEndDate" />';
 						  }
 						  //END ADD qCazelles - Ticket #119 ?>
						<tr>
							<td class="dialogLabel"><label for="versionProjectIdle"><?php echo i18n("colIdle");?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
							</td>
							<td>
								<div id="versionProjectIdle" name="versionProjectIdle"
									dojoType="dijit.form.CheckBox" type="checkbox" 
									<?php 
									if ($vp->idle) { 
									  echo ' checked="checked" ';
									  if ($vers->idle) echo ' disabled';
									}
									?> ></div>
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
			<td align="center"><input type="hidden" id="versionProjectAction">
				<button class="mediumTextButton" dojoType="dijit.form.Button"
					type="button" onclick="dijit.byId('dialogVersionProject').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
				<button class="mediumTextButton" dojoType="dijit.form.Button"
					type="submit" id="dialogVersionProjectSubmit"
					onclick="protectDblClick(this);saveVersionProject();return false;">
          <?php echo i18n("buttonOK");?>
        </button></td>
		</tr>
	</table>
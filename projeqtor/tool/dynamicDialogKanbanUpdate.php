<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
******************************************************************************
*** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
******************************************************************************
*
* Copyright 2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
*
* This file is an add-on to ProjeQtOr, packaged as a plug-in module.
* It is NOT distributed under an open source license.
* It is distributed in a proprietary mode, only to the customer who bought
* corresponding licence.
* The company ProjeQtOr remains owner of all add-ons it delivers.
* Any change to an add-ons without the explicit agreement of the company
* ProjeQtOr is prohibited.
* The diffusion (or any kind if distribution) of an add-on is prohibited.
* Violators will be prosecuted.
*
*** DO NOT REMOVE THIS NOTICE ************************************************/

/*
 * ============================================================================
 * Habilitation defines right to the application for a menu and a profile.
 */
require_once "../tool/projeqtor.php";
$needRessource = false;
if (array_key_exists ( 'needRessource', $_REQUEST )) {
	$needRessource = true;
}

$needResult = false;
if (array_key_exists ( 'needResult', $_REQUEST )) {
	$needResult = true;
}

$needResolution = false;
if (array_key_exists ( 'needResolution', $_REQUEST )) {
	$needResolution = true;
}

if (! array_key_exists ( 'typeDynamic', $_REQUEST )) {
	throwError ( 'Parameter typeDynamic not found in REQUEST' );
}
$typeDynamic = $_REQUEST ['typeDynamic'];

if ($typeDynamic == 'update') {
	if (! array_key_exists ( 'idTicket', $_REQUEST )) {
		throwError ( 'Parameter idTicket not found in REQUEST' );
	}
	$idTicket = $_REQUEST ['idTicket'];
	
	if (! array_key_exists ( 'idStatus', $_REQUEST )) {
		throwError ( 'Parameter idStatus not found in REQUEST' );
	}
	$idStatus = $_REQUEST ['idStatus'];
	$ticket = new Ticket ( $idTicket );
	$detailHeight = 350;
	$detailWidth = 600;
	?>
<form id='kanbanResultForm' name='kanbanResultForm'
	onSubmit="return false;">
	<table style="width: 100%;">
<?php
	if ($needRessource) {
		?>
<tr>
			<td>
				<div class="dialogLabel"><?php echo i18n("colMandatoryResourceOnHandled");?></div>
			</td>
		</tr>
		<tr>
			<td><select dojoType="dijit.form.FilteringSelect"
				class="input required" required="true"
				<?php echo autoOpenFilteringSelect ();?> name="kanbanResourceList"
				id="kanbanResourceList">
    <?php
		htmlDrawOptionForReference ( "idResource", getSessionUser ()->isResource ? getSessionUser ()->id : null, null, true, "idProject", $ticket->idProject );
		?>
    </select></td>
		</tr>
<?php
	}
	if ($needResult) {
		?>
<tr>
			<td><div class="dialogLabel"><?php echo i18n("colMandatoryResultOnDone");?></div></td>
		</tr>
		<tr>
			<td><input id="kanbanResultEditorType" name="kanbanResultEditorType"
				type="hidden" value="<?php if (isNewGui()) echo 'CK'; else echo getEditorType();?>" />
         <?php if (getEditorType()=="CK" or isNewGui()) {?> 
          <textarea style="width:<?php echo $detailWidth;?>px; height:<?php echo $detailHeight;?>px"
          name="kanbanResult" class="required" id="kanbanResult"></textarea>
        <?php } else if (getEditorType()=="text"){?>
          <textarea dojoType="dijit.form.Textarea" id="kanbanResult"
					name="kanbanResult" style="width: 500px;" maxlength="4000"
					class="input required"
					onClick="dijit.byId('kanbanResult').setAttribute('class','');"></textarea>
        <?php } else {?>
          <textarea dojoType="dijit.form.Textarea" type="hidden"
					id="kanbanResult" name="kanbanResult" style="display: none;"></textarea>
				<div data-dojo-type="dijit.Editor" id="kanbanResultEditor"
             data-dojo-props="onChange:function(){window.top.dojo.byId('kanbanResult').value=arguments[0];}
              ,plugins:['removeFormat','bold','italic','underline','|', 'indent', 'outdent', 'justifyLeft', 'justifyCenter', 
                        'justifyRight', 'justifyFull','|','insertOrderedList','insertUnorderedList','|']
              ,onKeyDown:function(event){window.top.onKeyDownFunction(event,'kanbanResultEditor',this);}
              ,onBlur:function(event){window.top.editorBlur('kanbanResultEditor',this);}
              ,extraPlugins:['dijit._editor.plugins.AlwaysShowToolbar','foreColor','hiliteColor']"
              style="color:#606060 !important; background:none; 
                padding:3px 0px 3px 3px;margin-right:2px;height:<?php echo $detailHeight;?>px;width:<?php echo $detailWidth;?>px;min-height:16px;overflow:auto;"
              class="input required"></div>
        <?php }?>

      </td>
		</tr>
<?php
	}
	if ($needResolution) {
		?>
<tr>
			<td>
				<div class="dialogLabel"><?php echo i18n("colIdResolution");?></div>
			</td>
		</tr>
		<tr>
			<td><select dojoType="dijit.form.FilteringSelect"
				class="input required" required="true" name="kanbanResolutionList"
				id="kanbanResolutionList">
    <?php echo autoOpenFilteringSelect ();?>
    <?php htmlDrawOptionForReference("idResolution", null, null ,true);?>
    </select></td>
		</tr>
<?php
	}
	?>
			<tr><td>&nbsp;</td></tr>
    <tr>
			<td align="center"><input type="hidden" id="dialogKanbanResultAction">
				<button class="mediumTextButton" dojoType="dijit.form.Button"
					type="button"
					onclick="dijit.byId('dialogKanbanUpdate').hide();formChangeInProgress=false;loadContent('../view/kanbanView.php', 'divKanbanContainer');">
          <?php echo i18n("buttonCancel");?>
        </button>
				<button class="mediumTextButton" id="dialogKanbanUpdateSubmit"
					dojoType="dijit.form.Button" type="submit"
					onclick="protectDblClick(this);saveKanbanResult(<?php echo $idTicket;?>,'Status',<?php echo $idStatus;?>);return false;">
          <?php echo i18n("buttonOK");?>
        </button></td>
		</tr>
	</table>
</form>
<?php
} else if ($typeDynamic == "addKanban") {
	?>
<form id='kanbanResultForm' name='kanbanResultForm'
	onSubmit="return false;">
	<div style="height: <?php echo (isNewGui())?'34':'30';?>px;">
		<label class="dialogLabel"><?php echo i18n("colName");?> <?php if (!isNewGui()) echo ': ';?></label> <input
			id="kanbanName" name="kanbanName" style="width: 150px;" type="text"
			dojoType="dijit.form.TextBox" class="input required" value="" />
	</div>
	<div style="height: <?php echo (isNewGui())?'34':'30';?>px;;">
		<label class="dialogLabel"><?php echo i18n("colRefType");?> <?php if (!isNewGui()) echo ': ';?></label>
		<select dojoType="dijit.form.FilteringSelect" class="input required"
			required="true" <?php echo autoOpenFilteringSelect();?>
			name="kanbanReffList" id="kanbanReffList">
			<?php if (Security::checkDisplayMenuForUser('Ticket',false)) {?><option value="Ticket"><?php echo i18n('Ticket');?></option><?php }?>
			<?php if (Security::checkDisplayMenuForUser('Activity',false)) {?><option value="Activity"><?php echo i18n('Activity');?></option><?php }?>
			<?php if (Security::checkDisplayMenuForUser('Action',false)) {?><option value="Action"><?php echo i18n('menuAction');?></option><?php }?>
			<?php if (Security::checkDisplayMenuForUser('Requirement',false)) {?><option value="Requirement"><?php echo i18n('menuRequirement');?></option><?php }?>
			<script type="dojo/connect" event="onChange" args="evt">
          var param = dijit.byId('kanbanReffList').get('value'); 
          dijit.byId('kanbanTypeList').store;
          dojo.byId("kanbanTypeList").value="";
          kanbanRefreshListType("kanbanReffList", "kanbanTypeList", param, "Activity");
        </script>
		</select>
	</div>
	<div style="height: <?php echo (isNewGui())?'34':'30';?>px;;">
		<label class="dialogLabel"><?php echo i18n("colType");?> <?php if (!isNewGui()) echo ': ';?></label> 
		<select
			dojoType="dijit.form.FilteringSelect" class="input required" required="true"
			default="" style="width: 150px;" name="kanbanTypeList"
			id="kanbanTypeList">
			<option value=""></option>
			<option value="Status"><?php echo i18n("colIdStatus");?></option>
			<option value="TargetProductVersion"><?php echo i18n("colIdTargetProductVersion");?></option>
			<option value="Activity"><?php echo i18n("colPlanningActivity");?></option>
		</select>
	</div>
	<div style="height: 40px;">
		<label class="dialogLabel"><?php echo i18n("kanbanSharedCheck");?> <?php if (!isNewGui()) echo ': ';?></label>
		<div style="" id="kanbanShared" name="kanbanShared" type="checkbox"
			dojoType="dijit.form.CheckBox"></div>
	</div>
	<table style="width: 100%;">
		<tr>
			<td align="center"><input type="hidden" id="dialogKanbanResultAction">
				<button class="mediumTextButton" dojoType="dijit.form.Button"
					type="button" onclick="dijit.byId('dialogKanbanUpdate').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
				<button class="mediumTextButton" id="dialogKanbanUpdateSubmit"
					dojoType="dijit.form.Button" type="submit"
					onclick="protectDblClick(this);plgAddKanban();return false;">
          <?php echo i18n("buttonOK");?>
        </button></td>
		</tr>
	</table>
</form>
<?php
} else if ($typeDynamic == "addColumnKanban") {
	if (! array_key_exists ( 'typeD', $_REQUEST )) {
		throwError ( 'Parameter typeD not found in REQUEST' );
	}
	$typeD = $_REQUEST ['typeD'];
	
	if (! array_key_exists ( 'idKanban', $_REQUEST )) {
		throwError ( 'Parameter idKanban not found in REQUEST' );
	}
	$idFrom = - 1;
	if (array_key_exists ( 'idFrom', $_REQUEST )) {
		$idFrom = $_REQUEST ['idFrom'];
	}
	$idKanban = $_REQUEST ['idKanban'];
	$kanban = new Kanban ( $idKanban, true );
	$json = json_decode ( $kanban->param, true );
	$listForbiden = array ();
	if (isset ( $json ['column'] ))
		foreach ( $json ['column'] as $line ) {
			$listForbiden [] = $line ['from'];
		}
	$typeData = $json ['typeData'];
	if ($typeD == 'Status') {
		$status = new Status ();
		$tableName = $status->getDatabaseTableName ();
		$workflowStatus = new WorkflowStatus ();
		$tableName2 = $workflowStatus->getDatabaseTableName ();
		$type = new Type ();
		$tableName3 = $type->getDatabaseTableName ();
		$result = Sql::query ( "SELECT s.id as id, s.name as name from $tableName s where s.idle=0 and (s.id in (select idStatusFrom from $tableName2 w, $tableName3 t where t.idWorkflow=w.idWorkflow and t.scope='$typeData')
      or s.id in (select idStatusTo from $tableName2 w, $tableName3 t where t.idWorkflow=w.idWorkflow and t.scope='$typeData') ) order by s.sortOrder" );
		$listToHave = array ();
		while ( $line = Sql::fetchLine ( $result ) ) {
			$listToHave [$line ['id']] = $line ['name'];
		}
	} else {
		$crit = array (
				'idle' => '0' 
		);
		if ($typeD != 'Project' and property_exists ( $typeD, 'idProject' ) 
		and array_key_exists ( 'project', $_SESSION ) and $_SESSION ['project'] != '*' and strpos($_SESSION ['project'],',')===null) {
			$crit ['idProject'] = $_SESSION ['project'];
		}
		$listToHave = SqlList::getListWithCrit ( $typeD, $crit );
		if ($typeD == 'TargetProductVersion') {
			$restrictArray = getSessionUser ()->getVisibleVersions ();
			$listToHave = array_intersect_key ( $listToHave, $restrictArray );
		}
	}
	$listFinal = array ();
	foreach ( $listToHave as $elmId => $elmName ) {
		$find = false;
		for($iterateur = 0; $iterateur < count ( $listForbiden ) && ! $find; $iterateur ++) {
			$find = $listForbiden [$iterateur] == $elmId;
		}
		if (! $find) {
			$listFinal [$elmId] = $elmName;
		}
	}
	$valText = '';
	if ($idFrom != - 1) {
		if (isset ( $json ['column'] ))
			foreach ( $json ['column'] as $line ) {
				if ($line ['from'] == $idFrom)
					$valText = $line ['name'] != null ? $line ['name'] : '';
			}
	}
	?>
<form id='kanbanResultForm' name='kanbanResultForm'
	onSubmit="return false;">
	<table style="width: 100%;">
<?php
	if ($typeD == "Status") {
		?>
<?php echo '<div style="height:'.((isNewGui())?'34':'30').'x;"><label class="dialogLabel">'.i18n("colName");?> <?php if (!isNewGui()) echo ': ';?></label>
		<input id="kanbanName" name="kanbanName" style="width: 150px"
			type="text" dojoType="dijit.form.TextBox" class="input required"
			value="<?php echo $valText;?>" />
		</div>
<?php
	} else {
		echo '<div style="height:'.((isNewGui())?'34':'30').'px;"><label class="dialogLabel">' . i18n ( "colName" );
		?> <?php if (!isNewGui()) echo ': ';?></label>
		<input id="kanbanName" name="kanbanName" style="width: 150px"
			type="text" dojoType="dijit.form.TextBox" class="input"
			value="<?php echo $valText;?>" />
		</div>
<?php
	}
	if ($idFrom == - 1) {
		$trad = "colIdTargetProductVersion";
		if ($typeD == "Activity")
			$trad = "colPlanningActivity";
		if ($typeD == "Status")
			$trad = "colIdStatus";
		echo '<div style="height:40px;"><label class="dialogLabel">' . i18n ( $trad );
		?> <?php if (!isNewGui()) echo ': ';?></label>
		<select dojoType="dijit.form.FilteringSelect" class="input required"
			required="true" style="width: 150px;" name="kanbanTypeList"
			id="kanbanTypeList">
<?php foreach ($listFinal as $elmId => $elmName){?>
<option value="<?php echo $elmId;?>"><?php echo $elmName;?></option>
<?php }?>
</select>
		</div>
<?php }?>
	  <tr><td>&nbsp;</td></tr>
      <tr>
			<td align="center"><input type="hidden" id="dialogKanbanResultAction">
				<button class="mediumTextButton" dojoType="dijit.form.Button"
					type="button"
					onclick="dijit.byId('dialogKanbanUpdate').hide();formChangeInProgress=false;">
          <?php echo i18n("buttonCancel");?>
        </button>
				<button class="mediumTextButton" id="dialogKanbanUpdateSubmit"
					dojoType="dijit.form.Button" type="submit"
					onclick="protectDblClick(this);plgAddColumnKanban(<?php echo $idKanban;?>,<?php echo $idFrom;?>,<?php echo $typeD=="Status"?'true':'false';?>,'<?php echo $typeD;?>');return false;">
          <?php echo i18n("buttonOK");?>
        </button></td>
		</tr>
	</table>
</form>
<?php
} else if ($typeDynamic == "editKanban") {
	$idKanban = $_REQUEST ['idKanban'];
	$kanban = new Kanban ( $idKanban );
	$paramJson = json_decode ( $kanban->param, true );
	if (! isset ( $paramJson ['typeData'] )) {
		$paramJson ['typeData'] = 'Ticket';
		$kanban->param = json_encode ( $paramJson );
		$kanban->save ();
	}
	?>
<form id='kanbanResultForm' name='kanbanResultForm'
	onSubmit="return false;">
	<div style="height: 40px;">
		<label class="dialogLabel"><?php echo i18n("colName");?> <?php if (!isNewGui()) echo ': ';?></label><input
			id="kanbanName" name="kanbanName" style="width: 150px;" type="text"
			dojoType="dijit.form.TextBox" class="input required"
			value="<?php echo $kanban->name;?>" />
	</div>
	<table style="width: 100%;">
		<tr>
			<td>
				<div style="height: 40px;">
					<label class="dialogLabel"><?php echo i18n("colReffType");?> <?php if (!isNewGui()) echo ': ';?></label>
					<select dojoType="dijit.form.FilteringSelect"
						class="input required" required="true"
						<?php echo autoOpenFilteringSelect ();?> name="kanbanReffList"
						id="kanbanReffList">
						<option value="Ticket"
							<?php echo ($paramJson['typeData']=='Ticket' ? 'selected' : '');?>><?php echo i18n('Ticket');?></option>
						<option value="Activity"
							<?php echo ($paramJson['typeData']=='Activity' ? 'selected' : '');?>><?php echo i18n('Activity');?></option>
					</select>
				</div>
			</td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td align="center"><input type="hidden" id="dialogKanbanResultAction">
				<button class="mediumTextButton" dojoType="dijit.form.Button"
					type="button" onclick="dijit.byId('dialogKanbanUpdate').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
				<button class="mediumTextButton" id="dialogKanbanUpdateSubmit"
					dojoType="dijit.form.Button" type="submit"
					onclick="protectDblClick(this);saveEditKanban(<?php echo $idKanban;?>);return false;">
          <?php echo i18n("buttonOK");?>
        </button></td>
		</tr>
	</table>
</form>
<?php
}
?>
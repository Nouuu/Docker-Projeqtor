<?php // ==============================================================================================================
      // =========================================== FUNTIONS ==========================================================
      // ==============================================================================================================

// $planningType defined which planning is displayed
//   => 'planning'  => main planning
//   => 'portfolio' => project portfolio
//   => 'resource'  => resource planning
//   => 'global'    => global planning
//   => 'version'   => product version et component version planning
//   => 'contract'  => contract 

// ================================================== BUTTON PLAN 
function drawButtonPlan() {?>
  <button id="planButton" dojoType="dijit.form.Button" showlabel="false"
    title="<?php echo i18n('buttonPlan');?>" class="buttonIconNewGui detailButton"
    iconClass="dijitIcon iconPlanStopped" >
    <script type="dojo/connect" event="onClick" args="evt">
      showPlanParam();
      return false;
    </script>
  </button>
<?php 
}

// ================================================== CHECKBOX AUTOMATIC PLANNING 
function drawOptionAutomatic() {
  global $automaticRunPlanning,$displayWidthPlan;?>
  <div style="white-space:nowrap;">
  <?php if (isNewGui()) htmlDrawSwitch('automaticRunPlan',$automaticRunPlanning);?>
    <span title="<?php echo i18n('automaticRunPlanHelp');?>" dojoType="dijit.form.CheckBox" 
      <?php if (isNewGui()) echo 'style="display:none"';?>
      type="checkbox" id="automaticRunPlan" name="automaticRunPlan" id="automaticRunPlan" class="whiteCheck"
      <?php if ( $automaticRunPlanning) {echo 'checked="checked"'; } ?>  >  
      <script type="dojo/connect" event="onChange" args="evt">
        saveUserParameter('automaticRunPlanning',((this.checked)?'1':'0'));
      </script>                    
    </span>&nbsp;<?php if ($displayWidthPlan>1250) echo i18n('automaticRunPlan'); else echo i18n('automaticRunPlanShort');?>
  </div>
<?php 
}

// ================================================== FIELD START DATE FOR DISPLAY
function drawFieldStartDate() {
  global $projectDate,$startDate; ?>         
  <div dojoType="dijit.form.DateTextBox"
  	<?php if (sessionValueExists('browserLocaleDateFormatJs')) {
			echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
		}?>
    id="startDatePlanView" name="startDatePlanView"
    invalidMessage="<?php echo i18n('messageInvalidDate')?>"
    type="text" maxlength="10" 
    <?php if ($projectDate) {echo 'disabled'; } ?> 
    style="width:100px; text-align: center;" class="input roundedLeft"
    hasDownArrow="true"
    value="<?php if(sessionValueExists('startDatePlanView') and !$projectDate){ echo getSessionValue('startDatePlanView'); }else{ echo $startDate; } ?>" >
    <script type="dojo/method" event="onChange" >
      saveDataToSession('startDatePlanView',formatDate(dijit.byId('startDatePlanView').get("value")), true);
      refreshJsonPlanning();
    </script>
  </div>
<?php 
}

// ================================================== FIELD END DATE FOR DISPLAY
function drawFieldEndDate() {
  global $projectDate,$endDate; ?>                           
    <div dojoType="dijit.form.DateTextBox"
      <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
				echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
			}?>
      id="endDatePlanView" name="endDatePlanView"
      invalidMessage="<?php echo i18n('messageInvalidDate')?>"
      type="text" maxlength="10"
      <?php if ($projectDate) {echo 'disabled'; } ?> 
      style="width:100px; text-align: center;" class="input roundedLeft"
      hasDownArrow="true"
      value="<?php if(sessionValueExists('endDatePlanView') and !$projectDate){ echo getSessionValue('endDatePlanView'); }else{ echo $endDate; } ?>" >
      <script type="dojo/method" event="onChange" >
        saveDataToSession('endDatePlanView',formatDate(dijit.byId('endDatePlanView').get("value")), false);
        refreshJsonPlanning();
      </script>
    </div>
<?php 
}

// ================================================== CHECKBOX SAVE DATES
function drawOptionSaveDates() {
  global $projectDate, $saveDates; ?>
  <span title="<?php echo i18n('saveDates')?>" dojoType="dijit.form.CheckBox"
    type="checkbox" id="listSaveDates" name="listSaveDates" class="whiteCheck"
    <?php if ($projectDate) {echo 'disabled'; } ?> 
    <?php if ( $saveDates) {echo 'checked="checked"'; } ?>  >
    <script type="dojo/method" event="onChange" >
      refreshJsonPlanning();
    </script>
  </span>
  <span for="listSaveDates"><?php echo i18n("saveDates");?></span>
<?php 
}

// ================================================== CHECKBOX SHOW ALL THE PROJECT
function drawOptionAllProject() {  
  global $projectDate;?>                      
  <span title="<?php echo i18n("projectDate")?>" dojoType="dijit.form.CheckBox"
    type="checkbox" id="projectDate" name="projectDate" class="whiteCheck"
    <?php if ($projectDate) {echo 'checked="checked"'; } ?>  >
    <script type="dojo/method" event="onChange" >
      saveUserParameter('projectDate',((this.checked)?'1':'0'));
      var now = formatDate(new Date());
      if (this.checked == false) {
        //dojo.setAttr('startDatePlanView', 'value', date.toLocaleDateString());
        dijit.byId('startDatePlanView').set("value",now);
        enableWidget("startDatePlanView");
        enableWidget("endDatePlanView");
        enableWidget("listSaveDates");
      } else {
        dijit.byId('startDatePlanView').reset();
        dijit.byId('endDatePlanView').reset();
        dijit.byId('listSaveDates').set('checked', false);
        disableWidget("startDatePlanView");
        disableWidget("endDatePlanView");
        disableWidget("listSaveDates");
      }
      refreshJsonPlanning();
    </script>
  </span>
  <span for="projectDate"><?php echo i18n("projectDate");?></span>
<?php 
}

// ================================================== BUTTONS FOR PLANNING FUNCTIONS (save validated dates, baselines, print, pdf export)
function drawButtonsPlanning() { 
  global $canPlan,$showValidationButton, $planningType;?>
  <table>
    <tr>
      <?php 
      if ($canPlan and ($planningType=='planning' or $planningType=='global') ) { 
        if($showValidationButton){
      ?>
      <td colspan="1" width="32px">
        <button id="savePlanningButton" dojoType="dijit.form.Button" showlabel="false"
         title="<?php echo i18n('validatePlanning');?>" 
         iconClass="dijitButtonIcon dijitButtonIconValidPlan" class="buttonIconNewGui detailButton">
         <script type="dojo/connect" event="onClick" args="evt">
		       showPlanSaveDates();
           return false;  
         </script>
        </button>
      </td>
      <?php 
        }
      ?>
      <td colspan="1" width="32px">
        <button id="saveBaselineButton" dojoType="dijit.form.Button" showlabel="false"
          title="<?php echo i18n('savePlanningBaseline');?>"
          iconClass="dijitButtonIcon dijitButtonIconSavePlan" class="buttonIconNewGui detailButton">
          <script type="dojo/connect" event="onClick" args="evt">
		        showPlanningBaseline();
            return false;  
          </script>
        </button>
      </td>
      <?php 
      }
      ?>  
      <td colspan="1" width="32px">
        <button title="<?php echo i18n('printPlanning')?>"
         dojoType="dijit.form.Button"
         id="listPrint" name="listPrint"
         iconClass="dijitButtonIcon dijitButtonIconPrint" class="buttonIconNewGui detailButton" showLabel="false">
          <script type="dojo/connect" event="onClick" args="evt">
            <?php 
            $ganttPlanningPrintOldStyle=Parameter::getGlobalParameter('ganttPlanningPrintOldStyle');
            if (!$ganttPlanningPrintOldStyle) {$ganttPlanningPrintOldStyle="NO";}
            if ($ganttPlanningPrintOldStyle=='YES') {?>
	            showPrint("../tool/jsonPlanning.php", 'planning');
            <?php } else { ?>
              showPrint("planningPrint.php", 'planning');
            <?php }?>                          
          </script>
        </button>
      </td>
      <td colspan="1" width="32px">
        <button title="<?php echo i18n('reportPrintPdf')?>"
          dojoType="dijit.form.Button"
          id="listPrintPdf" name="listPrintPdf"
          iconClass="dijitButtonIcon dijitButtonIconPdf" class="buttonIconNewGui detailButton" showLabel="false">
          <script type="dojo/connect" event="onClick" args="evt">
            var paramPdf='<?php echo Parameter::getGlobalParameter("pdfPlanningBeta");?>';
            if(paramPdf!='false') planningPDFBox();
            else showPrint("../tool/jsonPlanning_pdf.php", 'planning', null, 'pdf');
          </script>
        </button>
      </td>
      <?php if ($planningType=='planning' or $planningType=='global') {?>
      <td width="32px" style="padding-right:10px;">
        <button title="<?php echo i18n('reportExportMSProject')?>"
          dojoType="dijit.form.Button"
          id="listPrintMpp" name="listPrintMpp"
          iconClass="dijitButtonIcon dijitButtonIconMSProject" class="buttonIconNewGui detailButton" showLabel="false">
          <script type="dojo/connect" event="onClick" args="evt">
            showPrint("../tool/jsonPlanning.php", 'planning', null, 'mpp');
          </script>
        </button>
        <input type="hidden" id="outMode" name="outMode" value="" />
      </td>
      <?php }?>
    </tr>
  </table>
<?php 
}

// ================================================== BUTTONS DEFAULT (NEW, FILTER, COLUMNS)
function drawButtonsDefault() {
  global $objectClass, $planningType, $showListFilter;
  ?>
  <table style="width:10px">
    <tr>
      <?php 
      if ($planningType=='planning' or $planningType=='resource' or $planningType=='global' or $planningType=='version') {?>
        <td colspan="1" width="51px" style="<?php if (isNewGui()) echo 'padding-right: 5px;';?>">
          <?php // ================================================================= NEW ?>
          <?php if ($planningType=='version') {?><div id ="addNewActivity" style="visibility:<?php echo ($showListFilter=='true')?'visible':'hidden';?>;"><?php } ?>
          <div dojoType="dijit.form.DropDownButton"
            class="comboButton"   
            id="planningNewItem" jsId="planningNewItem" name="planningNewItem" 
            showlabel="false" class="" iconClass="dijitButtonIcon dijitButtonIconNew"
            title="<?php echo i18n('comboNewButton');?>">
            <span>title</span>
            <div dojoType="dijit.TooltipDialog" class="white" style="width:200px;">   
              <div style="font-weight:bold; height:25px;text-align:center"><?php echo i18n('comboNewButton');?>      </div>
              <?php 
              $arrayItems=array('Project','Activity','Milestone','Meeting','PeriodicMeeting','TestSession');
              if ($planningType=='resource' or $planningType=='version') $arrayItems=array('Activity');
              if ($planningType=='global') $arrayItems=array_merge($arrayItems,array('Ticket','Action','Decision','Delivery','Risk','Issue','Opportunity','Question'));
              foreach($arrayItems as $item) {
                $canCreate=securityGetAccessRightYesNo('menu' . $item,'create');
                if ($canCreate=='YES') {
                  if (! securityCheckDisplayMenu(null,$item) ) {
                    $canCreate='NO';
                  }
                }
                if ($canCreate=='YES') {?>
                  <div style="vertical-align:top;cursor:pointer;" class="newGuiIconText"
                    onClick="addNewItem('<?php echo $item;?>');" >
                    <table width:"100%"><tr style="height:22px" >
                      <td style="vertical-align:top; width: 30px;padding-left:5px"><?php echo formatIcon($item, 22, null, false);;?></td>    
                      <td style="vertical-align:top;padding-top:2px"><?php echo i18n($item)?></td>
                    </tr></table>   
                  </div>
                  <div style="height:5px;"></div>
                <?php 
                } 
              }?>
            </div>
          </div>
          <?php if ($planningType=='version') {?></div><?php } ?>        
        </td>   
      <?php
      } 
      if ($planningType=='global') {?>
        <td colspan="1" width="51px" style="<?php if (isNewGui()) echo 'padding-right: 5px;';?>">
          <?php drawGlobalItemsSelector();?>
        </td>  
      <?php 
      } 
      $activeFilter=false;
      if (is_array(getSessionUser()->_arrayFilters)) {
        if (array_key_exists('Planning', getSessionUser()->_arrayFilters)) {
          if (count(getSessionUser()->_arrayFilters['Planning'])>0) {
         	  foreach (getSessionUser()->_arrayFilters['Planning'] as $filter) {
         		  if (!isset($filter['isDynamic']) or $filter['isDynamic']=="0") {
         			  $activeFilter=true;
         		  }
         	  }
          }
        }
      }
      ?>
      <?php 
      if ($planningType=='planning' or $planningType=='resource' or $planningType=='version') {?>
        <td colspan="1" width="55px" style="padding-left:1px";>
          <?php // ================================================================= FILTER ?>
          <?php if ($planningType=='version') {?><div id="listFilterAdvanced" style="visibility:<?php echo ($showListFilter=='true')?'visible':'hidden';?>;"><?php }?>
          <button title="<?php echo i18n('advancedFilter')?>"  
            class="comboButton"
            dojoType="dijit.form.DropDownButton" 
            id="listFilterFilter" name="listFilterFilter"
            iconClass="dijitButtonIcon icon<?php echo($activeFilter)?'Active':'';?>Filter" showLabel="false">
            <?php 
            if(!isNewGui()){?>
              <script type="dojo/connect" event="onClick" args="evt">
                showFilterDialog();
              </script>
              <script type="dojo/method" event="onMouseEnter" args="evt">
                clearTimeout(closeFilterListTimeout);
                clearTimeout(openFilterListTimeout);
                openFilterListTimeout=setTimeout("dijit.byId('listFilterFilter').openDropDown();",popupOpenDelay);
              </script>
              <script type="dojo/method" event="onMouseLeave" args="evt">
                clearTimeout(openFilterListTimeout);
                closeFilterListTimeout=setTimeout("dijit.byId('listFilterFilter').closeDropDown();",2000);
              </script>
              <?php 
            }?>
            <div dojoType="dijit.TooltipDialog" id="directFilterList" style="z-index: 999999;<!-- display:none; --> position: absolute;">
              <?php 
              $objectClass='Planning';
              $dontDisplay=true;
              if(isNewGui())include "../tool/displayQuickFilterList.php";
              include "../tool/displayFilterList.php";
              if(!isNewGui()){?>
                <script type="dojo/method" event="onMouseEnter" args="evt">
                  clearTimeout(closeFilterListTimeout);
                  clearTimeout(openFilterListTimeout);
                </script>
                <script type="dojo/method" event="onMouseLeave" args="evt">
                  dijit.byId('listFilterFilter').closeDropDown();
                </script>
              <?php  
              }?>
            </div> 
          </button>
          <?php if ($planningType=='version') {?></div><?php }?>
        </td>
      <?php 
      }?>  
      <td colspan="1">
        <?php // ================================================================= COLUMNS SELECTOR ?> 
        <div dojoType="dijit.form.DropDownButton"
          id="planningColumnSelector" jsId="planningColumnSelector" name="planningColumnSelector"  
          showlabel="false" class="comboButton" iconClass="dijitButtonIcon dijitButtonIconColumn" 
          title="<?php echo i18n('columnSelector');?>">
          <span>title</span>
          <?php 
          $screenHeight=getSessionValue('screenHeight','1080');
          $columnSelectHeight=intval($screenHeight*0.6);?>
          <div dojoType="dijit.TooltipDialog" id="planningColumnSelectorDialog" class="white" style="width:300px;">   
            <script type="dojo/connect" event="onHide" data-dojo-args="evt">
              if (dndMoveInProgress) {  setTimeout('dijit.byId("planningColumnSelector").openDropDown();',1); }
            </script>
            <div id="dndPlanningColumnSelector" jsId="dndPlanningColumnSelector" dojotype="dojo.dnd.Source"  
              dndType="column" style="overflow-y:auto; max-height:<?php echo $columnSelectHeight;?>px; position:relative"
              withhandles="true" class="container">    
              <?php 
              if ($planningType=='portfolio') $portfolioPlanning=true;
              if ($planningType=='contract') $contractGantt=true;
              if ($planningType=='version') $versionPlanning=true;
              include('../tool/planningColumnSelector.php');?>
            </div>
            <div style="height:5px;"></div>    
            <div style="text-align: center;"> 
              <button title="" dojoType="dijit.form.Button" 
                id="" name="" showLabel="true"><?php echo i18n('buttonOK');?>
                <script type="dojo/connect" event="onClick" args="evt">
                  validatePlanningColumn();
                </script>
              </button>
            </div>          
          </div>
        </div>
      </td>
    </tr>
  </table>
<?php 
}

// ================================================== BASELINE
function drawOptionBaseline() {
  global $displayWidthPlan,$proj;?>
  <table>
    <tr>
      <td style="font-weight:bold;text-align:center;"><?php echo ucfirst(i18n('displayBaseline'));?></td>
    </tr>
    <tr>
      <td style="text-align:right;white-space:nowrap;">
        <?php 
        if (isNewGui()) echo ucfirst(i18n('baselineTop')) .'&nbsp;';
        else echo (($displayWidthPlan>1230)?i18n('baselineTop'):i18n('baselineTopShort')).'&nbsp;:&nbsp;';?>
        <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
          style="width:<?php echo ($displayWidthPlan>930)?'150':'80';?>px;"
          name="selectBaselineTop" id="selectBaselineTop"
          <?php echo autoOpenFilteringSelect();?> >
          <script type="dojo/method" event="onChange" >
            saveDataToSession("planningBaselineTop",this.value,false);
            refreshJsonPlanning();
          </script>
          <?php htmlDrawOptionForReference('idBaselineSelect', getSessionValue("planningBaselineTop"), null,false,null,null);?>
        </select>
      </td>
    </tr>
    <tr>
      <td style="text-align:right;white-space:nowrap;">
        <?php 
        if (isNewGui()) echo ucfirst(i18n('baselineBottom')) .'&nbsp;';
        else echo (($displayWidthPlan>1230)?i18n('baselineBottom'):i18n('baselineBottomShort')).'&nbsp;:&nbsp';?>
        <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
          style="width:<?php echo ($displayWidthPlan>930)?'150':'80';?>px;"
          name="selectBaselineBottom" id="selectBaselineBottom"
          <?php echo autoOpenFilteringSelect();?> >
          <script type="dojo/method" event="onChange" >
            saveDataToSession("planningBaselineBottom",this.value,false);
            refreshJsonPlanning();
          </script>
          <?php htmlDrawOptionForReference('idBaselineSelect', getSessionValue("planningBaselineBottom"), null,false,($proj)?'idProject':null,($proj)?$proj:null);?>
        </select>
      </td>
    </tr>
  </table>
<?php 
}

// ================================================== CHECKBOXES FOR DISPLAY OPTIONS 
function drawOptionsDisplay() {
  global $saveShowWbs, $saveShowClosed, $saveShowResource,$planningType, $showListFilter,$showClosedPlanningVersion;?>
  <table width="100%">
    <?php if ($planningType!='contract' and $planningType!='version') {?>
    <tr class="checkboxLabel">
      <td><?php echo ucfirst(i18n("labelShowWbs".((isNewGui())?'':'Short')));?></td>
      <td width="35px">
        <div title="<?php echo ucfirst(i18n('showWbs'));?>" dojoType="dijit.form.CheckBox" 
          class="whiteCheck" type="checkbox" id="showWBS" name="showWBS"
          <?php if ($saveShowWbs=='1') { echo ' checked="checked" '; }?> >
          <script type="dojo/method" event="onChange" >
            saveUserParameter('planningShowWbs',((this.checked)?'1':'0'));
            refreshJsonPlanning();
          </script>
        </div>&nbsp;
      </td>
    </tr>
    <?php }?>
    <tr class="checkboxLabel" <?php echo ($planningType=='version')?'style="height:25px"':''?>>
      <td><?php echo ucfirst(i18n("labelShowIdle".((isNewGui() or $planningType=='version')?'':'Short')));?></td>
      <td style="width: 30px;">
        <?php if ($planningType=='version') {?>
        <div title="<?php echo i18n('labelShowIdle')?>" dojoType="dijit.form.CheckBox" 
         class="whiteCheck" type="checkbox" id="showClosedPlanningVersion" name="showClosedPlanningVersion"
         <?php if ($showClosedPlanningVersion=='1') { echo ' checked="checked" '; }?> >
          <script type="dojo/method" event="onChange" >
            saveUserParameter('planningVersionShowClosed',((this.checked)?'1':'0'));
            refreshJsonPlanning();
          </script>
        </div>&nbsp;
        <?php } else {?>
        <div title="<?php echo ucfirst(i18n('showIdleElements'));?>" dojoType="dijit.form.CheckBox" 
          class="whiteCheck" type="checkbox" id="listShowIdle" name="listShowIdle"
          <?php if ($saveShowClosed=='1') { echo ' checked="checked" '; }?> >
          <script type="dojo/method" event="onChange" >
            saveUserParameter('planningShowClosed',((this.checked)?'1':'0'));
            refreshJsonPlanning();
          </script>
        </div>&nbsp;
        <?php }?>
      </td>
    </tr>
    <?php 
    if (strtoupper(Parameter::getUserParameter('displayResourcePlan'))!='NO' and ($planningType=='planning' or  $planningType=='global' or $planningType=='contract' or $planningType=='version') ) {?>
      <tr class="checkboxLabel" <?php echo ($planningType=='version')?'style="height:25px"':''?>>
        <td>
          <?php if ($planningType=='version') {?><div id="displayRessource" style="visibility:<?php echo ($showListFilter=='true')?'visible':'hidden';?>;"><?php }?>
          <?php echo ucfirst(i18n("labelShowResource".((isNewGui() or $planningType=='version')?'':'Short')));?>
          <?php if ($planningType=='version') {?></div><?php }?>
        </td>
        <td style="width: 30px;">
          <?php if ($planningType=='version') {?><div id="displayRessourceCheck" style="visibility:<?php echo ($showListFilter=='true')?'visible':'hidden';?>!important;"><?php }?>
          <div title="<?php echo ucfirst(i18n('showResources'));?>" dojoType="dijit.form.CheckBox" 
            class="whiteCheck" type="checkbox" 
            <?php if ($planningType=='version') {?>id="showRessourceComponentVersion" name="showRessourceComponentVersion"<?php } else { ?>id="listShowResource" name="listShowResource"<?php }?> 
            <?php if ($saveShowResource=='1') { echo ' checked="checked" '; }?> >
            <script type="dojo/method" event="onChange" >
              saveUserParameter('planningShowResource',((this.checked)?'1':'0'));
              refreshJsonPlanning();
            </script>
          </div>&nbsp;
          <?php if ($planningType=='version') {?></div><?php }?>
        </td>
      </tr>
    <?php 
    }?>
  </table>
<?php 
}

// ================================================== CHECKBOX FOR CRITICAL PATH
function drawOptionCriticalPath() {
?>
  <div style="white-space:nowrap; <?php echo (isNewGui())?'margin-right:6px;margin-top:5px;':'position:absolute; bottom:5px;left:10px;'; ?>" class="checkboxLabel">
    <?php if (isNewGui()) {?><?php echo ucfirst(i18n('criticalPath'));?>&nbsp;<?php }?>
    <span title="<?php echo ucfirst(i18n('criticalPath'));?>" dojoType="dijit.form.CheckBox"
      type="checkbox" id="criticalPathPlanning" name="criticalPathPlanning" class="whiteCheck"
      <?php if ( Parameter::getUserParameter('criticalPathPlanning')=='1') {echo 'checked="checked"'; } ?>  >  
      <script type="dojo/connect" event="onChange" args="evt">
        saveUserParameter('criticalPathPlanning',((this.checked)?'1':'0'));
        refreshJsonPlanning();
      </script>                    
    </span>
    <?php if (!isNewGui()) {?>&nbsp;<?php echo i18n('criticalPath');?><?php }?>
  </div>
<?php 
}

// ================================================== FIELD MILESTONES
function drawMilestones() {
  global $saveShowMilestone;
  ?>
  <?php echo i18n("showMilestoneShort");?>
  <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
    style="width: 150px;"
    <?php echo autoOpenFilteringSelect();?>
    name="listShowMilestone" id="listShowMilestone">
    <script type="dojo/method" event="onChange" >
      saveUserParameter('planningShowMilestone',this.value);
      refreshJsonPlanning();
    </script>
    <option value=" " <?php echo (! $saveShowMilestone)?'SELECTED':'';?>><?php echo i18n("paramNone");?></option>                            
      <?php htmlDrawOptionForReference('idMilestoneType', (($saveShowMilestone and $saveShowMilestone!='all')?$saveShowMilestone:null) ,null, true);?>
    <option value="all" <?php echo ($saveShowMilestone=='all')?'SELECTED':'';?>><?php echo i18n("all");?></option>                            
  </select>
<?php                         
}

// ================================================== CHECKBOX SHOW LEFT WORK
function drawOptionLeftWork() {
  global $saveShowWork;?>
  <table width="100%">
    <tr class="checkboxLabel">
      <td >
        <?php echo ucfirst(i18n("labelShowLeftWork".((isNewGui()?'':'Short'))));?>
      </td>
      <td style="width:36px">
        <div title="<?php echo i18n('showLeftWork')?>" dojoType="dijit.form.CheckBox" 
          type="checkbox" id="listShowLeftWork" name="listShowLeftWork" class="whiteCheck"
          <?php if ($saveShowWork=='1') { echo ' checked="checked" '; }?> >
          <script type="dojo/method" event="onChange" >
        saveUserParameter('planningShowWork',((this.checked)?'1':'0'));
        refreshJsonPlanning();
      </script>
        </div>&nbsp;
      </td>
    </tr>
  </table>
<?php 
}

// ================================================== CHECKBOX FOR RESOURCE 
function drawOptionResource() {
  global $saveShowNullAssignment, $saveShowProject;?>
  <table width="100%">
    <tr class="checkboxLabel">
      <td style="min-width:80px;<?php if (!isNewGui()) echo 'text-align:right;padding-right:10px;';?>"><?php echo ucfirst(i18n("labelShowAssignmentWithoutWork".((isNewGui())?'':'Short')));?></td>
      <td style="width:36px">
        <div title="<?php echo i18n('titleShowAssignmentWithoutWork')?>" dojoType="dijit.form.CheckBox" 
          type="checkbox" id="listShowNullAssignment" name="listShowNullAssignment" class="whiteCheck" 
          <?php if ($saveShowNullAssignment=='1') { echo ' checked="checked" '; }?> >
          <script type="dojo/method" event="onChange" >
          saveUserParameter('listShowNullAssignment',((this.checked)?'1':'0'));
          refreshJsonPlanning();
        </script>
        </div>&nbsp;
      </td>
    </tr>
    <tr class="checkboxLabel">
      <td style="min-width:80px;<?php if (!isNewGui()) echo 'text-align:right;padding-right:10px;';?>"><?php echo ucfirst(i18n("labelShowProjectLevel".((isNewGui())?'':'Short')));?></td>
      <td tyle="width:36px">
        <div title="<?php echo i18n('showProjectLevel')?>" dojoType="dijit.form.CheckBox" 
          type="checkbox" id="listShowProject" name="listShowProject" class="whiteCheck"
          <?php if ($saveShowProject=='1') { echo ' checked="checked" '; }?> >
          <script type="dojo/method" event="onChange" >
            saveUserParameter('planningShowProject',((this.checked)?'1':'0'));
            refreshJsonPlanning();
          </script>
        </div>&nbsp;
      </td>
  </table>
<?php 
}

// ==================
function drawResourceTeamOrga() {
  global $displayWidthPlan, $displayListDiv;
  $sizeSelect=($displayListDiv>1400)?150:100;
  $showOrga=($displayListDiv>1180)?true:false;
  $showTeam=($displayListDiv>980)?true:false;
  $showRes=($displayListDiv>780)?true:false;
  ?>
  <table>
    <tr>
      <td style="text-align:right;padding-left:15px;<?php if (! $showRes) echo 'display:none;'?>"><?php echo i18n('colIdResource');?>&nbsp;&nbsp;</td>
      <td style="<?php if (! $showRes) echo 'display:none;'?>">
        <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
          style="width: <?php echo $sizeSelect;?>px;"
          <?php echo autoOpenFilteringSelect();?>
          name="selectResourceName" id="selectResourceName" value="<?php if(sessionValueExists('selectResourceName')){ echo getSessionValue('selectResourceName'); }?>" >
          <script type="dojo/method" event="onChange" >
            saveDataToSession('selectResourceName', dijit.byId('selectResourceName').get("value"), false);
            refreshJsonPlanning();
          </script>
          <option value=""></option>
          <?php 
          $specific='resourcePlanning';
          $includePool=true;
          $specificDoNotInitialize=true;                       
          include '../tool/drawResourceListForSpecificAccess.php'; ?>
        </select>
      </td>
    <?php if (! isNewGui()) {?>
    </tr>
    <tr>
    <?php }?>
      <td style="text-align:right;padding-left:15px;<?php if (! $showTeam) echo 'display:none;'?>"><?php echo i18n('colIdTeam');?>&nbsp;&nbsp;</td>
      <td style="<?php if (! $showTeam) echo 'display:none;'?>">
        <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
          style="width:<?php echo $sizeSelect;?>px;"
          name="teamName" id="teamName" value="<?php if(sessionValueExists('teamName')){ echo getSessionValue('teamName'); }?>"
          <?php echo autoOpenFilteringSelect();?>
          >
          <script type="dojo/method" event="onChange" > 
            saveDataToSession('teamName', dijit.byId('teamName').get("value"), false);                          
            refreshJsonPlanning();
          </script>
          <?php 
          htmlDrawOptionForReference('idTeam', null)?>  
        </select>
      </td>
    <?php if (! isNewGui()) {?>  
    </tr>
    <tr>
    <?php }?>
      <td style="text-align:right;padding-left:15px;<?php if (! $showOrga) echo 'display:none;'?>"><?php echo i18n('colIdOrganization');?>&nbsp;&nbsp;</td>
        <td style="<?php if (! $showOrga) echo 'display:none;'?>">
        <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
          style="width:<?php echo $sizeSelect;?>px;"
          name="organizationName" id="organizationName" value="<?php if(sessionValueExists('organizationName')){ echo getSessionValue('organizationName'); }?>"
          <?php echo autoOpenFilteringSelect();?>
          >
          <script type="dojo/method" event="onChange" > 
            saveDataToSession('organizationName', dijit.byId('organizationName').get("value"), false);                          
            refreshJsonPlanning();
          </script>
          <?php 
          htmlDrawOptionForReference('idOrganization', null)?>  
        </select>
      </td>
    </tr>
  </table>
<?php   
}

function drawGlobalItemsSelector() {
?>
  <div dojoType="dijit.form.DropDownButton"
    id="listItemsSelector" jsId="listItemsSelector" name="listItemsSelector"
    showlabel="false" class="comboButton" iconClass="iconGlobalView iconSize22 imageColorNewGui"
    title="<?php echo i18n('itemSelector');?>">
    <span>title</span>
    <div dojoType="dijit.TooltipDialog" class="white" id="listItemsSelectorDialog"
      style="position: absolute; top: 50px; right: 40%">
      <script type="dojo/connect" event="onShow" args="evt">
        oldSelectedItems=dijit.byId('globalPlanningSelectItems').get('value');
      </script>
      <div style="text-align: center;position: relative;">
        <button title="" dojoType="dijit.form.Button"
          class="mediumTextButton" id="" name="" showLabel="true"><?php echo i18n('buttonOK');?>
          <script type="dojo/connect" event="onClick" args="evt">
            dijit.byId('listItemsSelector').closeDropDown();
          </script>
        </button>
        <div style="position: absolute;top: 34px; right:42px;"></div>
      </div>   
      <div style="height:5px;border-bottom:1px solid #AAAAAA"></div>    
      <div>                       
        <?php GlobalPlanningElement::drawGlobalizableList();?>
      </div>
      <div style="height:5px;border-top:1px solid #AAAAAA"></div>    
      <div style="text-align: center;position: relative;">
        <button title="" dojoType="dijit.form.Button" 
          class="mediumTextButton" id="" name="" showLabel="true"><?php echo i18n('buttonOK');?>
          <script type="dojo/connect" event="onClick" args="evt">
            dijit.byId('listItemsSelector').closeDropDown();
          </script>
        </button>
        <div style="position: absolute;bottom: 33px; right:42px;" ></div>
      </div>   
	  </div>
	</div>       
<?php             
}
function drawVersionOptionsComponentVersionActivity() {
  global $displayComponentVersionActivity;
  ?>
  <td style="padding-right:5px;padding-left:20px;text-align: right;">
    <?php echo ucfirst(i18n('displayComponentVersionActivity'));?>
  </td>
  <td>
    <div title="<?php echo ucfirst(i18n('displayComponentVersionActivity'));?>" dojoType="dijit.form.CheckBox" 
     class="whiteCheck" type="checkbox" id="listDisplayComponentVersionActivity" name="listDisplayComponentVersionActivity"
     <?php if ($displayComponentVersionActivity=='1') { echo ' checked="checked" '; }?> >
      <script type="dojo/method" event="onChange" >
        saveUserParameter('planningVersionDisplayComponentVersionActivity',((this.checked)?'1':'0'));
        showListFilter('planningVersionDisplayComponentVersionActivity',((this.checked)?'1':'0'));
        refreshJsonPlanning();
      </script>
    </div>
  </td>
<?php 
}
function drawVersionOptionsVersionsWithoutActivity() {
  global $showListFilter;
  ?>
  <td style="padding-right:5px;padding-left:20px;text-align: right;" >
	  <div id="versionsWithoutActivity" style="visibility:<?php  echo ($showListFilter=='true')?'visible':'hidden';?>;">
      <?php echo ucfirst(i18n('versionsWithoutActivity'));?>
    </div>
  </td>
  <td>
	  <div id="hideVersionsWithoutActivityCheck" style="visibility:<?php  echo ($showListFilter=='true')?'visible':'hidden';?>!important;">
      <div title="<?php echo ucfirst(i18n('versionsWithoutActivityCheck'));?>" dojoType="dijit.form.CheckBox" 
       class="whiteCheck" type="checkbox" id="versionsWithoutActivityCheck" name="versionsWithoutActivityCheck"
       <?php if ($hideversionsWithoutActivity=Parameter::getUserParameter('versionsWithoutActivity')=='1') { echo ' checked="checked" '; }?> >
        <script type="dojo/method" event="onChange" >
          saveUserParameter('versionsWithoutActivity',((this.checked)?'1':'0'));
          refreshJsonPlanning();
        </script>
      </div>
    </div>
  </td>
<?php
} 
function drawVersionOptionsProductVersionActivity() {
  global $showListFilter, $displayProductVersionActivity;
  ?>
  <td style="padding-right:5px;padding-left:20px;text-align: right;">
    <?php echo ucfirst(i18n('displayProductVersionActivity'));?>
  </td>
  <td>
    <div title="<?php echo ucfirst(i18n('displayProductVersionActivity'));?>" dojoType="dijit.form.CheckBox" 
     class="whiteCheck" type="checkbox" id="listDisplayProductVersionActivity" name="listDisplayProductVersionActivity"
     <?php if ($displayProductVersionActivity=='1') { echo ' checked="checked" '; }?> >
      <script type="dojo/method" event="onChange" >
        saveUserParameter('planningVersionDisplayProductVersionActivity',((this.checked)?'1':'0'));
        showListFilter('planningVersionDisplayProductVersionActivity',((this.checked)?'1':'0'));
        refreshJsonPlanning();
      </script>
    </div>
  </td>
<?php
} 

function drawVersionOptionsOnlyActivesVersions() {
  global $showOnlyActivesVersions,$showListFilter;?>
  <td  style="padding-right:5px;padding-left:20px;text-align: right;" > 
    <?php echo ucfirst(i18n('showOnlyActivesVersions'));?>
  </td>
  <td>  
    <div title="<?php echo ucfirst(i18n('showOnlyActivesVersions'));?>" dojoType="dijit.form.CheckBox" 
     class="whiteCheck" type="checkbox" id="showOnlyActivesVersions" name="showOnlyActivesVersions"
     <?php if ($showOnlyActivesVersions=='1') { echo ' checked="checked" '; }?> >
      <script type="dojo/method" event="onChange" >
        saveUserParameter('showOnlyActivesVersions',((this.checked)?'1':'0'));
        refreshJsonPlanning();
      </script>
    </div>
  </td>
<?php 
}
function drawVersionOptionsOneTimeActivities() {
  global $showOneTimeActivities,$showListFilter;?>
  <td  style="padding-right:5px;padding-left:20px;text-align: right;" >
    <div id="hideOneTimeActivitiesLabel" style="visibility:<?php  echo ($showListFilter=='true')?'visible':'hidden';?>;">
      <span for="showOneTimeActivities"><?php echo ucfirst(i18n("versionPlanningShowOneTimeActivities"));?></span>
    </div>
  </td>
  <td>
    <div id="hideOneTimeActivitiesCheck" style="visibility:<?php  echo ($showListFilter=='true')?'visible':'hidden';?>!important;">  
    <span title="<?php echo ucfirst(i18n('versionPlanningShowOneTimeActivities'));?>" dojoType="dijit.form.CheckBox"
     type="checkbox" id="showOneTimeActivities" name="showOneTimeActivities" class="whiteCheck"
     <?php if ( $showOneTimeActivities) {echo 'checked="checked"'; } ?>  >
      <script type="dojo/method" event="onChange" >
        saveUserParameter('showOneTimeActivities',((this.checked)?'1':'0'));
        refreshJsonPlanning();
      </script>
    </span>
    </div>
  </td>
<?php 
}                            
function drawVersionOptionsProjectLevels() {
  global $showProjectLevel,$showListFilter;?>
  <td style="padding-right:5px;padding-left:20px;text-align: right;">
    <div id="hideProjectLevelLabel" style="visibility:<?php  echo ($showListFilter=='true')?'visible':'hidden';?>;">
    <?php echo ucfirst(i18n('labelShowProjectLevel'));?>
    </div>
  </td>
  <td>
      <div id="hideProjectLevelCheck" style="visibility:<?php  echo ($showListFilter=='true')?'visible':'hidden';?>;">
    <div title="<?php echo ucfirst(i18n('labelShowProjectLevel'));?>" dojoType="dijit.form.CheckBox"
     class="whiteCheck" type="checkbox" id="showProjectLevel" name="showProjectLevel"
     <?php if ($showProjectLevel) { echo ' checked="checked" '; }?> >
      <script type="dojo/method" event="onChange" >
        saveUserParameter('planningVersionShowProjectLevel',((this.checked)?'1':'0'));
        refreshJsonPlanning();
      </script>
    </div>
  </td>
<?php 
}                            
function drawVersionOptionsActivityHierarchy() {
  global $showActivityHierarchy,$showListFilter;?>  
  <td style="padding-right:5px;padding-left:20px;text-align: right;">
    <div id="hideActivityHierarchyLabel" style="visibility:<?php  echo ($showListFilter=='true')?'visible':'hidden';?>;">
    <?php echo ucfirst(i18n('labelShowActivityHierarchy'));?>
    </div>
  </td>
  <td>
    <div id="hideActivityHierarchyCheck" style="visibility:<?php  echo ($showListFilter=='true')?'visible':'hidden';?>;">
    <div title="<?php echo ucfirst(i18n('labelShowActivityHierarchy'));?>" dojoType="dijit.form.CheckBox"
     class="whiteCheck" type="checkbox" id="showActivityHierarchy" name="showActivityHierarchy"
     <?php if ($showActivityHierarchy) { echo ' checked="checked" '; }?> >
      <script type="dojo/method" event="onChange" >
        saveUserParameter('planningVersionDisplayActivityHierarchy',((this.checked)?'1':'0'));
        refreshJsonPlanning();
      </script>
    </div>
    </div>
  </td>
<?php 
} ?>
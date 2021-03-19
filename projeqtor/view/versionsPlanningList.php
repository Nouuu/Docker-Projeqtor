<?php
/*
 * @author: qCazelles
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/view/versionsPlanningList.php');
$planningType='version';
require_once '../tool/planningListFunction.php';

$startDate=date('Y-m-d');
$endDate=null;
$user=getSessionUser();
$saveDates=false;
$saveShowResource=Parameter::getUserParameter('planningShowResource');
$paramStart=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningStartDate'));
if ($paramStart->id) {
	$startDate=$paramStart->parameterValue;
	$saveDates=true;
}
$paramEnd=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningEndDate'));
if ($paramEnd->id) {
	$endDate=$paramEnd->parameterValue;
	$saveDates=true;
}
$displayWidthPlan="9999";
if (RequestHandler::isCodeSet('destinationWidth')) {
  $displayWidthPlan=RequestHandler::getNumeric('destinationWidth');
}

$saveShowWbsObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowWbs'));
$saveShowWbs=$saveShowWbsObj->parameterValue;
// $saveShowResourceObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowResource'));
// $saveShowResource=$saveShowResourceObj->parameterValue;
$saveShowWorkObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowWork'));
$saveShowWork=$saveShowWorkObj->parameterValue;
$saveShowClosedObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowClosed'));
$saveShowClosed=$saveShowClosedObj->parameterValue;
$saveShowMilestoneObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowMilestone'));
$saveShowMilestone=$saveShowMilestoneObj->parameterValue;

$displayProductVersionActivity = Parameter::getUserParameter('planningVersionDisplayProductVersionActivity');
$displayComponentVersionActivity = Parameter::getUserParameter('planningVersionDisplayComponentVersionActivity');
$showClosedPlanningVersion = Parameter::getUserParameter('planningVersionShowClosed');
$showOnlyActivesVersions=Parameter::getUserParameter('showOnlyActivesVersions');
$listDisplayProductVersionActivity=Parameter::getUserParameter('listDisplayProductVersionActivity');
$listDisplayComponentVersionActivity=Parameter::getUserParameter('listDisplayComponentVersionActivity');
$showOneTimeActivities=Parameter::getUserParameter('showOneTimeActivities');
$showProjectLevel = Parameter::getUserParameter('planningVersionShowProjectLevel');
$showActivityHierarchy = Parameter::getUserParameter('planningVersionDisplayActivityHierarchy');
if ($saveShowClosed) {
	$_REQUEST['idle']=true;
}

$proj=null;
if (sessionValueExists('project')) {
	$proj=getSessionValue('project');
	if(strpos($proj, ",")){
		$proj="*";
	}
}
if ($proj=='*' or !$proj) {
	$proj=null;
}
$showListFilter='false';
if($displayComponentVersionActivity=='1' or $displayProductVersionActivity=='1'){
  $showListFilter='true';
}
$activeFilter=false;
if (is_array(getSessionUser()->_arrayFilters)) {
  if (array_key_exists('VersionsPlanning', getSessionUser()->_arrayFilters)) {
    if (count(getSessionUser()->_arrayFilters['VersionsPlanning'])>0) {
      foreach (getSessionUser()->_arrayFilters['VersionsPlanning'] as $filter) {
        if (!isset($filter['isDynamic']) or $filter['isDynamic']=="0") {
          $activeFilter=true;
        }
      }
    }
  }
}
?>

<div id="mainPlanningDivContainer" dojoType="dijit.layout.BorderContainer">
	<div dojoType="dijit.layout.ContentPane" region="top" id="listHeaderDiv" height="27px"
	style="z-index: 3; position: relative; overflow: visible !important;">
		<table width="100%" height="27px" class="listTitle" >
		  <tr height="27px">
		  	<td style="vertical-align:top; min-width:100px; width:15%">
		      <table >
    		    <tr height="32px">
        		    <td width="50px"  style="min-width:50px"  align="center">
                    <?php echo formatIcon('VersionsPlanning', 32, null, true);?>
                  </td>
                  <td width="200px" ><span class="title" style="max-width:200px;white-space:normal"><?php echo i18n('menuVersionsPlanning');?></span></td>
      		    </tr>
                <tr height="32px">
        		    <td width="50px"  style="min-width:50px"  align="center">
        		    </td>
      		    </tr>
    		  </table>
		    </td>
		    <td>   
		      <form dojoType="dijit.form.Form" id="listForm" action="" method="" >
		      	<?php 
            $canPlan=false;
		      	$objectClass=(RequestHandler::isCodeSet('objectClass'))?RequestHandler::getClass('objectClass'):'';
            $objectId=(RequestHandler::isCodeSet('objectId'))?RequestHandler::getId('objectId'):'';
            $productVersionsListId=(RequestHandler::isCodeSet('productVersionsListId'))?RequestHandler::getValue('productVersionsListId'):'';
            $objectVersion=(RequestHandler::isCodeSet('objectVersion'))?RequestHandler::getValue('objectVersion'):'';?>
            <input type="hidden" id="objectClass" name="objectClass" value="<?php echo $objectClass;?>" /> 
            <input type="hidden" id="objectId" name="objectId" value="<?php echo $objectId;?>" />
            <input type="hidden" id="productVersionsListId" name="productVersionsListId" value="<?php echo $productVersionsListId;?>" />
            <input type="hidden" id="objectVersion" name="objectVersion" value="<?php echo $objectVersion;?>" />
            <input type="hidden" id="versions" name="versions" value="true" />
		        <?php if (!isNewGui()) { // =========================================================== NOT NEW GUI ?>
		        <table style="width: 100%;">
		          <tr>
		            <td style="width:10px; position:relative;">
		              &nbsp;&nbsp;&nbsp;             
		            </td>
		            <td style="white-space:nowrap;width:<?php echo ($displayWidthPlan>1030)?240:150;?>px">
		              <table align="right" style="margin:4px">
                    <tr>
                      <td align="right">&nbsp;&nbsp;&nbsp;<?php echo ($displayWidthPlan>1030)?i18n("displayStartDate"):i18n("from");?>&nbsp;&nbsp;</td><td>
                        <?php drawFieldStartDate();?>
                      </td>
                    </tr>
                    <tr>
                      <td align="right">&nbsp;&nbsp;&nbsp;<?php echo ($displayWidthPlan>1030)?i18n("displayEndDate"):i18n("to");?>&nbsp;&nbsp;</td>
                      <td>
                      <?php drawFieldEndDate();?>

                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td style="white-space:nowrap;padding-right:10px;position:relative;top:2px">
                        <?php drawOptionSaveDates();?>
                      </td>
                    </tr>
                  </table>
		            </td>
                <td style="width:150px;">
                  <table >
                    <tr>
                      <td><?php drawButtonsPlanning();?></td>
                    </tr>
                    <tr>
                      <td style="position:relative">
                       <?php drawButtonsDefault();?>
                      </td>
                    </tr>
                  </table>
                </td>
                <td ></td>
                <td style="width:30%;">
                 <table style="">                 
                   <tr class="checkboxLabel" style="height:25px"><?php drawVersionOptionsProductVersionActivity();?></tr>
                   <tr class="checkboxLabel" style="height:25px"><?php drawVersionOptionsComponentVersionActivity();?></tr>                                       
                   <tr class="checkboxLabel" style="height:25px"><?php drawVersionOptionsVersionsWithoutActivity();?></tr>
                 </table>
                </td>
                <td style="width:20%;">
                 <table>
                   <tr class="checkboxLabel" style="height:25px"><?php drawVersionOptionsOneTimeActivities();?></tr>
                   <tr class="checkboxLabel" style="height:25px"><?php drawVersionOptionsProjectLevels();?></tr>
                   <tr class="checkboxLabel" style="height:25px"><?php drawVersionOptionsActivityHierarchy();?></tr>
                 </table>                                                                         
                </td>
		            <td style="width:22%;text-align: right; align: right;">
		              <table width="100%"><tr class="checkboxLabel" style="height:25px"><?php drawVersionOptionsOnlyActivesVersions();?><td>&nbsp;</td></tr></table>
                  <?php drawOptionsDisplay();?>
		            </td>
		          </tr>
		        </table>
		        <?php }?>    
		        <?php if (isNewGui()) { // ========================================================= NEW GUI?>
		        <table style="width: 100%;">
		          <tr>
		            <td style="width:90%;">&nbsp;
                </td>
                <td style="width:150px;text-aliogn:right;">
                       <?php drawButtonsDefault();?>
                </td>
                <td style="width:50px;padding-right:10px">
                        <div dojoType="dijit.form.DropDownButton"							    
							             id="extraButtonPlanning" jsId="extraButtonPlanning" name="extraButtonPlanning" 
							             showlabel="false" class="comboButton" iconClass="dijitButtonIcon dijitButtonIconExtraButtons" class="detailButton" 
							             title="<?php echo i18n('extraButtons');?>">
                           <div dojoType="dijit.TooltipDialog" class="white" id="extraButtonImputationDialog"
							              style="position: absolute; top: 50px; right: 40%;max-width:800px">        
                               <table >
                                 <tr style="height:30px">
                                   <td colspan="2" style="position:relative;">
                                     <div style="position:absolute;right:0px;top:0px;text-align:right"><?php drawButtonsPlanning();?></div>
                                   </td>
                                 </tr>
                                 <tr>
                                   <td style="width:50%">
                                     <table align="right" style="margin:7px">
                                       <tr>
                                         <td align="right">&nbsp;&nbsp;&nbsp;<?php echo ($displayWidthPlan>1030)?i18n("displayStartDate"):i18n("from");?>&nbsp;&nbsp;</td>
                                         <td><?php drawFieldStartDate();?></td>
                                       </tr>
                                       <tr>
                                         <td align="right">&nbsp;&nbsp;&nbsp;<?php echo ($displayWidthPlan>1030)?i18n("displayEndDate"):i18n("to");?>&nbsp;&nbsp;</td>
                                         <td><?php drawFieldEndDate();?></td>
                                       </tr>
                                     </table>
                                   </td>
                                   <td style="width:50%">
                                     &nbsp;
                                     <br/><br/>
                                     <?php drawOptionSaveDates();?>
                                   </td>
                                 </tr>
                                 <tr>
                                   <td style="width:60%;">
                                     <table style="">
                                       <tr class="checkboxLabel"><?php drawVersionOptionsOnlyActivesVersions();?></tr>
                                       <tr class="checkboxLabel"><?php drawVersionOptionsProductVersionActivity();?></tr>
                                       <tr class="checkboxLabel"><?php drawVersionOptionsComponentVersionActivity();?></tr>                                       
                                       <tr class="checkboxLabel"><?php drawVersionOptionsVersionsWithoutActivity();?></tr>
                                       <tr class="checkboxLabel"><?php drawVersionOptionsOneTimeActivities();?></tr>
                                       <tr class="checkboxLabel"><?php drawVersionOptionsProjectLevels();?></tr>
                                       <tr class="checkboxLabel"><?php drawVersionOptionsActivityHierarchy();?></tr>
                                     </table>                                                                         
                                   </td>
		                               <td style="width:40%;text-align: right; align: right;vertical-align:top;">
                                     <?php drawOptionsDisplay();?> 
                                     <br/>
		                               </td>
		                             </tr>
		                           </table>
                           </div>
                        </div>
                </td>
		          </tr>
		        </table>
		        <?php }?>    
		      </form>
		    </td>
		  </tr>
		</table>
		<div id="listBarShow" class="dijitAccordionTitle"  onMouseover="showList('mouse')" onClick="showList('click');">
		  <div id="listBarIcon" align="center"></div>
		</div>
	
		<div dojoType="dijit.layout.ContentPane" id="planningJsonData" jsId="planningJsonData" 
     style="display: none">
		  <?php
            include '../tool/jsonVersionsPlanning.php';
          ?>
		</div>
	</div>
	<div dojoType="dijit.layout.ContentPane" region="center" id="gridContainerDiv">
   <div id="submainPlanningDivContainer" dojoType="dijit.layout.BorderContainer"
    style="border-top:1px solid #ffffff;">
        <?php $leftPartSize=Parameter::getUserParameter('planningLeftSize');
          if (! $leftPartSize) {$leftPartSize='325px';} ?>
	   <div dojoType="dijit.layout.ContentPane" region="left" splitter="true" 
      style="width:<?php echo $leftPartSize;?>; height:100%; overflow-x:scroll; overflow-y:hidden;" class="ganttDiv" 
      id="leftGanttChartDIV" name="leftGanttChartDIV"
      onScroll="dojo.byId('ganttScale').style.left=(this.scrollLeft)+'px'; this.scrollTop=0;" 
      onWheel="leftMouseWheel(event);">
      <script type="dojo/method" event="onUnload" >
         var width=this.domNode.style.width;
         setTimeout("saveUserParameter('planningLeftSize','"+width+"');",1);
         return true;
      </script>
     </div>
     <div dojoType="dijit.layout.ContentPane" region="center" 
      style="height:100%; overflow:hidden;" class="ganttDiv" 
      id="GanttChartDIV" name="GanttChartDIV" >
       <div id="mainRightPlanningDivContainer" dojoType="dijit.layout.BorderContainer">
         <div dojoType="dijit.layout.ContentPane" region="top" 
          style="width:100%; height:45px; overflow:hidden;" class="ganttDiv"
          id="topGanttChartDIV" name="topGanttChartDIV">
         </div>
         <div dojoType="dijit.layout.ContentPane" region="center" 
          style="width:100%; overflow-x:scroll; overflow-y:scroll; position: relative; top:-10px;" class="ganttDiv"
          id="rightGanttChartDIV" name="rightGanttChartDIV"
          onScroll="dojo.byId('rightside').style.left='-'+(this.scrollLeft+1)+'px';
                    dojo.byId('leftside').style.top='-'+(this.scrollTop)+'px';"
         >
         </div>
       </div>
     </div>
   </div>
	</div>
</div>
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

/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/planningList.php');
$planningType='planning';
require_once '../tool/planningListFunction.php';

$startDate=date('Y-m-d');
$endDate=null;
$user=getSessionUser();
$saveDates=false;
$projectDate=Parameter::getUserParameter('projectDate');
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
if($projectDate){
  $saveDates=false;
  $startDate=null;
  $endDate=null;
}else{
  $startDate=date('Y-m-d');
}
//$saveShowWbsObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowWbs'));
//$saveShowWbs=$saveShowWbsObj->parameterValue;
$saveShowWbs=Parameter::getUserParameter('planningShowWbs');
//$saveShowResourceObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowResource'));
//$saveShowResource=$saveShowResourceObj->parameterValue;
$saveShowResource=Parameter::getUserParameter('planningShowResource');
//$saveShowWorkObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowWork'));
//$saveShowWork=$saveShowWorkObj->parameterValue;
$saveShowWork=Parameter::getUserParameter('planningShowWork');
//$saveShowClosedObj=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningShowClosed'));
//$saveShowClosed=$saveShowClosedObj->parameterValue;
$saveShowClosed=Parameter::getUserParameter('planningShowClosed');
if ($saveShowClosed) {
	$_REQUEST['idle']=true;
}
if (is_array( getSessionUser()->_arrayFilters)) {
  if (array_key_exists('Planning', getSessionUser()->_arrayFilters)) {
    $arrayFilter=getSessionUser()->_arrayFilters['Planning'];
    foreach ($arrayFilter as $filter) {
      if ($filter['sql']['attribute']=='idle' and $filter['sql']['operator']=='>=' and $filter['sql']['value']=='0') {
        $saveShowClosed=1;
      }
    }
  }
}
$automaticRunPlanning=Parameter::getUserParameter('automaticRunPlanning');

$canPlan=false;
$right=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile'=>$user->idProfile, 'scope'=>'planning'));
if ($right) {
  $list=new ListYesNo($right->rightAccess);
  if ($list->code=='YES') {
    $canPlan=true;
  }
}
$plannableProjectsList=getSessionUser()->getListOfPlannableProjects();
// $plannedElement = new PlanningElement();
// $plannedProjectList = $plannedElement->getSqlElementsFromCriteria(array("refType"=>"Project"));

// foreach ($plannedProjectList as $plannedProject){
//   if($plannedProject->validatedStartDate < $startDate and $plannedProject->validatedStartDate != ''){
//     $startDate = $plannedProject->validatedStartDate;
//   }
//   if($plannedProject->validatedEndDate > $endDate and $plannedProject->validatedEndDate != ''){
//   	$endDate = $plannedProject->validatedEndDate;
//   }
// }

if (! $canPlan) {
  $canPlan=(count($plannableProjectsList)>0)?true:false;
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

$displayWidthPlan="9999";
if (RequestHandler::isCodeSet('destinationWidth')) {
  $displayWidthPlan=RequestHandler::getNumeric('destinationWidth');
}
//florent
$showValidationButton=false;
$lstUserP=$user->getAllProfiles();
foreach ($lstUserP as $prof){
  $priority=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$prof,'scope'=>'validatePlanning'));
  if($priority->rightAccess!=1){
     continue;
  }
  $showValidationButton=true;
  break;
}

//$objectClass='Task';
//$obj=new $objectClass;
?>
  
<div id="mainPlanningDivContainer" dojoType="dijit.layout.BorderContainer">
	<div dojoType="dijit.layout.ContentPane" region="top" id="listHeaderDiv" 
	 style="z-index: 3; position: relative; overflow: visible !important;">
		<table width="100%" class="listTitle" style="height:36px">
		  <tr height="27px" >
		    <td style="vertical-align:top; min-width:100px; width:15%">
		      <table >
    		    <tr height="32px">
      		    <td width="50px" style="min-width:50px;<?php if (isNewGui()) echo 'position:relative;top:2px';?>" align="center">
                <?php echo formatIcon('Planning', 32, null, true);?>
              </td>
              <td style="min-width:100px" ><span class="title" style="max-width:250px;white-space:normal"><?php echo i18n('menuPlanning');?></span></td>
      		  </tr>
      		  <?php if (!isNewGui()) {?>
      		  <tr><td>
  		        <?php drawOptionCriticalPath();?>
            </td></tr>
            <?php }?>       		  
    		  </table>
		    </td>
		    <td>   
		      <form dojoType="dijit.form.Form" id="listForm" action="" method="" style="">
		      	<?php 
		        $objectClass=(RequestHandler::isCodeSet('objectClass'))?RequestHandler::getClass('objectClass'):'';
		        $objectId=(RequestHandler::isCodeSet('objectId'))?RequestHandler::getId('objectId'):'';?>
		        <input type="hidden" id="objectClass" name="objectClass" value="<?php echo $objectClass;?>" /> 
		        <input type="hidden" id="objectId" name="objectId" value="<?php echo $objectId;?>" />
		        <?php if (!isNewGui()) { // =========================================================== NOT NEW GUI?>
		        <table style="width: 100%;">
		          <tr>
		            <td style="width:70px; position:relative;">

		              &nbsp;&nbsp;&nbsp;
                  <?php if ($canPlan) { ?>
                  <?php drawButtonPlan(); ?>
                  <?php drawOptionAutomatic();?>
                  <?php }?>             
		            </td>
		            <td style="white-space:nowrap;width:<?php echo ($displayWidthPlan>1030)?240:150;?>px">
		              <table align="right" style="margin:7px">
                    <tr>
                      <td align="right" style="white-space:nowrap">&nbsp;&nbsp;&nbsp;<?php echo ($displayWidthPlan>1030)?i18n("displayStartDate"):i18n("from");?>&nbsp;&nbsp;</td><td>
                        <?php drawFieldStartDate();?>
                      </td>
                    </tr>
                    <tr>
                      <td align="right" style="white-space:nowrap">&nbsp;&nbsp;&nbsp;<?php echo ($displayWidthPlan>1030)?i18n("displayEndDate"):i18n("to");?>&nbsp;&nbsp;</td>
                      <td>
                      <?php drawFieldEndDate();?>

                      </td>
                    </tr>
                  </table>
		            </td>
                <td style="width:250px;">
                  <table >
                    <tr>
                    <td style="white-space:nowrap;padding-right:10px;position:relative;top:4px">
                    <?php drawOptionAllProject();?>
                            
                     </td>
                      <td colsan="3">
                        <?php drawButtonsPlanning();?>
                      </td>
                    </tr>
                    <tr>
                    <td style="white-space:nowrap;padding-right:10px;position:relative;top:-4px">
                       <?php drawOptionSaveDates();?>
                          </td>
                      <td colspan="3">
                       <?php drawButtonsDefault();?>
                      </td>
                    </tr>
                  </table>
                </td>
		            <td style="">
                  <?php drawOptionBaseline();?>
                </td>
                
		            <td style="text-align: right; align: right;">
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
                <?php if ($canPlan) { ?>
		            <td style="width:70px; position:relative;">
		              &nbsp;&nbsp;&nbsp;
		                <div style="position:absolute;top:-4px;right:10px">
                    <?php drawButtonPlan(); ?>
                    </div>    
		            </td>
		            <td style="width:70px; position:relative;padding-right:20px;">
                    <?php drawOptionAutomatic();?>
                </td>
                <?php } ?>
                <td style="width:150px;text-aliogn:right;">
                       <?php drawButtonsDefault();?>
                </td>
                <td style="width:50px;padding-right:10px">
                        <div dojoType="dijit.form.DropDownButton"							    
							             id="extraButtonPlanning" jsId="extraButtonPlanning" name="extraButtonPlanning" 
							             showlabel="false" class="comboButton" iconClass="dijitButtonIcon dijitButtonIconExtraButtons" class="detailButton" 
							             title="<?php echo i18n('extraButtons');?>">
                           <div dojoType="dijit.TooltipDialog" class="white" id="extraButtonImputationDialog"
							              style="position: absolute; top: 50px; right: 40%">        
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
                                     <?php drawOptionAllProject();?>
                                     <br/><br/>
                                     <?php drawOptionSaveDates();?>
                                   </td>
                                 </tr>
                                 <tr>
                                   <td>
                                     <?php drawOptionBaseline();?>
                                   </td>
		                               <td style="text-align: right; align: right;">
                                     <?php drawOptionsDisplay();?>
                                      <?php drawOptionCriticalPath();?>
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
	
		<div dojoType="dijit.layout.ContentPane" id="planningJsonData" jsId="planningJsonData" 
     style="display: none">
		  <?php
		       if ($saveShowResource) $_REQUEST['showResource']='on';
            include '../tool/jsonPlanning.php';
          ?>
		</div>
	</div>
	<div dojoType="dijit.layout.ContentPane" region="center" id="gridContainerDiv"" >
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
       <div id="mainRightPlanningDivContainer" dojoType="dijit.layout.BorderContainer" style="z-index:-4;">
         <div dojoType="dijit.layout.ContentPane" region="top" 
          style="width:100%; height:45px; overflow:hidden;" class="ganttDiv"
          id="topGanttChartDIV" name="topGanttChartDIV">
         </div>
         <div dojoType="dijit.layout.ContentPane" region="center" 
          style="z-index:-4; width:100%; overflow-x:scroll; overflow-y:scroll; position: relative; top:-10px;" class="ganttDiv"
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

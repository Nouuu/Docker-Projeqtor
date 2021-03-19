<?php
/*
 * @author: qCazelles
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/contractGanttList.php');
$planningType='contract';
require_once '../tool/planningListFunction.php';

$startDate=date('Y-m-d');
$endDate=null;
$user=getSessionUser();
$saveDates=false;
$typeGanttContract='GanttSupplierContract';
$paramStart=SqlElement::getSingleSqlElementFromCriteria('Parameter', array(
    'idUser'=>$user->id, 
    'idProject'=>null, 
    'parameterCode'=>'planningStartDate'));
if ($paramStart->id) {
  $startDate=$paramStart->parameterValue;
  $saveDates=true;
}
$paramEnd=SqlElement::getSingleSqlElementFromCriteria('Parameter', array(
    'idUser'=>$user->id, 
    'idProject'=>null, 
    'parameterCode'=>'planningEndDate'));
if ($paramEnd->id) {
  $endDate=$paramEnd->parameterValue;
  $saveDates=true;
}
$saveShowResource=Parameter::getUserParameter('contractGanttShowResource');
$showClosedContract=Parameter::getUserParameter('contractShowClosed');

if ($showClosedContract) {
  $_REQUEST['idle']=true;
}

$proj=null;
if (sessionValueExists('project')) {
  $proj=getSessionValue('project');
  if (strpos($proj, ",")) {
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
$objectClass=(RequestHandler::isCodeSet('objectClass'))?RequestHandler::getClass('objectClass'):'';
if ($objectClass==='ClientContract') {
  $typeGanttContract='GanttClientContract';
}
$contractGantt=true;
?>
<input type="hidden" name="objectGantt" id="objectGantt"
	value="<?php echo $objectClass;?>" />
<div id="mainPlanningDivContainer"
	dojoType="dijit.layout.BorderContainer">
	<div dojoType="dijit.layout.ContentPane" region="top"
		id="listHeaderDiv" height="27px"
		style="z-index: 3; position: relative; overflow: visible !important;">		
		<table width="100%" style="height:36px" class="listTitle">
			<tr height="27px">
				<td style="vertical-align: top; min-width: 100px; width: 25%">
					<table>
						<tr height="32px">
							<td width="50px" style="min-width: 50px;<?php if (isNewGui()) echo 'position:relative;top:2px';?>" align="center">
                  <?php echo formatIcon($typeGanttContract, 32, null, true);?>
            </td>
							<td width="400px"><span class="title"
								style="max-width: 200px; white-space: normal"><?php echo i18n('menu'.$typeGanttContract);?></span></td>
						</tr>
					</table>
				</td>
				<td>
					<form dojoType="dijit.form.Form" id="listForm" action="" method="">
  					<?php
            $objectClass=(RequestHandler::isCodeSet('objectClass'))?RequestHandler::getClass('objectClass'):'';
            $objectId=(RequestHandler::isCodeSet('objectId'))?RequestHandler::getId('objectId'):'';
            ?>
            <input type="hidden" id="objectClass" name="objectClass" value="<?php echo $objectClass;?>" /> 
            <input type="hidden" id="objectId" name="objectId" value="<?php echo $objectId;?>" />
		        <?php if (!isNewGui()) { // =========================================================== NOT NEW GUI?>
		        <table style="width: 100%;">
		          <tr>
		            <td style="white-space:nowrap;width:<?php echo ($displayWidthPlan>1030)?240:150;?>px">
		              <table align="right" style="margin:7px">
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
                  </table>
		            </td>
		            <td style="width:50px">&nbsp;</td> 
                <td style="width:250px;">
                  <table >
                    <tr>
                      <td colspan="3">
                       <?php drawButtonsDefault();?>
                      </td>
                      <td colsan="3">
                        <?php drawButtonsPlanning();?>
                      </td>
                    </tr>
                  </table>
                </td>
                <td style="">&nbsp;</td>               
		            <td style="text-align: right; width:120px">
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
                             <td></td>
                           </tr>
                           <tr>
                             <td></td>
                             <td style="text-align: right; align: right;">
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
		<div id="listBarShow" class="dijitAccordionTitle"
			onMouseover="showList('mouse')" onClick="showList('click');">
			<div id="listBarIcon" align="center"></div>
		</div>

		<div dojoType="dijit.layout.ContentPane" id="planningJsonData"
			jsId="planningJsonData" style="display: none">
		  <?php
    include '../tool/jsonContractGantt.php';
    ?>
		</div>
	</div>
	<div dojoType="dijit.layout.ContentPane" region="center"
		id="gridContainerDiv">
		<div id="submainPlanningDivContainer"
			dojoType="dijit.layout.BorderContainer"
			style="border-top: 1px solid #ffffff;">
        <?php
        
$leftPartSize=Parameter::getUserParameter('planningLeftSize');
        if (!$leftPartSize) {
          $leftPartSize='325px';
        }
        ?>
	   <div dojoType="dijit.layout.ContentPane" region="left" splitter="true" 
      style="width:<?php echo $leftPartSize;?>; height:100%; overflow-x:scroll; overflow-y:hidden;" class="ganttDiv" 
      id="leftGanttChartDIV" name="leftGanttChartDIV"
      onScroll="dojo.byId('ganttScale').style.left=(this.scrollLeft)+'px'; this.scrollTop=0;" 
      onWheel="leftMouseWheel(event);">
				<script type="dojo/method" event="onUnload">
         var width=this.domNode.style.width;
         setTimeout("saveUserParameter('planningLeftSize','"+width+"');",1);
         return true;
      </script>
			</div>
			<div dojoType="dijit.layout.ContentPane" region="center"
				style="height: 100%; overflow: hidden;" class="ganttDiv"
				id="GanttChartDIV" name="GanttChartDIV">
				<div id="mainRightPlanningDivContainer"
					dojoType="dijit.layout.BorderContainer">
					<div dojoType="dijit.layout.ContentPane" region="top"
						style="width: 100%; height: 45px; overflow: hidden;"
						class="ganttDiv" id="topGanttChartDIV" name="topGanttChartDIV"></div>
					<div dojoType="dijit.layout.ContentPane" region="center"
						style="width: 100%; overflow-x: scroll; overflow-y: scroll; position: relative; top: -10px;"
						class="ganttDiv" id="rightGanttChartDIV" name="rightGanttChartDIV"
						onScroll="dojo.byId('rightside').style.left='-'+(this.scrollLeft+1)+'px';
                    dojo.byId('leftside').style.top='-'+(this.scrollTop)+'px';">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
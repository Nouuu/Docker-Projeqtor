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
 * Presents an object. 
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/globalPlanningMain.php');
    
  $topDetailDivHeight=Parameter::getUserParameter('contentPaneTopGlobalPlanningDivHeight');
  $screenHeight=getSessionValue('screenHeight');
  if ($screenHeight and $topDetailDivHeight>$screenHeight-300) {
    $topDetailDivHeight=$screenHeight-300;
  }
  //florent
  $paramScreen=RequestHandler::getValue('paramScreen');
  $paramLayoutObjectDetail=RequestHandler::getValue('paramLayoutObjectDetail');
  $paramRightDiv=RequestHandler::getValue('paramRightDiv');
  $currentScreen='GlobalPlanning';
  setSessionValue('currentScreen', $currentScreen);
  $positionListDiv=changeLayoutObjectDetail($paramScreen,$paramLayoutObjectDetail);
  $positonRightDiv=changeLayoutActivityStream($paramRightDiv);
  $codeModeLayout=Parameter::getUserParameter('paramScreen');
 if ($positionListDiv=='top'){
   $listHeight=HeightLayoutListDiv($currentScreen);
 }
 if($positonRightDiv=="bottom"){
    $rightHeightGlobalPlanning=getHeightLaoutActivityStream($currentScreen);
 }else{
  $rightWidthGlobalPlanning=getWidthLayoutActivityStream($currentScreen);
 }
 $tableWidth=WidthDivContentDetail($positionListDiv,$currentScreen);
 $activModeStream=Parameter::getUserParameter('modeActiveStreamGlobal');
 //////
?>
<input type="hidden" name="objectClassManual" id="objectClassManual" value="GlobalPlanning" />
<input type="hidden" name="globalPlanning" id="globalPlanning" value="true" />
<div id="mainDivContainer" class="container" dojoType="dijit.layout.BorderContainer" onclick="hideDependencyRightClick();">
 <div dojoType="dijit.layout.ContentPane" region="center" splitter="true">
    <div class="container" dojoType="dijit.layout.BorderContainer" liveSplitters="false">
    <div id="listBarShow" class="dijitAccordionTitle"  onMouseover="showList('mouse')" onClick="showList('click');">
		  <div id="listBarIcon" align="center"></div>
		</div>
      <div id="listDiv" dojoType="dijit.layout.ContentPane" region="<?php echo $positionListDiv; ?>" splitter="true" 
      style="<?php if($positionListDiv=='top'){echo "height:".$listHeight;}else{ echo "width:".$tableWidth[0];}?>">
        <script type="dojo/connect" event="resize" args="evt">
          if (switchedMode) return;
          var paramDiv=<?php echo json_encode($positionListDiv); ?>;
          var paramMode=<?php echo json_encode($codeModeLayout); ?>;
          if(paramDiv=="top" && paramMode!='switch'){
            saveContentPaneResizing("contentPaneTopDetailDivHeight<?php echo $currentScreen;?>",dojo.byId("listDiv").offsetHeight);
          }else if(paramMode!='switch'){
            saveContentPaneResizing("contentPaneTopDetailDivWidth<?php echo $currentScreen;?>", dojo.byId("listDiv").offsetWidth, true);
          }
        </script>
        <?php include 'globalPlanningList.php'?>
      </div>
      <div id="contentDetailDiv" dojoType="dijit.layout.ContentPane" region="center"   style="width:<?php echo $tableWidth[1]; ?>;">
          <script type="dojo/connect" event="resize" args="evt">
              var paramDiv=<?php echo json_encode($positionListDiv); ?>;
              var paramRightDiv=<?php echo json_encode($positonRightDiv);?>;
              var paramMode=<?php echo json_encode($codeModeLayout); ?>;
              if (checkValidatedSize(paramDiv,paramRightDiv, paramMode)){
                return;
              }
              if(paramDiv=="top" && paramMode!='switch'){
                saveContentPaneResizing("contentPaneDetailDivHeight<?php echo $currentScreen;?>", dojo.byId("contentDetailDiv").offsetHeight, true);
              }else if(paramMode!='switch'){
                saveContentPaneResizing("contentPaneDetailDivWidth<?php echo $currentScreen;?>", dojo.byId("contentDetailDiv").offsetWidth, true);
              var param=dojo.byId('objectClass').value;
              var paramId=dojo.byId('objectId').value;
              if(paramId !='' && multiSelection==false){
                loadContent("objectDetail.php?objectClass="+param+"&objectId="+paramId, "detailDiv", 'listForm');  
              }else if(multiSelection==true){
               loadContent('objectMultipleUpdate.php?objectClass=' + param,
                  'detailDiv')
              }
              }
          </script>
	  <div class="container" dojoType="dijit.layout.BorderContainer"  liveSplitters="false">
	  <div id="detailBarShow" class="dijitAccordionTitle" onMouseover="hideList('mouse');" onClick="hideList('click');"
	    <?php if (RequestHandler::isCodeSet('switchedMode') and RequestHandler::getValue('switchedMode')=='on') echo ' style="display:block;"'?>>
              <div id="detailBarIcon" align="center"></div> 
           </div> 
          <div id="detailDiv" dojoType="dijit.layout.ContentPane" region="center"> 
            <?php $noselect=true; //include 'objectDetail.php'; ?>
          </div>
          <?php 
            if (Module::isModuleActive('moduleActivityStream')) {
              if(property_exists('Activity', '_Note') or property_exists('Project', '_Note') or property_exists('Milestone', '_Note')){
                $showNotes=true;
                $item=new Activity();
                if ($item->isAttributeSetToField('_Note','hidden')) $showNotes=false;
                else if (in_array('_Note',$item->getExtraHiddenFields(null, null, getSessionUser()->getProfile()))) $showNotes=false;
                $item=new Project();
                if ($item->isAttributeSetToField('_Note','hidden')) $showNotes=false;
                else if (in_array('_Note',$item->getExtraHiddenFields(null, null, getSessionUser()->getProfile()))) $showNotes=false;
                $item=new Milestone();
                if ($item->isAttributeSetToField('_Note','hidden')) $showNotes=false;
                else if (in_array('_Note',$item->getExtraHiddenFields(null, null, getSessionUser()->getProfile()))) $showNotes=false;
              }
            } else {
              $showNotes=false;
            }
          if ($showNotes) {?>
          <div id="detailRightDiv" dojoType="dijit.layout.ContentPane" region="<?php echo $positonRightDiv; ?>" splitter="true"
          style="<?php if($positonRightDiv=="bottom"){echo "height:".$rightHeightGlobalPlanning;}else{ echo "width:".$rightWidthGlobalPlanning;}?>" >
              <script type="dojo/connect" event="resize" args="evt">
                var paramDiv=<?php echo json_encode($positionListDiv); ?>;
                var paramMode=<?php echo json_encode($codeModeLayout); ?>;
                var paramRightDiv=<?php echo json_encode($positonRightDiv); ?>;
                var activModeStream=<?php echo json_encode($activModeStream);?>;
                  hideSplitterStream (paramRightDiv);
                  if (checkValidatedSizeRightDiv(paramDiv,paramRightDiv, paramMode)){
                    return;
                  }
                  if(paramRightDiv=='trailing'){
                    saveContentPaneResizing("contentPaneRightDetailDivWidthGlobalPlanning", dojo.byId("detailRightDiv").offsetWidth, true);
                    var newWidth=dojo.byId("detailRightDiv").offsetWidth;
                    dojo.query(".activityStreamNoteContainer").forEach(function(node, index, nodelist) {
                    node.style.maxWidth=(newWidth-30)+"px";
                    });
                  }else{
                    saveContentPaneResizing("contentPaneRightDetailDivHeightGlobalPlanning", dojo.byId("detailRightDiv").offsetHeight, true);
                    var newHeight=dojo.byId("detailRightDiv").offsetHeight;
                    if (dojo.byId("noteNoteStream")) dojo.byId("noteNoteStream").style.height=(newHeight-40)+'px';
                 }
              </script>
              <script type="dojo/connect" event="onLoad" args="evt">
                scrollInto();
	         </script>
              <?php include 'objectStream.php'?>
          </div>
          <?php }?>
      </div>
      </div>
 </div>
</div>  
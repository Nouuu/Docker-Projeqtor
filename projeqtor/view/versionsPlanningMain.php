<?php

/*
 * @author: qCazelles 
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/view/versionsPlanningMain.php');
//florent
$paramScreen=RequestHandler::getValue('paramScreen');
$paramLayoutObjectDetail=RequestHandler::getValue('paramLayoutObjectDetail');
$paramRightDiv=RequestHandler::getValue('paramRightDiv');
$currentScreen='VersionsPlanning';
setSessionValue('currentScreen', $currentScreen);
$positionListDiv=changeLayoutObjectDetail($paramScreen,$paramLayoutObjectDetail);
$positonRightDiv=changeLayoutActivityStream($paramRightDiv);
$codeModeLayout=Parameter::getUserParameter('paramScreen');
if ($positionListDiv=='top'){
  $listHeight=HeightLayoutListDiv($currentScreen);
}
if($positonRightDiv=="bottom"){
  $rightHeightVersionsPlanning=getHeightLaoutActivityStream($currentScreen);
}else{
  $rightWidthVersionsPlanning=getWidthLayoutActivityStream($currentScreen);
}
$tableWidth=WidthDivContentDetail($positionListDiv,$currentScreen);
$activModeStream=Parameter::getUserParameter('modeActiveStreamGlobal');
//////
?>
<input type="hidden" name="objectClassManual" id="objectClassManual" value="VersionsPlanning" />
<input type="hidden" name="versionsPlanning" id="versionsPlanning" value="true" />
<div id="mainDivContainer" class="container" dojoType="dijit.layout.BorderContainer" onclick="hideDependencyRightClick();">
  <div id="listDiv" dojoType="dijit.layout.ContentPane" region="<?php  echo $positionListDiv;?>" splitter="true" 
   style="<?php if($positionListDiv=='top'){echo "height:".$listHeight;}else{ echo "width:".$tableWidth[0];}?>">
    <script type="dojo/connect" event="resize" args="evt">
         if (switchedMode) return;
         var paramDiv=<?php  echo json_encode($positionListDiv); ?>;
         var paramMode=<?php  echo json_encode($codeModeLayout); ?>;
         if(paramDiv=="top" && paramMode!='switch'){
             saveContentPaneResizing("contentPaneTopDetailDivHeight<?php echo $currentScreen;?>", dojo.byId("listDiv").offsetHeight, true);
          }else{
            saveContentPaneResizing("contentPaneTopDetailDivWidth<?php  echo $currentScreen;?>", dojo.byId("listDiv").offsetWidth, true);
          }
    </script>
   <?php include 'versionsPlanningList.php'?>
  </div>
  <div id="contentDetailDiv" dojoType="dijit.layout.ContentPane" region="center"   style="width:<?php  echo $tableWidth[1]; ?>;">
      <script type="dojo/connect" event="resize" args="evt">
           var paramDiv=<?php  echo json_encode($positionListDiv); ?>;
           var paramRightDiv=<?php echo json_encode($positonRightDiv);?>;
           var paramMode=<?php  echo json_encode($codeModeLayout); ?>;
           if (checkValidatedSize(paramDiv,paramRightDiv, paramMode, paramMode)){
            return;
           }
           if(paramDiv=="top" && paramMode!='switch'){
             saveContentPaneResizing("contentPaneDetailDivHeight<?php  echo $currentScreen;?>", dojo.byId("contentDetailDiv").offsetHeight, true);
           }else if(paramMode!='switch'){
              saveContentPaneResizing("contentPaneDetailDivWidth<?php  echo $currentScreen;?>", dojo.byId("contentDetailDiv").offsetWidth, true);
              var param=dojo.byId('objectClass').value;
              var paramId=dojo.byId('objectId').value;
              if(paramId !='' && multiSelection==false){
                loadContent("objectDetail.php?objectClass"+param+"&objectId="+paramId, "detailDiv", 'listForm');  
              }else if(multiSelection==true){
               loadContent('objectMultipleUpdate.php?objectClass=' + param,
                  'detailDiv');
              }
            }
      </script>
     <div class="container" dojoType="dijit.layout.BorderContainer"  liveSplitters="false">
        <div id="detailBarShow" class="dijitAccordionTitle" onMouseover="hideList('mouse');" onClick="hideList('click');"
          <?php  if (RequestHandler::isCodeSet('switchedMode') and RequestHandler::getValue('switchedMode')=='on') echo ' style="display:block;"'?>>
          <div id="detailBarIcon" align="center"></div>
        </div>
        <div id="detailDiv" dojoType="dijit.layout.ContentPane" region="center" >
          <?php  $noselect=true; //include 'objectDetail.php'; ?>
        </div>
    <?php if (Module::isModuleActive('moduleActivityStream')) {?>
        <div id="detailRightDiv" dojoType="dijit.layout.ContentPane" region="<?php echo $positonRightDiv; ?>" splitter="true" 
             style="<?php  if($positonRightDiv=="bottom"){echo "height:".$rightHeightVersionsPlanning;}else{ echo "width:".$rightWidthVersionsPlanning;}?>">
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
                   saveContentPaneResizing("contentPaneRightDetailDivWidth<?php  echo $currentScreen;?>", dojo.byId("detailRightDiv").offsetWidth, true);
                   var newWidth=dojo.byId("detailRightDiv").offsetWidth;
                   dojo.query(".activityStreamNoteContainer").forEach(function(node, index, nodelist) {
                      node.style.maxWidth=(newWidth-30)+"px";
                   });
                }else{
                  saveContentPaneResizing("contentPaneRightDetailDivHeight<?php  echo $currentScreen;?>", dojo.byId("detailRightDiv").offsetHeight, true);
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
<?php 
use PhpOffice\PhpPresentation\Shape\RichText\Paragraph;
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
  scriptLog('   ->/view/objectMain.php');

  //florent
  $paramScreen=RequestHandler::getValue('paramScreen');
  $paramLayoutObjectDetail=RequestHandler::getValue('paramLayoutObjectDetail');
  $paramRightDiv=RequestHandler::getValue('paramRightDiv');
  if ($paramScreen) {
    if ($paramScreen=='top') $paramRightDiv='trailing';
    else if (($paramScreen=='left')) $paramRightDiv='bottom';
  }
  
  setSessionValue('currentScreen', 'Object');
  $positionListDiv=changeLayoutObjectDetail($paramScreen,$paramLayoutObjectDetail);
  $positonRightDiv=changeLayoutActivityStream($paramRightDiv);
  $codeModeLayout=Parameter::getUserParameter('paramScreen');
  
  ///////
  $objectClass="";
  if (isset($_REQUEST['objectClass'])) {
    $objectClass=$_REQUEST['objectClass'];
    Security::checkValidClass($objectClass);
  	if ($_REQUEST['objectClass']=='CalendarDefinition') {
  		$listHeight='25%';
  	}else if ($positionListDiv=='top'){
  	  $listHeight=HeightLayoutListDiv($objectClass);
  	}
  	if($positonRightDiv=="bottom"){
      $rightHeight=getHeightLaoutActivityStream($objectClass);
    }else{
      $rightWidth=getWidthLayoutActivityStream($objectClass);
  	}
  	if ($objectClass=='GlobalView') {
  	  setSessionValue('currentScreen', 'GlobalView');
  	}
  }
  $tableWidth=WidthDivContentDetail($positionListDiv,$objectClass);
  
?>
<div id="mainDivContainer" class="container" dojoType="dijit.layout.BorderContainer" liveSplitters="false">
  <div dojoType="dijit.layout.ContentPane" region="center" splitter="true">
    <div class="container" dojoType="dijit.layout.BorderContainer"  liveSplitters="false">
      <div id="listBarShow" class="dijitAccordionTitle" onMouseover="showList('mouse')" onClick="showList('click');">
        <div id="listBarIcon" align="center"></div>
      </div>
	  <div id="listDiv" dojoType="dijit.layout.ContentPane" region="<?php echo $positionListDiv; ?>" splitter="true" 
	  style="<?php if($positionListDiv=='top'){echo "height:".$listHeight;}else{ echo "width:".$tableWidth[0];}?>">
	     <script type="dojo/connect" event="resize" args="evt">
            if (switchedMode) return;
            var paramDiv=<?php echo json_encode($positionListDiv); ?>;
            var paramMode=<?php echo json_encode($codeModeLayout); ?>;
            if(paramDiv=="top" && paramMode!='switch'){
              saveContentPaneResizing("contentPaneTopDetailDivHeight<?php echo $objectClass;?>", dojo.byId("listDiv").offsetHeight, true);
            }else if(paramMode!='switch'){
              saveContentPaneResizing("contentPaneTopDetailDivWidth<?php echo $objectClass;?>", dojo.byId("listDiv").offsetWidth, true);
            }
         </script>
	     <?php include 'objectList.php'?>
	  </div>
	  <div id="contentDetailDiv" dojoType="dijit.layout.ContentPane" region="center"  style="width:<?php echo $tableWidth[1];?>;">
	    <script type="dojo/connect" event="resize" args="evt">
          var paramDiv=<?php echo json_encode($positionListDiv); ?>;
          var paramRightDiv=<?php echo json_encode($positonRightDiv);?>;
          var paramMode=<?php echo json_encode($codeModeLayout); ?>;
          resizeListDiv();
          if (checkValidatedSize(paramDiv,paramRightDiv, paramMode)){
            return;
          }
          if (contentPaneResizingInProgress) clearTimeout(contentPaneResizingInProgress);
          if(paramDiv=="top" && paramMode!='switch'){
            saveContentPaneResizing("contentPaneDetailDivHeight<?php echo $objectClass;?>", dojo.byId("contentDetailDiv").offsetHeight, true);
          } else if(paramMode!='switch'){
            saveContentPaneResizing("contentPaneDetailDivWidth<?php echo $objectClass;?>", dojo.byId("contentDetailDiv").offsetWidth, true);
            refreshObjectDivAfterResize();
          }
          if (paramMode=='switch' && dojo.byId('contentDetailDiv').offsetHeight<=15) setTimeout("dojo.byId('contentDetailDiv').style.top=(parseInt(dojo.byId('contentDetailDiv').style.top)-6)+'px';dijit.byId('contentDetailDiv').resize({h:21});",10);
         </script>
	    <div class="container" dojoType="dijit.layout.BorderContainer"  liveSplitters="false">
	       <div id="detailBarShow" class="dijitAccordionTitle"
              onMouseover="hideList('mouse');" onClick="hideList('click');"
              <?php if (RequestHandler::isCodeSet('switchedMode') and RequestHandler::getValue('switchedMode')=='on') echo ' style="display:block;"'?>>
              <div id="detailBarIcon" align="center"></div>
          </div>
	    <div id="detailDiv" dojoType="dijit.layout.ContentPane" region="center" >
		   <?php $noselect=true; include 'objectDetail.php'; ?>
		</div>
	  <?php 
            if (property_exists($objectClass, '_Note') and Module::isModuleActive('moduleActivityStream')) {
              $showNotes=true;
              $item=new $objectClass();
              if ($item->isAttributeSetToField('_Note','hidden')) $showNotes=false;
              else if (in_array('_Note',$item->getExtraHiddenFields(null, null, getSessionUser()->getProfile()))) $showNotes=false;
            } else {
              $showNotes=false;
            }
      if ($showNotes) {?>
	  <div id="detailRightDiv" dojoType="dijit.layout.ContentPane" region="<?php echo $positonRightDiv; ?>" splitter="true" 
	  style="<?php if($positonRightDiv=="bottom"){echo "height:".$rightHeight;}else{ echo "width:".$rightWidth;}?>">
      	  <script type="dojo/connect" event="resize" args="evt">
              var paramDiv=<?php echo json_encode($positionListDiv); ?>;
              var paramRightDiv=<?php echo json_encode($positonRightDiv); ?>;
              var paramMode=<?php echo json_encode($codeModeLayout); ?>;
              hideSplitterStream (paramRightDiv);
              checkValidatedSizeRightDiv(paramDiv,paramRightDiv);
              if(paramRightDiv=='trailing'){
                saveContentPaneResizing("contentPaneRightDetailDivWidth<?php echo $objectClass;?>", dojo.byId("detailRightDiv").offsetWidth, true);
                var newWidth=dojo.byId("detailRightDiv").offsetWidth;
                dojo.query(".activityStreamNoteContainer").forEach(function(node, index, nodelist) {
                  node.style.maxWidth=(newWidth-30)+"px";
                });
              }else {
                saveContentPaneResizing("contentPaneRightDetailDivHeight<?php echo $objectClass;?>", dojo.byId("detailRightDiv").offsetHeight, true);
                if (paramMode=='left') saveContentPaneResizing("contentPaneRightDetailDivWidth<?php echo $objectClass;?>", dojo.byId("detailRightDiv").offsetWidth, true);
                var newHeight=dojo.byId("detailRightDiv").offsetHeight;
                if (dojo.byId("noteNoteStream")) dojo.byId("noteNoteStream").style.height=(newHeight-40)+'px';
                var newWidth=dojo.byId("detailRightDiv").offsetWidth;
                dojo.query(".activityStreamNoteContainer").forEach(function(node, index, nodelist) {
                  node.style.maxWidth=((newWidth*.7)-30)+"px";
                });
              }
              if (paramRightDiv=='trailing' && evt.w) {
                refreshObjectDivAfterResize();
              }
      	  </script>
      	  <script type="dojo/connect" event="onLoad" args="evt">
              scrollInto();
	  	  </script>
          <?php include 'objectStream.php';?>
	  </div>
      <?php }?>  
      </div>
      </div>
    </div>
  </div>
</div>
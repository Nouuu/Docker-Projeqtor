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
 * Presents the action buttons of an object.
 * 
 */ 
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/objectButton.php'); 
  global $displayWidthButton;
  if (! isset($comboDetail)) {
    $comboDetail=false;
  }
  $id=null;
  $class=$_REQUEST['objectClass'];
  $objClassList = RequestHandler::getValue('objectClassList');
  Security::checkValidClass($class);
  if (array_key_exists('objectId',$_REQUEST)) {
  	$id=$_REQUEST['objectId'];
  }	
  $obj=new $class($id);
  $objectIsClosed=(isset($obj) and property_exists($obj, 'idle') and $obj->idle)?true:false;
  if (isset($_REQUEST['noselect'])) {
  	$noselect=true;
  }
  if (! isset($noselect)) {
  	$noselect=false;
  }
// MTY - LEAVE SYSTEM
    // Can't delete or copy if leave conditions are'nt satisfed.
    $noSelectLeaveDeleteCopy = isLeaveMngConditionsKO($class, $id);
// MTY - LEAVE SYSTEM  
    
  $printPage="objectDetail.php";
  $printPagePdf="objectDetail.php";
  $modePdf='pdf';
  $tmpMode=null;
  $existCustom=(file_exists('../report/object/'.$class.'_CustomReport.php'))?'custom':null;
  $existClass=(file_exists('../report/object/'.$class.'.php'))?'template':null;
  if ($existClass) { 
    $rpt=file_get_contents('../report/object/'.$class.'.php');
    if (! strpos($rpt,"templateReportExecute.php")>0) {
      $existClass="custom";
    }
  }
  if ($existClass=='template' and SqlElement::class_exists('TemplateReport') and Plugin::isPluginEnabled('templateReport')) {
    $tmpMode=TemplateReport::getMode($class);
  }
  
  if ($existClass or $existCustom) {
    if ($existClass=='custom') {
      $printPage='../report/object/'.$class.'.php';
      $printPagePdf='../report/object/'.$class.'.php';
    } else if ($existClass=='template') {
      if ($tmpMode=='revert') {
        if ($existCustom=='custom') {
          $printPage='../report/object/'.$class.'_CustomReport.php';
          $printPagePdf='../report/object/'.$class.'_CustomReport.php';
        } else {
          $printPage="objectDetail.php";
          $printPagePdf="objectDetail.php";
        }
      } else {
        if ($existCustom=='custom') {
          $printPage='../report/object/'.$class.'_CustomReport.php';
          $printPagePdf='../report/object/'.$class.'.php';
        } else {
          $printPage="objectDetail.php";
          $printPagePdf='../report/object/'.$class.'.php';
        }
      }
    } else { // ! existClass 
      if ($existCustom=='custom') {
        $printPage='../report/object/'.$class.'_CustomReport.php';
        $printPagePdf='../report/object/'.$class.'_CustomReport.php';
      } else {
        $printPage="objectDetail.php";
        $printPagePdf="objectDetail.php";
      }
    }
//     $printPage='../report/object/'.$class.$extCustom.'.php';
//     $printPagePdf='../report/object/'.$class.$extCustom.'.php';
    if (SqlElement::class_exists('TemplateReport') and Plugin::isPluginEnabled('templateReport')) {
      $tmpMode=TemplateReport::getMode($class);
      if ($tmpMode=='download') {
        $modePdf='download';
        //if ($custom=='default') $printPage="objectDetail.php"; // If template must be downloaded, do not use it for print
      } else if ($tmpMode=='show') {
        $modePdf='download'; // If template can be shown print will show, pdf will download
      } else if ($tmpMode=='multi') {
        $modePdf='download multi';
        //if ($custom=='default') $printPage="objectDetail.php";
      } else if ($tmpMode=='revert') {
        // detected some inconsistent custom report
        //$printPage="objectDetail.php";
        //$printPagePdf="objectDetail.php";
      } // else : keep default behavior
    }
  }
  $createRight=securityGetAccessRightYesNo('menu' . $class, 'create');
  if (!$obj->id) {
    $updateRight=$createRight;
  } else {
    $updateRight=securityGetAccessRightYesNo('menu' . $class, 'update', $obj);
  }
  $deleteRight=securityGetAccessRightYesNo('menu' . $class, 'delete', $obj);
  
  $displayWidthButton="9999";
  if (isset($_REQUEST ['destinationWidth'])) {
    $displayWidthButton=$_REQUEST ['destinationWidth'];
  }

  $cptButton=0;
  $isAttachmentEnabled = true; // allow attachment
  if (! Parameter::getGlobalParameter ( 'paramAttachmentDirectory' ) or ! Parameter::getGlobalParameter ( 'paramAttachmentMaxSize' )) {
  	$isAttachmentEnabled = false;
  }
  if ($objectIsClosed) {
    $isAttachmentEnabled = false;
  }
  if (!isset($readOnly)) {
    $readOnly=false;
  }
  if ($readOnly or $updateRight!='YES') {
    $isAttachmentEnabled = false;
  }
  
  $showAttachment=($isAttachmentEnabled and property_exists($obj,'_Attachment') and $updateRight=='YES' and isHtml5() and ! $readOnly )?true:false;
  $extendedZone=false;
  $maxTitleWidth=round($displayWidthButton*0.4,0);
  
  $isMailEnabled=(Parameter::getGlobalParameter( 'paramMailSmtpServer'))?true:false;
?>
<table style="width:100%;height:100%;">
 <tr style="height:100%";>
  <td style="z-index:-1;width:40%;white-space:nowrap;">  
    <div style="width:100%;height:100%;">
      <table style="width:100%;height:100%;">
        <tr style="height:35px;">
          <td style="width:43px;min-width:43px;max-width:43px;">&nbsp;
            <?php $iconClassName=((SqlElement::is_subclass_of($class, 'PlgCustomList'))?'ListOfValues':$class);?>
            <div style="position:absolute;left:0px;width:43px;max-width:50px;top:0px;height:35px;" class="iconHighlight">&nbsp;</div>
            <div style="position:absolute; top:2px;left:5px ;" class="icon<?php echo $iconClassName;?>32 icon<?php echo $iconClassName;?> iconSize32" style="margin-left:9px;width:32px;height:32px" /></div>          
          </td>
          <td class="title" style="width:10%;max-width:<?php echo $maxTitleWidth?>px;overflow:hidden">
            &nbsp;<?php echo i18n($_REQUEST['objectClass']);
//ADD BY Quentin Boudier - 2017-04-26 'copylink in title of object detail    '
            $ref=$obj->getReferenceUrl();
            echo '&nbsp;<span id="buttonDivObjectId">';
            echo '<span class="roundedButton">';
            echo '<a href="' . $ref . '" id="buttonDivObjectIdLink" onClick="copyDirectLinkUrl(\'Button\');return false;"' . ' title="' . i18n("rightClickToCopy") . '" style="cursor: pointer; '.((isNewGui())?'"':'color: white;" onmouseover="this.style.color=\'black\';" onmouseout="this.style.color=\'white\';"').'>';
            echo ($obj->id)?'&nbsp;#'.$obj->id:'';
 			      echo '&nbsp;</a>';
           	echo '</span>';
          	if (isNewGui()) echo '<input readOnly type="text" onClick="this.select();" id="directLinkUrlDivButton" style="display:none;font-size:10pt;position :absolute; top: 114px; left: 204px; border: 0;background: transparent;width:100%;" value="' . $ref . '" />';
          	else echo '<input readOnly type="text" onClick="this.select();" id="directLinkUrlDivButton" style="display:none;font-size:9px;color: #000000;position :absolute; top: 47px; left: 157px; border: 0;background: transparent;width:300px;" value="' . $ref . '" />';
          	echo '</span>';
// END ADD BY Quentin Boudier - 2017-04-26 'copylink in tilte of object detail	'
           	?>
          </td>
          <td class="title" style="height:35px;<?php if ($displayWidthButton<400) echo 'display:none;'?>">
            <div style="width:100%;height:100%;position:relative;<?php if ($displayWidthButton<400) echo 'display:none;'?>">
              <div id="buttonDivObjectName" style="width:100%;position:absolute;top:8px;text-overflow:ellipsis;overflow:hidden;">
                   <?php  
                    if (property_exists($obj,'name') and $obj->name){ 
                   	  echo '-&nbsp;';
                   	  if (isset($obj->_isNameTranslatable) and $obj->_isNameTranslatable) {
                   	  	echo i18n($obj->name);
                   	  } else { 
                   	  	echo $obj->name;
                      }
                    }?>
              </div>
            </div>
          </td>
        </tr>
      </table>  
    </div> 
  </td>
  <td style="width:1%; text-align:right;"  >
      <?php 
      $creationInfoWidth=0;
      if (property_exists($obj, 'idStatus') and $displayWidthButton>=500) $creationInfoWidth+=130;
      if ($displayWidthButton>=800) $creationInfoWidth+=130;
      ?>
      <div style="width:<?php echo $creationInfoWidth;?>px;margin-right:0px;white-space:nowrap;max-height:32px;" id="buttonDivCreationInfo"><?php include_once '../tool/getObjectCreationInfo.php';?></div>
  </td>
  <td style="width:1%"></td>
  <td  style="white-space:nowrap;width:33%">
    <div style="float:right;position:relative;width:fit-content;white-space:nowrap;<?php if ($showAttachment) echo 'padding-right:44px';?>" id="buttonDivContainerDiv"> 
    <?php if (! $comboDetail and $class!='GlobalView') {?>
      <?php organizeButtons();?>
      <button id="newButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonNew', array(i18n($_REQUEST['objectClass'])));?>"
       iconClass="dijitButtonIcon dijitButtonIconNew" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
		      dojo.byId("newButton").blur();
          hideResultDivs();
          hideExtraButtons('extraButtonsDetail');
          id=dojo.byId('objectId');
          var objectClass=dojo.byId('objectClass').value;
	        if (id) { 	
            var currentItem=historyTable[historyPosition];
            var currentScreen=(currentItem && currentItem.length>2)?currentItem[2]:null;
            if (currentItem && (currentScreen=="Planning" || currentScreen=="GlobalPlanning") || ( (currentScreen=="VersionsPlanning" || currentScreen=="ResourcePlanning") && objectClass=="Activity")){
              var currentItemParent = currentItem[1];
              var originClass = currentItem[0];
              var url = 'objectDetail.php?insertItem=true&currentItemParent='+currentItemParent+'&originClass='+originClass;
              if(currentScreen=="VersionsPlanning" || currentScreen=="ResourcePlanning"){
                url+="&currentPlanning="+currentScreen;
              } 
              id.value="";
		          unselectAllRows("objectGrid");
              loadContent(url, "detailDiv", 'listForm');
            }else if ( (currentScreen=="VersionsPlanning" || currentScreen=="ResourcePlanning") && objectClass!="Activity"){
                showAlert(i18n('alertActivityVersion'));
            }else{
		          id.value="";
		          unselectAllRows("objectGrid");
              loadContent("objectDetail.php", "detailDiv", 'listForm');
              loadContentStream();
            }
          } else { 
            showError(i18n("errorObjectId"));
	        }
        </script>
      </button>
      <?php organizeButtons();?>
      <button id="saveButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonSave', array(i18n($_REQUEST['objectClass'])));?>"
       <?php if ($noselect) {echo "disabled";} ?>
       iconClass="dijitButtonIcon dijitButtonIconSave" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
          hideExtraButtons('extraButtonsDetail');
		      saveObject();
        </script>
      </button>
      <?php organizeButtons();?>
      <button id="undoButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonUndo', array(i18n($_REQUEST['objectClass'])));?>"
       <?php if ($noselect or 1) {echo "disabled style=\"display:none;\"";} ?>
       iconClass="dijitButtonIcon dijitButtonIconUndo" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
          dojo.byId("undoButton").blur();
          hideResultDivs();
          hideExtraButtons('extraButtonsDetail');
          loadContentStream();
// ADD BY Marc TABARY - 2017-03-10 - PERIODIC YEAR BUDGET ELEMENT
          // If undo Organization's detail screen, must passed periodic year in REQUEST
          cl='';
          if (dojo.byId('objectClass')) {
            cl=dojo.byId('objectClass').value;
          }
          if (cl=='Organization' && dijit.byId('OrganizationBudgetElementCurrent__byMet_periodYear')) {
            param='?OrganizationBudgetPeriod='+dijit.byId('OrganizationBudgetElementCurrent__byMet_periodYear').get("value");
          } else {
            param='';
          }
          loadContent("objectDetail.php"+param, "detailDiv", 'listForm');
// END ADD BY Marc TABARY - 2017-03-10 - PERIODIC YEAR BUDGET ELEMENT
// COMMENT BY Marc TABARY - 2017-03-10 - PERIODIC YEAR BUDGET ELEMENT
//          loadContent("objectDetail.php", "detailDiv", 'listForm');
// END COMMENT BY Marc TABARY - 2017-03-10 - PERIODIC YEAR BUDGET ELEMENT
          formChangeInProgress=false;
        </script>
      </button>
     <?php // organizeButtons(); // removed on V7.1 : buttons undo and refresh not visible at same time?>
     <button id="refreshButton" dojoType="dijit.form.Button" showlabel="false" 
       title="<?php echo i18n('buttonRefresh', array(i18n($_REQUEST['objectClass'])));?>"
       <?php if ($noselect) {echo "disabled";} ?> 
       iconClass="dijitButtonIcon dijitButtonIconRefresh" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
          dojo.byId("refreshButton").blur();
          hideResultDivs();
          hideExtraButtons('extraButtonsDetail');
          formChangeInProgress=false;
// ADD BY Marc TABARY - 2017-03-10 - PERIODIC YEAR BUDGET ELEMENT
          // If undo Organization's detail screen, must passed periodic year in REQUEST
          cl='';
          if (dojo.byId('objectClass')) {
            cl=dojo.byId('objectClass').value;
          }
          if (cl=='Organization' && dijit.byId('OrganizationBudgetElementCurrent__byMet_periodYear')) {
            param='?OrganizationBudgetPeriod='+dijit.byId('OrganizationBudgetElementCurrent__byMet_periodYear').get("value");
          } else {
            param='';
          }
          loadContent("objectDetail.php"+param, "detailDiv", 'listForm');
          loadContentStream();
// END ADD BY Marc TABARY - 2017-03-10 - PERIODIC YEAR BUDGET ELEMENT
// COMMENT BY Marc TABARY - 2017-03-10 - PERIODIC YEAR BUDGET ELEMENT
//          loadContent("objectDetail.php", "detailDiv", 'listForm');
// END COMMENT BY Marc TABARY - 2017-03-10 - PERIODIC YEAR BUDGET ELEMENT        </script>
      </button>        
      <?php organizeButtons();
      if (! (property_exists($_REQUEST['objectClass'], '_noCopy')) ) { ?>
      <?php organizeButtons();?>
      <button id="copyButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonCopy', array(i18n($_REQUEST['objectClass'])));?>"
       <?php
// MTY - LEAVE SYSTEM       
            if ($noselect or $noSelectLeaveDeleteCopy) {echo "disabled";}
// MTY - LEAVE SYSTEM       
       ?>
       iconClass="dijitButtonIcon dijitButtonIconCopy" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
          hideExtraButtons('extraButtonsDetail');
          hideResultDivs();
          <?php 
          $crit=array('name'=> $_REQUEST['objectClass']);
          $paramCopy="copyProject";
          if($_REQUEST['objectClass'] != "Project"){
            $copyable=SqlElement::getSingleSqlElementFromCriteria('Copyable', $crit);
            //if ($_REQUEST['objectClass']=='ProductVersion' or $_REQUEST['objectClass']=='ComponentVersion') {
            if ($_REQUEST['objectClass']=='ComponentVersion') {
            	$paramCopy="copyVersion";
            	echo "copyObjectBox('$paramCopy');";
            } else if ($copyable->id) {
              $paramCopy="copyObjectTo";
              echo "copyObjectBox('$paramCopy');";
            }else{
              //gautier #2522
              if ($_REQUEST['objectClass']=='Document'){
                $paramCopy="copyDocument";
                echo "copyObjectBox('$paramCopy');";
              }else{            
                echo "copyObject('" .$_REQUEST['objectClass'] . "');";
              }
            }
          }else{
            echo "copyObjectBox('$paramCopy');";
          }
          ?>
        </script>
      </button>    
<?php }?>
            <?php organizeButtons();?>
      <button id="deleteButton" dojoType="dijit.form.Button" showlabel="false" 
       title="<?php echo i18n('buttonDelete', array(i18n($_REQUEST['objectClass'])));?>"
       <?php
// MTY - LEAVE SYSTEM       
            if ($noselect or $noSelectLeaveDeleteCopy) {echo "disabled";} 
// MTY - LEAVE SYSTEM       
       ?> 
       iconClass="dijitButtonIcon dijitButtonIconDelete" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
          dojo.byId("deleteButton").blur();
          hideResultDivs();
          hideExtraButtons('extraButtonsDetail');
		      action=function(){
		        loadContent("../tool/deleteObject.php", "resultDivMain", 'objectForm', true);
            loadContentStream();
          };
          var alsoDelete="";
          showConfirm(i18n("confirmDelete", new Array("<?php echo i18n($_REQUEST['objectClass']);?>",dojo.byId('id').value))+alsoDelete ,action);
        </script>
      </button>  
      <?php organizeButtons();?>
      <button id="printButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonPrint', array(i18n($_REQUEST['objectClass'])));?>"
       <?php if ($noselect) {echo "disabled";} ?> 
       iconClass="dijitButtonIcon dijitButtonIconPrint" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
		    dojo.byId("printButton").blur();
        hideResultDivs();
        hideExtraButtons('extraButtonsDetail');
        if (dojo.byId("printPdfButton")) {dojo.byId("printPdfButton").blur();}
        showPrint("<?php echo $printPage;?>", null, null, null, 'P');
        </script>
      </button>
      <?php if ($_REQUEST['objectClass']!='Workflow' and $_REQUEST['objectClass']!='Mail') {
     // Disable PDF Export for :
     //    - Wokflow : too complex and systematically fails in timeout
     //    - Mail : description is content of email possibly truncated, so tags may be not closed?>    
     <?php organizeButtons();?>
     <button id="printButtonPdf" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo ($modePdf=='pdf')?i18n('reportPrintPdf'):i18n('reportPrintTemplate');?>"
       <?php if ($noselect) {echo "disabled";} ?> 
       iconClass="dijitButtonIcon dijitButtonIcon<?php echo ucfirst($modePdf);?>" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
        dojo.byId("printButton").blur();
        hideResultDivs();
        hideExtraButtons('extraButtonsDetail');
        if (dojo.byId("printPdfButton")) {dojo.byId("printPdfButton").blur();}
        <?php if (substr($modePdf,-5)=="multi" and SqlElement::class_exists('TemplateReport') ) {?>
        selectTemplateForReport('<?php echo $class?>','detail');
        <?php } else { ?> 
        showPrint("<?php echo $printPagePdf;?>", null, null, '<?php echo $modePdf;?>', 'P');
        <?php } ?>
        </script>
      </button>   
<?php } ?>  
      <?php 
      $clsObj=get_class($obj);
      if ($clsObj=='TicketSimple') {$clsObj='Ticket';}
      $mailable=SqlElement::getSingleSqlElementFromCriteria('Mailable', array('name'=>$clsObj));
      if ($mailable and $mailable->id and $isMailEnabled) {
      ?>
     <?php organizeButtons();?>
     <button id="mailButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonMail', array(i18n($clsObj)));?>"
       <?php if ($noselect) {echo "disabled";} ?>
       iconClass="dijitButtonIcon dijitButtonIconEmail" class="detailButton" >
        <script type="dojo/connect" event="onClick" args="evt">
          showMailOptions();
          hideResultDivs();
          hideExtraButtons('extraButtonsDetail');  
        </script>
      </button>
      <?php 
        $userId=getSessionUser()->id;
        $sub=SqlElement::getSingleSqlElementFromCriteria('Subscription', array('refType'=>get_class($obj),'refId'=>$obj->id,'idAffectable'=>$userId));
        $subscribed=($sub and $sub->id)?true:false;
        $canSubscribeForOthers=true;
        $canSubscribeForHimself=true;
		    $crit=array('scope' => 'subscription','idProfile' => getSessionUser()->idProfile);
		    $habilitation=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', $crit);
		    $scope=new AccessScopeSpecific($habilitation->rightAccess, true);
		    if (! $scope->accessCode or $scope->accessCode == 'NO' ) {
		      $canSubscribeForOthers=false;
		      $canSubscribeForHimself=false;
		    } else if ($scope->accessCode == 'OWN') {
		    	$canSubscribeForOthers=false;
		    	$canSubscribeForHimself=true;
		    } else {
		    	$canSubscribeForOthers=true;
		    	$canSubscribeForHimself=true;
		    }
		    ?>
		  <?php if ($canSubscribeForHimself or $canSubscribeForOthers) {?>
      <?php organizeButtons();?>
      <button id="subscribeButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('showSubscribeOptions');?>"
       <?php if ($noselect) {echo "disabled";} ?> 
       iconClass="imageColorNewGui dijitButtonIcon dijitButtonIconSubscribe<?php if ($subscribed) echo 'Valid';?>" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
          showExtraButtons('subscribeButton');
          hideResultDivs();
        </script>
      </button>   
      <div class="statusBar" id="subscribeButtonDiv" style="display:none;position:absolute;<?php echo (isNewGui())?'width:250px;border:1px solid var(--color-light);border-right:0;':'width:220px;';?>z-index:100;">
        <button id="subscribeButtonSubscribe" dojoType="dijit.form.Button" showlabel="true" style="" <?php if ($subscribed) echo 'disabled';?>
          iconClass="imageColorNewGui dijitButtonIcon dijitButtonIconSubscribe" class="detailButton"><div style="width:180px;"><?php echo i18n('subscribeButton')?></div>
          <script type="dojo/connect" event="onClick" args="evt">
            hideExtraButtons('subscribeButton');  
            hideResultDivs();
            subscribeToItem('<?php echo get_class($obj)?>','<?php echo $obj->id;?>','<?php echo $userId;?>');
          </script>
        </button><br/>
        <button id="subscribeButtonUnsubscribe" dojoType="dijit.form.Button" showlabel="true"  style="" <?php if (! $subscribed) echo 'disabled';?>
          iconClass="imageColorNewGui dijitButtonIcon dijitButtonIconDelete" class="detailButton"><div style="width:180px;"><?php echo i18n('unsubscribeButton')?></div>
          <script type="dojo/connect" event="onClick" args="evt">
            hideExtraButtons('subscribeButton');  
            hideResultDivs();
            unsubscribeFromItem('<?php echo get_class($obj)?>','<?php echo $obj->id;?>','<?php echo getSessionUser()->id;?>');
          </script>
        </button><br/>  
        <?php if ($canSubscribeForOthers) {?>
        <button id="subscribeButtonSubscribeOthers" dojoType="dijit.form.Button" showlabel="true"
          iconClass="imageColorNewGui notDijitButtonIcon iconTeam22 iconTeam iconSize22" class="detailButton"><div style="width:180px"><?php echo i18n('subscribeOthersButton')?></div>
          <script type="dojo/connect" event="onClick" args="evt">
            hideExtraButtons('subscribeButton');  
            hideResultDivs();
            subscribeForOthers('<?php echo get_class($obj)?>','<?php echo $obj->id;?>');
          </script>
        </button><br/> 
        <?php } else {?>
        <button id="subscribeButtonSubscribers" dojoType="dijit.form.Button" showlabel="true"
          iconClass="imageColorNewGui notDijitButtonIcon iconTeam22 iconTeam iconSize22" class="detailButton"><div style="width:180px"><?php echo i18n('subscribersList')?></div>
          <script type="dojo/connect" event="onClick" args="evt">
            hideExtraButtons('subscribeButton');  
            hideResultDivs();
            showSubscribersList('<?php echo get_class($obj)?>','<?php echo $obj->id;?>');
          </script>
        </button><br/> 
        <?php }?>
        <button id="subscribeButtonSubscribtionList" dojoType="dijit.form.Button" showlabel="true"
          iconClass="imageColorNewGui notDijitButtonIcon iconListOfValues22 iconListOfValues iconSize22 idijitButtonIcon" class="detailButton"><div style="width:180px"><?php echo i18n('showSubscribedItemsList')?></div>
          <script type="dojo/connect" event="onClick" args="evt">
            hideExtraButtons('subscribeButton');  
            hideResultDivs();
            showSubscriptionList('<?php echo getSessionUser()->id;?>');
          </script>
        </button>     
      </div>
    <?php }?>
    <?php
        } // end of : if ($mailable and $mailable->id) {
      ?>
    <?php 
    $paramRightDiv=Parameter::getUserParameter('paramRightDiv');
    $showActivityStream=false;
    $currentScreen=getSessionValue('currentScreen');
    if ($currentScreen=='Object') $currentScreen=$objectClass;
    if($paramRightDiv=="bottom"){
      $activityStreamSize=getHeightLaoutActivityStream($currentScreen);
      $activityStreamDefaultSize=getDefaultLayoutSize('contentPaneRightDetailDivHeight');
    }else{
      $activityStreamSize=getWidthLayoutActivityStream($currentScreen);
      $activityStreamDefaultSize=getDefaultLayoutSize('contentPaneRightDetailDivWidth');
    }
    
    $user=getSessionUser();
    $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$user->getProfile($obj),'scope'=>'multipleUpdate'));
    $list=new ListYesNo($habil->rightAccess);
    $buttonMultiple=($list->code=='NO')?false:true;
    if (! isNewGui() and $buttonMultiple and ! array_key_exists('planning',$_REQUEST) and $objClassList != 'GlobalView') {?>
    <?php organizeButtons();?> 
    <span id="multiUpdateButtonDiv" >
    <button id="multiUpdateButton" dojoType="dijit.form.Button" showlabel="false"
       title="<?php echo i18n('buttonMultiUpdate');?>"
       iconClass="dijitButtonIcon dijitButtonIconMultipleUpdate" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
          hideResultDivs();
          var pos=<?php echo json_encode($paramRightDiv) ;?>;
          if (dijit.byId('detailRightDiv')) {
            if(pos=='bottom'){
              if(dijit.byId('detailRightDiv').h != 0){
                saveDataToSession('showActicityStream','show');
              }else{
                saveDataToSession('showActicityStream','hide');
              }
            }else{
              if(dijit.byId('detailRightDiv').w != 0){
                saveDataToSession('showActicityStream','show');
              }else{
                saveDataToSession('showActicityStream','hide');
              }
            }
          }
          hideStreamMode('false','<?php echo $paramRightDiv;?>','<?php echo $activityStreamDefaultSize;?>',false);
          startMultipleUpdateMode('<?php echo get_class($obj);?>');  
          hideExtraButtons('extraButtonsDetail');
        </script>
    </button>
    </span>
    <?php }
    //if (array_key_exists('planning',$_REQUEST) and array_key_exists('planningType',$_REQUEST) and $_REQUEST['planningType']=='Planning') {
    ?>
    <?php 
    $isGlobal=GlobalPlanningElement::isGlobalizable($class);
    if (RequestHandler::isCodeSet('planning') and RequestHandler::isCodeSet('planningType') and RequestHandler::getValue('planningType')=='Planning') {organizeButtons(2);}?>
    <div id="indentButtonDiv" class="statusBar" style="display:<?php echo ($isGlobal)?'none':'block';?>;<?php echo (isNewGui())?'height:64px;width:36px;background:#fff !important;':'height:32px; width:'.(($extendedZone)?'72':'68').'px;';?>">
     <button id="indentDecreaseButton" dojoType="dijit.form.Button" showlabel="false" 
        title="<?php echo i18n('indentDecreaseButton');?>"
        iconClass="dijitButtonIcon dijitButtonIconDecrease" class="statusBar detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
          indentTask("decrease");  
          hideResultDivs();
          hideExtraButtons('extraButtonsDetail');
        </script>
      </button><?php if (isNewGui()) echo '<br/>';?>
      <button id="indentIncreaseButton" dojoType="dijit.form.Button" showlabel="false"
        title="<?php echo i18n('indentIncreaseButton');?>"
        iconClass="dijitButtonIcon dijitButtonIconIncrease" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
          indentTask("increase");
          hideResultDivs();
          hideExtraButtons('extraButtonsDetail');  
        </script>
      </button> 
    </div>
    <?php }?> 
    <?php 
      $crit="nameChecklistable='".get_class($obj)."'";
      $type='id'.get_class($obj).'Type';
      if (property_exists($obj,$type) ) {
        $crit.=' and (idType is null ';
        if ($obj->$type) {
          $crit.=" or idType=".$obj->$type;
        }
        $crit.=')';
  		}
  		$cd=new ChecklistDefinition();
  		$cdList=$cd->getSqlElementsFromCriteria(null,false,$crit);
  		$user=getSessionUser();
  		$habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$user->getProfile($obj),'scope'=>'checklist'));
  		$list=new ListYesNo($habil->rightAccess);
  		$displayChecklist=Parameter::getUserParameter('displayChecklist');
  		if ($list->code!='YES' or $displayChecklist!='REQ') {
  		  $buttonCheckListVisible="never";
  		} else if (count($cdList)>0 and $obj->id) {
        $buttonCheckListVisible="visible";
      } else {
        $buttonCheckListVisible="hidden";
      }
      //$displayButton=( $buttonCheckListVisible=="visible")?'void':'none';?>
    <?php if ($buttonCheckListVisible=="visible" and $obj->id) {organizeButtons();}?>
    <span id="checkListButtonDiv" style="display:<?php echo ($buttonCheckListVisible=='visible')?'inline':'none';?>;">
      <?php if ($buttonCheckListVisible!="never") {?>
      <button id="checkListButton" dojoType="dijit.form.Button" showlabel="false"
        title="<?php echo i18n('Checklist');?>"
        iconClass="dijitButtonIcon dijitButtonIconChecklist" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
          showChecklist('<?php echo get_class($obj);?>');  
          hideResultDivs();
          hideExtraButtons('extraButtonsDetail');
        </script>
      </button>
      <?php }?>
      <input type="hidden" id="buttonCheckListVisible" value="<?php echo $buttonCheckListVisible;?>" />
    </span>
    
    <?php $buttonHistoryVisible=true; 
      $paramHistoryVisible=Parameter::getUserParameter('displayHistory');
      
      if ($paramHistoryVisible and $paramHistoryVisible!='REQ') {
        $buttonHistoryVisible=false;
      }
      if (!$obj->id) $buttonHistoryVisible=false;
      //gautier 
      if(RequestHandler::isCodeSet('mode')){
        if(RequestHandler::getValue('mode')=='new'){
          $buttonHistoryVisible=false;
        }
      }
      
    ?>
    <?php if ($paramHistoryVisible=='REQ' and $obj->id) organizeButtons();?>
    <span id="historyButtonDiv" style="display:<?php echo ($buttonHistoryVisible)?'inline':'none';?>;">
      <?php if ($paramHistoryVisible=='REQ') {?>
      <button id="historyButton" dojoType="dijit.form.Button" showlabel="false"
        title="<?php echo i18n('showHistory');?>"
        iconClass="dijitButtonIcon dijitButtonIconHistory" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">
          showHistory('<?php echo get_class($obj);?>');  
          hideResultDivs();
          hideExtraButtons('extraButtonsDetail');
        </script>
      </button>
      <?php }?>
      <input type="hidden" id="buttonHistoryVisible" value="<?php echo $paramHistoryVisible;?>" />
    </span>
     <?php organizeButtons();?> 
     <?php 
       $paramRightDiv=Parameter::getUserParameter('paramRightDiv');
       $showActivityStream=false;
       $currentScreen=getSessionValue('currentScreen');
       if ($currentScreen=='Object') $currentScreen=$objectClass;
       if($paramRightDiv=="bottom"){
         $activityStreamSize=getHeightLaoutActivityStream($currentScreen);
         $activityStreamDefaultSize=getDefaultLayoutSize('contentPaneRightDetailDivHeight');
       }else{
         $activityStreamSize=getWidthLayoutActivityStream($currentScreen);
         $activityStreamDefaultSize=getDefaultLayoutSize('contentPaneRightDetailDivWidth');
       }
       if ($activityStreamSize) {
         $showActivityStream=true;
       }
       $display=true;
       if(RequestHandler::isCodeSet('mode')){
          if(RequestHandler::getValue('mode')=='new'){
            $display=false;
          }
       }
     ?>
    <?php if (property_exists($objectClass, '_Note') and Module::isModuleActive('moduleActivityStream')and $display==true ) {?>
    <button id="hideStreamButton" dojoType="dijit.form.Button" showlabel="false" 
      title="<?php echo ($showActivityStream==false)?i18n('showActivityStream'):i18n('hideActivityStream');?>"
      <?php //if ($noselect) {echo 'style="display:none;"';}?> 
      iconClass="imageColorNewGui <?php if(! $showActivityStream){echo 'iconActivityStream22 iconActivityStream iconSize22';}else{echo 'iconActivityStreamClose22 iconActivityStreamClose iconSize22';}?>" class="detailButton">
      <script type="dojo/connect" event="onClick" args="evt">
         hideResultDivs();
         hideStreamMode('<?php echo ($showActivityStream)?'false':'true';?>','<?php echo $paramRightDiv;?>','<?php echo $activityStreamDefaultSize;?>',false);
      </script>
    </button>
    <?php }?>
    <?php $extraPlgButtons=Plugin::getButtons('detail', $objectClass);
    foreach ($extraPlgButtons as $bt) { 
    organizeButtons();?>
    <span id="pluginButton<?php echo $bt->id;?>Div" style="display:inline;">
      <button id="pluginButton<?php echo $bt->id;?>" dojoType="dijit.form.Button" showlabel="false"
        title="<?php echo i18n($bt->buttonName);?>"
        <?php if ($noselect) {echo "disabled";} ?>
        iconClass="<?php echo $bt->iconClass;?>" class="detailButton pluginButton">
        <script type="dojo/connect" event="onClick" args="evt">
          <?php if ($bt->scriptJS) {?>
          <?php echo $bt->scriptJS;?>;
          <?php } else {?>
          if (waitingForReply) {
            showInfo(i18n("alertOngoingQuery"));
            return true;
          }
          for (name in CKEDITOR.instances) {
            CKEDITOR.instances[name].updateElement();
          }
          dojo.byId("pluginButton<?php echo $bt->id;?>").blur();
          submitForm("<?php echo $bt->scriptPHP;?>", "resultDivMain", "listForm", true);
          <?php }?>
          hideExtraButtons('extraButtonsDetail');
          hideResultDivs();
        </script>
      </button>
    </span>
   <?php }?>
    <?php organizeButtonsEnd();?>
      <input type="hidden" id="createRight" name="createRight" value="<?php echo $createRight;?>" />
      <input type="hidden" id="updateRight" name="updateRight" value="<?php echo (!$obj->id)?$createRight:$updateRight;?>" />
      <input type="hidden" id="deleteRight" name="deleteRight" value="<?php echo $deleteRight;?>" />
       <?php if ($showAttachment) {
         $labelAttachmentFileDirect=i18n("dragAndDrop");
         ?>
			<span id="attachmentFileDirectDiv" title="<?php echo $labelAttachmentFileDirect;?>" style="position:relative;<?php echo (!$obj->id or $comboDetail)?'visibility:hidden;':'';?>;padding-left:4px;">
			<div dojoType="dojox.form.Uploader" type="file" id="attachmentFileDirect" name="attachmentFile" 
			MAX_FILE_SIZE="<?php echo Parameter::getGlobalParameter('paramAttachmentMaxSize');?>"
			url="../tool/saveAttachment.php?attachmentRefType=<?php echo get_class($obj);?>&attachmentRefId=<?php echo $obj->id;?>"
			multiple="true" class="directAttachment detailButton"
			uploadOnSelect="true"
			target="resultPost"
			onBegin="hideResultDivs();saveAttachment(true);"
			iconClass="iconAttachFiles"
			onError="dojo.style(dojo.byId('downloadProgress'), {display:'none'});"
			style="font-size:60%;height:26px; width:36px; border: 1px dashed #ffffff !important; padding:0; color: #000000; position:absolute;
			 text-align: left; vertical-align:middle;font-size: 7pt; opacity: 0.8;z-index:9999"
			label="">		 
			  <script type="dojo/connect" event="onComplete" args="dataArray">
          saveAttachmentAck(dataArray);
	      </script>
				<script type="dojo/connect" event="onProgress" args="data">
          saveAttachmentProgress(data);
	      </script>
	      <script type="dojo/connect" event="onError" args="data">
          hideWait();
          showError(i18n("uploadUncomplete"));
	      </script>
			</div>			
			</span>
			<?php } else {?>
			 <span style="display:inline-block;width:2px"></span>
			<?php }?>
    </td>
  </tr>
</table>
<?php 
function organizeButtons($nbButton=1) {
	global $displayWidthButton, $cptButton,$showAttachment,$extendedZone, $obj;
	$buttonWidth=34;
	$cptButton+=$nbButton;
	$requiredWidth=$cptButton*$buttonWidth;
	if ($showAttachment and $obj->id) {
		$requiredWidth+=44;
	}
	if ( ($requiredWidth>($displayWidthButton/3) and $displayWidthButton<1000)
	  or (isNewGui() and $cptButton>3) ) {
		if (! $extendedZone) {
			$extendedZone=true;
			echo '<div dojoType="dijit.form.Button" showlabel="false" title="'. i18n('extraButtonsBar'). '" '
          .' iconClass="dijitButtonIcon dijitButtonIconExtraButtons" class="detailButton"'
 		      .' id="extraButtonsDetail" onClick="showExtraButtons(\'extraButtonsDetail\')" '
 		      .'></div>';
			echo '<div class="statusBar" id="extraButtonsDetailDiv" style="display:none;position:absolute;'.((isNewGui())?'width:34px;':'width:36px;').'">';
		} else {
			echo '<div></div>';
		}
	}
	
}
function organizeButtonsEnd() {
	global $displayWidth, $cptButton,$showAttachment,$extendedZone;
	if ($extendedZone) {
		echo '</div>';
	}
}
?>
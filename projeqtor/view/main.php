<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : 
 *  2014 - Caccia : fix #1544
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
 * Main page of application.
 * This page includes Frame definitions and framework requirements.
 * All the other pages are included into this one, in divs, using Ajax.
 * 
 *  Remarks for deployment :
 *    - set isDebug:false in djConfig
 */
$mobile=false;
require_once "../tool/projeqtor.php";
if (isset($locked) and $locked) {
  include_once "../view/locked.php";
  exit;
}
header ('Content-Type: text/html; charset=UTF-8');
scriptLog('   ->/view/main.php');
if (Sql::getDbVersion()!=$version) {
	//Here difference of version is an important issue => disconnect and get back to login page.
	//session_destroy();
	Audit::finishSession();
	include_once 'login.php';
	exit;
}
unsetSessionTable('_tablesFormatList', 'User'); // Force refresh of User description (otherwise, after first install User screen is not correct)
$currency=Parameter::getGlobalParameter('currency');
$currencyPosition=Parameter::getGlobalParameter('currencyPosition');
checkVersion(); 
// Set Project & Planning element as cachable : will not change during operation
SqlElement::$_cachedQuery['Project']=array();
SqlElement::$_cachedQuery['ProjectPlanningElement']=array();
SqlElement::$_cachedQuery['PlanningElement']=array();
$keyDownEventScript=NumberFormatter52::getKeyDownEvent();
if(isNewGui())$firstColor=Parameter::getUserParameter('newGuiThemeColor');
$background=(isNewGui())?'#'.$firstColor.' !important':' #C3C3EB';
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>   
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta name="keywork" content="projeqtor, project management" />
  <meta name="author" content="projeqtor" />
  <meta name="Copyright" content="Pascal BERNARD" />
<?php if (! isset($debugIEcompatibility) or $debugIEcompatibility==false) {?>  
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
<?php }?> 
  <title><?php echo (Parameter::getGlobalParameter('paramDbDisplayName'))?Parameter::getGlobalParameter('paramDbDisplayName'):i18n("applicationTitle");?></title>
  <link rel="stylesheet" type="text/css" href="css/jsgantt.css" />
  <link rel="stylesheet" type="text/css" href="css/projeqtor.css" />
  <link rel="stylesheet" type="text/css" href="css/projeqtorFlat.css" />
  <link rel="stylesheet" type="text/css" href="css/projeqtorHRFlat.css" />
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="stylesheet" type="text/css" href="../external/dojox/form/resources/CheckedMultiSelect.css" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
<!-- ELIOTT - LEAVE SYSTEM -->
  <link rel="stylesheet" type="text/css" href="css/projeqtorHr.css" />
  <link rel="stylesheet" href="../external/dojox/calendar/themes/tundra/Calendar.css" />
  <?php if (isNewGui()) {?>
  <link rel="stylesheet" type="text/css" href="css/projeqtorNew.css" />
  <link rel="stylesheet" type="text/css" href="../external/codrops/css/component.css" />
  <script type="text/javascript" src="../external/codrops/js/modernizr-custom.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/codrops/js/classie.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/codrops/js/mainLeftMenu.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtorNewGui.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/dojox/mobile/deviceTheme.js" data-dojo-config="mblUserAgent: 'Custom'"></script>
  <?php }?>
  <script type="text/javascript" src="js/dynamicCss.js?version=<?php echo $version.'.'.$build;?>" ></script>
<!-- ELIOTT - LEAVE SYSTEM -->
  <script type="text/javascript" src="../external/html2canvas/html2canvas.js?version=<?php echo $version.'.'.$build;?>"></script>
  <?php if (isHtml5()) {?>
  <script type="text/javascript" src="../external/pdfmake/pdfmake.min.js?version=<?php echo $version.'.'.$build;?>"></script>
  <?php }?>
  <script type="text/javascript" src="../external/pdfmake/vfs_fonts.js?version=<?php echo $version.'.'.$build;?>"></script>
  <script type="text/javascript" src="../external/CryptoJS/rollups/md5.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/CryptoJS/rollups/sha256.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/phpAES/aes.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/phpAES/aes-ctr.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtor.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/jsgantt.js?version=<?php echo $version.'.'.$build;?>"></script>
  <script type="text/javascript" src="js/projeqtorWork.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtorDialog.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/liveMeeting.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/kanban.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtorFormatter.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="../external/ckeditor/ckeditor.js?version=<?php echo $version.'.'.$build;?>"></script>
  <!-- ELIOTT - LEAVE SYSTEM -->
  <script type="text/javascript" src="js/projeqtorHR.js?version=<?php echo $version.'.'.$build;?>" ></script>
<!-- ELIOTT - LEAVE SYSTEM -->
   
 <script type="text/javascript" src="../external/promise/es6-promise.min.js"></script>
 <script type="text/javascript" src="../external/promise/es6-promise.auto.min.js"></script>
 
  <script type="text/javascript">
        var dojoConfig = {
            modulePaths: {"i18n":"../../tool/i18n",
                          "i18nCustom":"../../plugin"},
            parseOnLoad: true,
            isDebug: <?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramDebugMode'));?>
        };
  </script>
  <script type="text/javascript" src="../external/dojo/dojo.js?version=<?php echo $version.'.'.$build;?>"></script>
  <script type="text/javascript" src="../external/dojo/projeqtorDojo.js?version=<?php echo $version;?>"></script>
  <?php Plugin::includeAllFiles();?>
  <script type="text/javascript">  
    var isNewGui=<?php echo (isNewGui())?'true':'false';?>;
    var customMessageExists=<?php echo(file_exists(Plugin::getDir()."/nls/$currentLocale/lang.js"))?'true':'false';?>; 
    dojo.require("dojo.data.ItemFileWriteStore");
    dojo.require("dojo.date");
    dojo.require("dojo.date.locale");
    dojo.require("dojo.dnd.Container");
    dojo.require("dojo.dnd.Manager");
    dojo.require("dojo.dnd.Source");
    dojo.require("dojo.dom-construct");
    dojo.require("dojo.dom-geometry");
    dojo.require("dojo.i18n");
    dojo.require("dojo.fx.easing");
    dojo.require("dojo.NodeList-fx");
    dojo.require("dojo.parser");
    dojo.require("dojo.query");
    dojo.require("dojo.store.DataStore");
    dojo.require("dijit.ColorPalette");
    dojo.require("dijit.Dialog"); 
    //dojo.require("dijit.Editor");
    //dojo.require("dijit._editor.plugins.AlwaysShowToolbar");
    //dojo.require("dijit._editor.plugins.FullScreen");
    //dojo.require("dijit._editor.plugins.FontChoice");
    //dojo.require("dijit._editor.plugins.Print");
    //dojo.require("dijit._editor.plugins.TextColor");
    dojo.require("dijit.Fieldset");
    dojo.require("dijit.form.Button");
    dojo.require("dijit.form.CheckBox");
    dojo.require("dojox.form.CheckedMultiSelect");
    dojo.require("dijit.form.ComboBox");
    dojo.require("dijit.form.DateTextBox");
    dojo.require("dijit.form.FilteringSelect");
    dojo.require("dijit.form.Form");
    dojo.require("dijit.form.HorizontalSlider");
    dojo.require("dijit.form.HorizontalRuleLabels");
    dojo.require("dijit.form.MultiSelect");
    dojo.require("dijit.form.NumberSpinner");
    dojo.require("dijit.form.NumberTextBox");
    dojo.require("dijit.form.RadioButton");
    dojo.require("dijit.form.Select");
    dojo.require("dijit.form.Textarea");
    dojo.require("dijit.form.TextBox");
    dojo.require("dijit.form.TimeTextBox");
    dojo.require("dijit.form.ValidationTextBox");
    dojo.require("dijit.InlineEditBox");
    dojo.require("dijit.layout.AccordionContainer");
    dojo.require("dijit.layout.BorderContainer");
    dojo.require("dijit.layout.ContentPane");
    dojo.require("dijit.layout.TabContainer");
    dojo.require("dijit.Menu"); 
    dojo.require("dijit.MenuBar"); 
    dojo.require("dijit.MenuBarItem");
    dojo.require("dijit.PopupMenuBarItem");
    dojo.require("dijit.ProgressBar");
    dojo.require("dijit.TitlePane");
    dojo.require("dijit.Toolbar");
    dojo.require("dijit.Tooltip");
    dojo.require("dijit.Tree"); 
    dojo.require("dojox.calendar.Calendar");
    dojo.require("dojox.form.FileInput");
    dojo.require("dojox.form.Uploader");
    dojo.require("dojox.form.uploader.FileList");
    dojo.require("dojox.fx.scroll");
    dojo.require("dojox.fx");
    dojo.require("dojox.grid.DataGrid");
    dojo.require("dojox.mobile.parser");
    dojo.require("dojox.mobile.Switch");
    dojo.require("dojox.mobile.SwapView");
    dojo.require("dojox.mobile.TabBar");
    dojo.require("dojox.mobile.PageIndicator");
    dojo.require("dojox.image.Lightbox");
    dojo.subscribe("/dnd/drop", function(source, nodes, copy, target){
      if(target.id == null){
      //gautier #translationApplication
        //if (target.id == null) we are in dgrid DROP , nothing to do.
  	    if(target.parent.id=='menuBarDndSource1' || target.parent.id=='menuBarDndSource2' || target.parent.id=='menuBarDndSource3' || target.parent.id=='menuBarDndSource4' || target.parent.id=='menuBarDndSource5'){
      	  setTimeout('moveMenuBarItem("'+ source.id +'", "'+ target.parent.id +'")',100); 
        }
      }else if (target.id.indexOf('dialogRow')!=-1 && source.id!=target.id){
        var idRow=nodes[0].id.split('itemRow')[1].split('-')[0];
        var typeRow=nodes[0].id.split('-')[1];
        var newStatut=target.id.split('dialogRow')[1];
        var oldStatut=source.id.split('dialogRow')[1];
        sendChangeKanBan(idRow,typeRow,newStatut,target,oldStatut);
      } else if (target.id=='subscriptionAvailable' || target.id=='subscriptionSubscribed') {
        if (source.id==target.id) return;
        for (i=0;i<nodes.length;i++) {
          var item=nodes[i];
          var mode=(target.id=='subscriptionAvailable')?'off':'on';
          changeSubscriptionFromDialog(mode,'other',item.getAttribute('objectclass'),item.getAttribute('objectid'),item.getAttribute('userid'),null,item.getAttribute('currentuserid'))
        }
      }else if (source.id!=target.id) {
    	  if( target.id=='menuBarDndSource' || target.id=='menuBarDndSource1' || target.id=='menuBarDndSource2' || target.id=='menuBarDndSource3' || target.id=='menuBarDndSource4' || target.id=='menuBarDndSource5'){
        	  setTimeout('moveMenuBarItem("'+ source.id +'", "'+ target.id +'")',100); 
          }else if(target.id=='removeMenuDiv'){
        	  setTimeout('removeMenuBarItem("'+ target.id +'")',100);
    	  }else{
        	  return;
          }
      } else if (nodes.length>0 && nodes[0] && target && target.current) {
        var moveTasks=false;
        var arrayTasks=new Array();
        dojo.forEach(nodes, function(selectedItem) {
           var idFrom = selectedItem.id;
           var idTo = target.current.id;
           if (target.id=='dndSourceTable') {
             moveTasks=idTo;
             arrayTasks.push(idFrom);
           } else  if (target.id=='dndPlanningColumnSelector') {
          	 setTimeout('movePlanningColumn("' + idFrom + '", "' + idTo + '")',100);
           } else  if (target.id=='dndListColumnSelector') {
             setTimeout('moveListColumn("' + idFrom + '", "' + idTo + '")',100);
           } else if (target.id=='dndTodayParameters') {
             setTimeout('reorderTodayItems()',100);  
           } else if (target.id=='dndFavoriteReports') {
          	 setTimeout('reorderFavoriteReportItems()',100);  
           } else if (target.id=='dndListFilterSelector') {
             setTimeout('moveFilterListColumn()',100); 
           } else if( target.id=='dndListFilterSelector2') {
             setTimeout('moveFilterListColumn2()',100); 
           } else if( target.id=='dndHierarchicalBudgetList') {
             setTimeout('moveBudgetFromHierarchicalView("' + idFrom + '", "' + idTo + '")',100); 
           } else if( target.id=='menuBarDndSource' || target.id=='menuBarDndSource1' || target.id=='menuBarDndSource2' || target.id=='menuBarDndSource3' || target.id=='menuBarDndSource4' || target.id=='menuBarDndSource5') {
             setTimeout('moveMenuBarItem("'+ source.id +'", "'+ target.id +'")',100); 
           }
        });
        if (moveTasks) {
        //setTimeout('moveTask("' + idFrom + '", "' + idTo + '")',100);
          var execMove=setTimeout(function() { moveTask(arrayTasks, moveTasks); },20);
        }
      }else if(source.id == target.id){
    	  if(source.id == 'menuBarDndSource'){
    		  dojo.byId('anotherBarContainer').style.display = 'none';
    		  dojo.byId('menuBarListDiv').setAttribute('style', 'overflow:hidden;width: 100%;height: 43px;border-left: 1px solid var(--color-dark);');
    	  }
    	  dojo.byId('removeMenuDiv').style.visibility = 'hidden';
      }
    });
    dojo.subscribe("/dnd/start", function(source, nodes, copy, target){
      if (!source || !source.id) return;
       if(source.id == 'menuBarDndSource'){
           dojo.byId('anotherBarContainer').style.display = 'block';
           dojo.byId('menuBarListDiv').setAttribute('style', 'overflow:hidden;width: 100%;height: 43px;border-radius: 5px;border-left: 1px solid var(--color-dark);');
           dojo.byId('removeMenuDiv').style.visibility = 'visible';
       } else if (source.id.substr(0,16)=='menuBarDndSource') {
    	   dojo.byId('removeMenuDiv').style.visibility = 'visible';
       }
    });
    dojo.subscribe("/dnd/cancel", function(){
    	if(dojo.byId('isEditFavorite').value == 'true'){
    		dojo.byId('isEditFavorite').value = 'false';
    	}else{
    		dojo.byId('isEditFavorite').value = 'true';
    	}
	    dojo.byId('anotherBarContainer').style.display = 'none';
	    dojo.byId('menuBarListDiv').setAttribute('style', 'overflow:hidden;width: 100%;height: 43px;border-left: 1px solid var(--color-dark);');
	    dojo.byId('removeMenuDiv').style.visibility = 'hidden';
    });

    dndMoveInProgress=false;
    dojo.subscribe("/dnd/drop/before", function(source, nodes, copy, target){
    	dndMoveInProgress=true;
      setTimeout("dndMoveInProgress=false;",50);
    });
    // Management of history
    var historyTable=new Array();
    var historyPosition=-1;    
    var fadeLoading=<?php echo getBooleanValueAsString(Parameter::getGlobalParameter('paramFadeLoadingMode'));?>;
    var refreshUpdates="YES";
    var aesLoginHash="<?php echo md5(session_id());?>";
    var printInNewWindow=<?php echo (getPrintInNewWindow())?'true':'false';?>;
    var pdfInNewWindow=<?php echo (getPrintInNewWindow('pdf'))?'true':'false';?>;
    var alertCheckTime='<?php echo Parameter::getGlobalParameter('alertCheckTime');?>';
    var scaytAutoStartup=<?php echo (Parameter::getUserParameter('scaytAutoStartup')=='NO')?'false':'true';?>;
    var offDayList='<?php echo Calendar::getOffDayList();?>';
    var workDayList='<?php echo Calendar::getWorkDayList();?>';
    var applicationName='<?php echo htmlEncode(((Parameter::getGlobalParameter('paramDbDisplayName'))?Parameter::getGlobalParameter('paramDbDisplayName'):i18n("applicationTitle")),'protectQuotes');?>';
// MTY - MULTI CALENDAR    
    var uOffDayList='<?php echo Calendar::getOffDayList(getSessionUser()->idCalendarDefinition);?>';
    var uWorkDayList='<?php echo Calendar::getWorkDayList(getSessionUser()->idCalendarDefinition);?>';
// MTY - MULTI CALENDAR    
    var defaultOffDays=new Array();
    var defaultMenu="<?php echo Parameter::getUserParameter('defaultMenu'); ?>";
    <?php 
// MTY - GENERIC DAY OFF
    $defaultOffDays = array();
    if (Parameter::getGlobalParameter('OpenDaySunday')=='offDays') {
        echo "defaultOffDays[0]=0;";
        $defaultOffDays[0] = 1;
    }    
    if (Parameter::getGlobalParameter('OpenDayMonday')=='offDays') {
        echo "defaultOffDays[1]=1;"; 
        $defaultOffDays[1] = 1;
    }    
    if (Parameter::getGlobalParameter('OpenDayTuesday')=='offDays') {
        echo "defaultOffDays[2]=2;"; 
        $defaultOffDays[2] = 1;
    }    
    if (Parameter::getGlobalParameter('OpenDayWednesday')=='offDays') {
        echo "defaultOffDays[3]=3;"; 
        $defaultOffDays[3] = 1;
    }    
    if (Parameter::getGlobalParameter('OpenDayThursday')=='offDays') {
        echo "defaultOffDays[4]=4;";
        $defaultOffDays[4] = 1;
    }    
    if (Parameter::getGlobalParameter('OpenDayFriday')=='offDays') {
        echo "defaultOffDays[5]=5;"; 
        $defaultOffDays[5] = 1;
    }    
    if (Parameter::getGlobalParameter('OpenDaySaturday')=='offDays') {
        echo "defaultOffDays[6]=6;";
        $defaultOffDays[6] = 1;        
    }    
    $calDef = new CalendarDefinition(getSessionUser()->idCalendarDefinition);
    for ($i=0;$i<=6;$i++) {
        $dayOfWeek = "dayOfWeek".$i;
        if ($calDef->$dayOfWeek==1 and !array_key_exists($i, $defaultOffDays)) {
            echo "defaultOffDays[$i]=$i;";
        }
    }
// MTY - GENERIC DAY OFF
    ?>
// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM
    var paramNotificationSystemActiv=<?php echo (Module::isModuleActive('moduleNotification'))?'true':'false'; ?>;
    var totalUnreadNotificationsCount=0;
// END - ADD BY TABARY - NOTIFICATION SYSTEM
// MTY - LEAVE SYSTEM
    var isLeaveSystemActiv = <?php echo (isLeavesSystemActiv()?1:0); ?>;
// MTY - LEAVE SYSTEM
    var draftSeparator='<?php echo Parameter::getGlobalParameter('draftSeparator');?>';
    var paramCurrency='<?php echo $currency;?>';
    var paramCurrencyPosition='<?php echo $currencyPosition;?>';
    var paramWorkUnit='<?php echo Parameter::getGlobalParameter('workUnit');?>';
    if (! paramWorkUnit) paramWorkUnit='days';
    var paramImputationUnit='<?php echo Parameter::getGlobalParameter('imputationUnit');?>';
    if (! paramImputationUnit) paramImputationUnit='days';
    var paramHoursPerDay='<?php echo Parameter::getGlobalParameter('dayTime');?>';
    if (! paramHoursPerDay) paramHoursPerDay=8;
    var paramConfirmQuit="<?php echo Parameter::getUserParameter("paramConfirmQuit")?>";
    var browserLocaleDateFormat="<?php echo Parameter::getUserParameter('browserLocaleDateFormat');?>";
    var browserLocaleDateFormatJs=browserLocaleDateFormat.replace(/D/g,'d').replace(/Y/g,'y');
    var browserLocaleTimeFormat="<?php echo Parameter::getUserParameter('browserLocaleTimeFormat');?>";
    <?php $fmt=new NumberFormatter52( $browserLocale, NumberFormatter52::DECIMAL );?>
    var browserLocaleDecimalSeparator="<?php echo $fmt->decimalSeparator?>";
    var aesKeyLength=<?php echo Parameter::getGlobalParameter('aesKeyLength');?>;
    dojo.addOnLoad(function(){
      currentLocale="<?php echo $currentLocale;?>";
      // Set color depending on theme for New Gui
      if (isNewGui) {
        changeTheme('<?php echo getTheme();?>');
        setColorTheming('<?php echo '#'.Parameter::getUserParameter('newGuiThemeColor');?>','<?php echo '#'.Parameter::getUserParameter('newGuiThemeColorBis');?>');
        (function() {
          var menuEl = dojo.byId('ml-menu'),
                       mlmenu = new MLMenu(menuEl, {
                         initialBreadcrumb : i18n('homePage'), // initial breadcrumb text
                         backCtrl : false, // show back button
                       });
        })();
        new menuLeft( dojo.byId( 'mainDiv' ) );
      }
      
      //setColorTheming('blue');
      // FIX IE11 not recognized as IE
      if( !dojo.isIE ) {
        var userAgent = navigator.userAgent.toLowerCase();
        var IEReg = /(msie\s|trident.*rv:)([\w.]+)/;
        var match = IEReg.exec(userAgent);
        if( match )
          dojo.isIE = match[2] - 0;
        else
          dojo.isIE = undefined;
      }

      //Disable the key events Ctrl and Shift
      dojo.extend( dojo.dnd.Source, { copyState: function( keyPressed, self ){ 
          return false; }}
      );
      <?php 
      if (sessionValueExists('project')) {
        $proj=getSessionValue('project');
        if(strpos($proj, ",") != null){
        	$proj="*";
        }
      } else {
        $proj="*";
      }
      echo "currentSelectedProject='$proj';";
      if (sessionValueExists('hideMenu')) {
        if (getSessionValue('hideMenu')!='NO') {
          echo "menuHidden=true;";
          echo "menuShowMode='" . getSessionValue('hideMenu') . "';";
        }
      }
      if (Parameter::getUserParameter('paramScreen')=='switch') {
        echo "switchedMode=true;";
        echo "switchListMode='CLICK';";
      }    
      ?>
      dijit.Tooltip.defaultPosition=["below", "right"];
      addMessage("<?php echo htmlEncode(i18n('welcomeMessage').' '.((getSessionUser()->resourceName)?getSessionUser()->resourceName:getSessionUser()->name),'qotes');?>");
      //dojo.byId('body').className='<?php echo getTheme();?>';
      saveResolutionToSession();
      saveBrowserLocaleToSession();
      aboutMessage="<?php echo $aboutMessage;?>";
      aboutMessage+='<br/><b>'+i18n('externalLibrary')+'</b><br/>';
      aboutMessage+='&nbsp;&nbsp;.&nbsp;Dojo : '+dojo.version.major+"."+dojo.version.minor+"."+dojo.version.patch+'<br/>';
      aboutMessage+='&nbsp;&nbsp;.&nbsp;html2pdf : <?php use Spipu\Html2Pdf\Html2Pdf;require_once '../external/html2pdf/vendor/autoload.php';$html2pdf = new Html2Pdf();echo $html2pdf->getVersion();?>'+'<br/>';
      aboutMessage+='&nbsp;&nbsp;.&nbsp;CK Editor : '+CKEDITOR.version+'<br/>';
      aboutMessage+='&nbsp;&nbsp;.&nbsp;pChart2 : <?php include_once('../external/pChart2/getVersion.php');echo pChartGetVersion();?>'+'<br/>';
      aboutMessage+='&nbsp;&nbsp;.&nbsp;phpMailer : <?php $vers=file_get_contents('../external/PHPMailer/VERSION');echo $vers;?>'+'<br/>';
      aboutMessage+='&nbsp;&nbsp;.&nbsp;html2canvas : <?php include_once('../external/html2canvas/getVersion.php');echo html2canvasGetVersion();?>'+'<br/>';    
      aboutMessage+='&nbsp;&nbsp;.&nbsp;pdfMake : <?php include_once('../external/pdfmake/getVersion.php');echo pdfmakeGetVersion();?>'+'<br/>';
      aboutMessage+='&nbsp;&nbsp;&nbsp;CryptoJS 3.1.2 '+'<br/>';      
      aboutMessage+='&nbsp;&nbsp;&nbsp;phpAES '+'<br/>';
      aboutMessage+='&nbsp;&nbsp;&nbsp;TinyButStrong 3.10.1'+'<br/>';
      aboutMessage+='&nbsp;&nbsp;&nbsp;XLSReader'+'<br/>';
      aboutMessage+='<br/>';
      // Relaunch Cron (if stopped, any connexion will restart it)
      adminCronRelaunch();
      if (dojo.isIE) {
        document.onhelp = function() { return (false); };
        window.onhelp = function() { return (false); };
      }
      var onKeyDownFunc = function(event) {
        if (event.keyCode == 83 && (navigator.platform.match("Mac") ? event.metaKey : event.ctrlKey) && ! event.altKey && event.target.id!="noteNoteStream") { // CTRL + S (save)
          event.preventDefault();
          if (dojo.isFF) stopDef();
          globalSave();
        } else if (event.keyCode == 112) { // F1 (show help)
          event.preventDefault();
          if (dojo.isFF) stopDef();
          showHelp();
        }else if(event.keyCode==27){ // ESCAPE (to exit full screen mode of CK Editor)
          if(editorInFullScreen() && whichFullScreen!=-1){
            editorArray[whichFullScreen].execCommand('maximize');
          }
        } if (event.target.id=="noteNoteStream") {
          saveNoteStream(event);
        } if (event.target.id=="noteStreamKanban") {
          saveNoteStreamKanban(event);
        }
      };
      //if (dojo.isIE && dojo.isIE<=8) { // compatibility with IE8 removed in V6.0
      //  dojo.connect(document, "onkeypress", this, onKeyPressFunc);
      //} else {
        dojo.connect(document, "onkeydown", this, onKeyDownFunc);
      //} 
        var fullScr=function(evt){
          var fullscreenElement = document.fullscreenElement || document.mozFullScreenElement ||
          document.webkitFullscreenElement || document.msFullscreenElement;
          if(!fullscreenElement){
            setTimeout('cancelBothFullScreen();',10);
          }
        };
        document.addEventListener("fullscreenchange",fullScr);
        document.addEventListener("webkitfullscreenchange",fullScr);
        document.addEventListener("mozfullscreenchange",fullScr);
        document.addEventListener("msfullscreenchange",fullScr);
      <?php 
      $firstPage="welcome.php";
      if (securityCheckDisplayMenu(1) ) {
      	$firstPage="today.php";
      }
      $paramFirstPage=Parameter::getUserParameter('startPage');
      if ($paramFirstPage) {
        $menuName=Menu::getMenuNameFromPage($paramFirstPage);
        if (securityCheckDisplayMenu(null,$menuName) or $paramFirstPage=='welcome.php') {
          $firstPage=$paramFirstPage;
        }
      }
      if (array_key_exists("directAccessPage",$_REQUEST)) {
 // MTY - LEAVE SYSTEM
        // After reload when changing Resource and :
        //      - leavesSystemActif = true 
        //      - ressource.isemployee is changed to false
        //      - ressource.id = user.id
        if ($_REQUEST["directAccessPage"]=== "objectMain.php") {
            if (array_key_exists("p1name", $_REQUEST)) {
                if ($_REQUEST['p1name']=='Resource') {
                    $class='Resource';
                    $id=$_REQUEST['p1value'];
                    $directObj=new $class($id);
                    $rights=$user->getAccessControlRights();
                    echo "dojo.byId('directAccessPage').value='';";
                    echo "dojo.byId('menuActualStatus').value='';";
                    echo 'gotoElement("' . $class . '","' . $id . '");';
                    $firstPage="";
                }else{
                  //gautier #3287
                  if(RequestHandler::getValue('changeCurrentLocale')=="changeCurrentLocale"){
                    $class=RequestHandler::getClass('p1name');
                    $id=RequestHandler::getId('p1value');
                    $directObj=new $class($id);
                    $rights=$user->getAccessControlRights();
                    echo "dojo.byId('directAccessPage').value='';";
                    echo "dojo.byId('menuActualStatus').value='';";
                    echo "waitingForReply=false;";
                    //echo 'gotoElement("' . $class . '","' . $id . '");';
                    echo 'setTimeout(\'gotoElement("' . $class . '","' . $id . '");\',100);';
                    $firstPage="";
                  }
                }
            }
        } else {
// MTY - LEAVE SYSTEM          
        securityCheckRequest();
        $firstPage=$_REQUEST['directAccessPage'];     
        for ($i=1;$i<=9;$i++) {
          $pName='p'.$i.'name';
          $pValue='p'.$i.'value';
          if (array_key_exists($pName,$_REQUEST) and array_key_exists($pValue,$_REQUEST) ) {
            $firstPage.=($i==1)?'?':'&';
            $firstPage.=htmlentities($_REQUEST[$pName])."=".htmlentities($_REQUEST[$pValue]);
          } else {
            break;
          }
        }
        echo "dojo.byId('directAccessPage').value='';";
        echo "dojo.byId('menuActualStatus').value='';";
}        
      } else if (array_key_exists('objectClass', $_REQUEST) and array_key_exists('objectId', $_REQUEST) ) {
        $class=$_REQUEST['objectClass'];
        if (class_exists($class)) {
		      Security::checkValidClass($class);
          $id=$_REQUEST['objectId'];
          Security::checkValidId($id);
          $directObj=new $class($id);
        } else {
          $directObj=null;
          $id=null;
          $class="Today";
        }
        $rights=$user->getAccessControlRights();
        if ($class=='Ticket' and (securityGetAccessRightYesNo('menuTicket', 'read', $directObj)=='NO' or securityCheckDisplayMenu(null,'Ticket')==false ) )  {
          $class='TicketSimple';
        } else if ($class=='TicketSimple' and securityGetAccessRightYesNo('menuTicket', 'read', $directObj)=='YES' and securityCheckDisplayMenu(null,'Ticket')==true) {
          $class='Ticket';
        }
        if (array_key_exists('directAccess', $_REQUEST)) {
        	echo "noDisconnect=true;";
        	if (sessionValueExists('directAccessIndex')) {
        		$directAccessIndex=getSessionValue('directAccessIndex');
        	}	else { 
        	  $directAccessIndex=array();
          }
          $index=count($directAccessIndex)+1;
          if ($directObj) $directAccessIndex[$index]=$directObj;
          else $directAccessIndex[$index]='';
          setSessionValue('directAccessIndex', $directAccessIndex);
        	echo "directAccessIndex=$index;";
        }
        if ($class=="Today") {
          $firstPage="welcome.php";
        } else { 
          echo 'var delay=(dojo.isFF)?1000:10;';
          echo 'setTimeout(\'gotoElement("' . $class . '","' . $id . '");\',delay);';
          $firstPage="";
        }
      }
      if(!isNewGui()){
        $hideMenu=false;
        if (Parameter::getUserParameter('hideMenu') and Parameter::getUserParameter('hideMenu')!='NO' and ! getSessionValue('showModule')){
          echo 'hideShowMenu(true,true);';
          $hideMenu=true;
        }
      }
      echo "menuDivSize='".Parameter::getUserParameter('contentPaneLeftDivWidth')."';";
      // Module
      if (getSessionValue('showModule')) {
        setSessionValue('showModule', $firstPage); 
        $firstPage=null;
      } else if ($firstPage) {
      ?>
        setTimeout('loadContent("<?php echo $firstPage;?>","centerDiv");',200);
      <?php 
      }
      ?>
      dojo.byId("loadingDiv").style.visibility="hidden";
      dojo.byId("loadingDiv").style.display="none";
      dojo.byId("mainDiv").style.visibility="visible"; 
      setTimeout('checkAlert();',5000); //first check at 5 seco 

// MTY - LEAVE SYSTEM
      <?php
        if (isLeavesSystemActiv() and (getSessionUser()->isEmployee or isLeavesAdmin(getSessionUser()->id))) {
      ?>
            checkLeavesEarned(<?php echo getSessionUser()->id?>);
      <?php
        }
      ?>
// MTY - LEAVE SYSTEM
      <?php if ($firstPage=="welcome.php") {?>
          //setTimeout("runWelcomeAnimation();",2000);
      <?php } ?>
      <?php // check for ongoing work on Ticket 
      if (getSessionUser()->id) {
	      $crit=array('ongoing'=>'1','idUser'=>getSessionUser()->id);
	      $we=SqlElement::getSingleSqlElementFromCriteria('WorkElement', $crit);
	      if ($we and $we->id) {
	      	$start=$we->ongoingStartDateTime;
	      	//echo "startStopWork('start', '$we->refType', $we->refRefId, $start);";
	      }
      }
      ?>
      showHideMoveButtons();
    }); // End of dojo.addOnload 
    var ganttPlanningScale="<?php echo Parameter::getUserParameter('planningScale');?>";
    if (! ganttPlanningScale) ganttPlanningScale='day';
    var cronSleepTime=<?php echo Cron::getSleepTime();?>;
    var canCreateArray=new Array();
    var dependableArray=new Array();
    var linkableArray=new Array();
    var originableArray=new Array();
    var copyableArray=new Array();
    var indicatorableArray=new Array();
    var mailableArray=new Array();
    var textableArray=new Array();
    var situationableArray=new Array();
    var checklistableArray=new Array();
    var planningColumnOrder=new Array();
    <?php
      echo "\n";
      $list=SqlList::getListNotTranslated('Dependable');
      foreach ($list as $id=>$name) {
      	$right=securityGetAccessRightYesNo('menu' . $name,'create');
      	echo "canCreateArray['" . $name . "']='" . $right . "';";
      	echo "dependableArray['" . $id . "']='" . $name . "';";
      }
      echo "\n";
      $list=SqlList::getListNotTranslated('Linkable');
      foreach ($list as $id=>$name) {
        $right=securityGetAccessRightYesNo('menu' . $name,'create');
        echo "canCreateArray['" . $name . "']='" . $right . "';";
        echo "linkableArray['" . $id . "']='" . $name . "';";
      }
      echo "\n";
      $list=SqlList::getListNotTranslated('Originable');
      foreach ($list as $id=>$name) {
        $right=securityGetAccessRightYesNo('menu' . $name,'create');
        echo "canCreateArray['" . $name . "']='" . $right . "';";
        echo "originableArray['" . $id . "']='" . $name . "';";
      }
      echo "\n";
      $list=SqlList::getListNotTranslated('Copyable');
      foreach ($list as $id=>$name) {
        echo "copyableArray['" . $id . "']='" . $name . "';";
      }
      echo "\n";
      $list=SqlList::getListNotTranslated('Indicatorable');
      foreach ($list as $id=>$name) {
        echo "indicatorableArray['" . $id . "']='" . $name . "';";
      }
      echo "\n";
      $list=SqlList::getListNotTranslated('Mailable');
      foreach ($list as $id=>$name) {
        echo "mailableArray['" . $id . "']='" . $name . "';";
      }
      echo "\n";
      $list=SqlList::getListNotTranslated('Textable');
      foreach ($list as $id=>$name) {
        echo "textableArray['" . $id . "']='" . $name . "';";
      }
      echo "\n";
      $list=SqlList::getListNotTranslated('Checklistable');
      foreach ($list as $id=>$name) {
      	echo "checklistableArray['" . $id . "']='" . $name . "';";
      }
      echo "\n";
      $list=SqlList::getListNotTranslated('Situationable');
      foreach ($list as $id=>$name) {
      	echo "situationableArray['" . $id . "']='" . $name . "';";
      }
      echo "\n";
      // Retrieve order and visibility info for Planning Columns
      $list=Parameter::getPlanningColumnOrder();
      foreach ($list as $order=>$name) {
        echo "planningColumnOrder[" . ($order-1) . "]='" . $name . "';";
        echo "setPlanningFieldShow('$name',true);";
        echo "setPlanningFieldOrder('$name',$order);\n";
      } 
      $list=Parameter::getPlanningColumnDescription();
      foreach ($list as $name=>$desc) {
        echo "setPlanningFieldWidth('$name',".$desc['width'].");";
      }
      echo "\n";
      // Retrieve translation files for each installed plugin
      Plugin::getTranslationJsArrayForPlugins('i18nPluginArray');
      ?>
    //window.onbeforeunload = function (evt){ return beforequit();};
  </script>
</head>
<body id="body" class="nonMobile tundra <?php echo getTheme();?>" <?php if(isNewGui()) echo 'style="background-color:'.$background.';"'; ?> onBeforeUnload="return beforequit();" onUnload="quit();">
<div id="centerThumb80" style="display:none;z-index:999999;position:absolute;top:10px;left:10px;height:80px;width:80px;"></div>
<div id="loadingDiv" class="<?php echo getTheme();  echo (isNewGui())?'loginFrameNewGui':'loginFrame' ;?>" 
 style="position:absolute; visibility: visible; display:block; width:100%; height:100%; margin:0; padding:0; border:0">
 <?php if (1 and isNewGui()) echo '<div style="position:absolute;margin-top:-50%;margin-left:-0%;width:250%;height:250%;opacity:10%;z-index:-2;" class="loginBackgroundNewGui"></div>';?>
  <?php if (isNewGui()) echo '<div style="position:absolute;width:100%;height:100%;opacity:60%;z-index:-1;" class="loginBackgroundNewGui"></div>';?>
  <?php if (0 and isNewGui()) echo '<div style="position:absolute;width:100%;height:100%;opacity:5%;position:-20px;" class="loginBackgroundNewGui"></div>';?>   
  <table align="center" width="100%" height="100%" class="<?php echo (isNewGui())?'':'loginBackground';?>">
    <tr height="100%">
      <td width="100%" align="center">
        <div class="background  <?php  echo (isNewGui())?'loginFrameNewGui':'loginFrame' ;?>" <?php echo (isNewGui())?'style="background-color:white;"':'';?>>
        <table align="center" >
		    <?php if(isNewGui()){?>
			    <tr style="height:42px;" >
			     <td align="center" style="position:relative;height: 1%;" valign="center">
			       <div style="position:relative;height:75px;">
			         <div class="divLoginIconDrawing" style="position:absolute;background-color:#<?php echo $firstColor;?>";>
			           	<div class="divLoginIconBig"></div>		         
			         </div>
			       </div>
			     </td>
			    </tr>
	    <?php }?>
          <tr style="height:10px;">
            <td align="left" style="position:relative;height: 100%;" valign="top">
              <div style="position:relative; width: 400px; height: 54px;">
    	          <div style="overflow:visible;position:absolute;width: 480px; height: 250px;top:15px;text-align: center">
    	           <div id="waitLogin" style="position:absolute;top:50%"></div>  
	    		        <img style="max-height:60px" src="<?php 
	    		          if (file_exists("../logo.gif")) echo '../logo.gif';
	    		          else if (file_exists("../logo.jpg")) echo '../logo.jpg';
	    		          else if (file_exists("../logo.png")) echo '../logo.png';
	    		          else echo 'img/titleSmall.png';?>" />
    	          </div>
  	            <div style="width: 470px; height:130px;position:absolute;top:160px;overflow:hidden;text-align:center;">
                  Loading ...    
                </div>
              </div>
            </td>
          </tr>
          <tr style="height:100%" height="100%">
            <td style="height:99%" align="left" valign="middle">
              <div  id="" style="width: 470px; height:210px;overflow:hidden">
              </div>
            </td>
          </tr>
        </table>
        </div>
      </td>
    </tr>
  </table>
</div>
<div id="mainDiv" style="visibility: hidden;" class="mainDiv">
  <div id="wait" >
  </div>
  <div id="errorPopup" data-dojo-type="dijit.Dialog" title=<?php echo strtoupper(i18n("ERROR")) ?>></div>
  <div dojoType="dijit.layout.ContentPane" id="resultPopup" style ="pointer-events: none;vertical-align:middle;min-height:20px;max-height:500px;height:auto" onclick="dojo.byId('resultPopup').style.display='none';dojo.byId('resultPopup').style.pointerEvents='none';"></div>
  <div id="temporaryMessage" style="display:none;z-index:999;text-align:center;position:fixed;width:500px;left:50%;top:10px;margin-left:-250px">
    <div id="temporaryMessageText" style="text-align:center;cursor:pointer;" onClick="dojo.byId('temporaryMessage').style.display='none';"></div>
  </div>
  <div dojoType="dijit/ProgressBar" id="downloadProgress" data-dojo-props="maximum:1">
  </div>
  <?php
  // Module
  if (getSessionValue('showModule')) {
    $showModuleScreen=true;
    include "../view/moduleView.php";
  }
  ?>
  	<?php   
  //Gautier RGPD
  $crit = array ('idUser'=>$user->id,'accepted'=>'0');
  $listMessageLegalFollowup=SqlList::getListWithCrit ('MessageLegalFollowup',$crit,'idMessageLegal');
  if($listMessageLegalFollowup){
    $nbListMess = count($listMessageLegalFollowup);
    $cptMess = 0;
    foreach ($listMessageLegalFollowup as $idFollowup=>$idMessage){
      $messLegal = new MessageLegal($idMessage);
      if((date('Y-m-d H:i:s') > $messLegal->endDate AND $messLegal->endDate )  OR (date('Y-m-d H:i:s') < $messLegal->startDate AND $messLegal->startDate ) OR $messLegal->idle){
        unset($listMessageLegalFollowup[$idFollowup]);
      }
    }
   }
   if($listMessageLegalFollowup){
    ?>
   <div id="dialogMessageLegal" style="width:100%; visibility:visible; display:inline-block;height:30%;"> 
     <div id="messageLegallArrow" style="display:block; float:right; margin-top:15px; width:5%; height:100%; margin-top:5px;">
      <div class="iconArrowMessageLegal" ><span style="color:white; width:18px; height:90%;writing-mode:vertical-rl;"><?php echo i18n("readToHide");?></span></div>
     </div>
      <?php Sql::beginTransaction();
            foreach ($listMessageLegalFollowup as $idFollowup=>$idMessage){
              $messFollow = new MessageLegalFollowup($idFollowup);
              $messLegal = new MessageLegal($idMessage);
              $cptMess++;
              if(!$messFollow->firstViewDate){
                $messFollow->firstViewDate = date('Y-m-d H:i:s');
              }
              $messFollow->lastViewDate = date('Y-m-d H:i:s');
              $messFollow->save();
              $val=$messLegal->description; 
              if($cptMess < $nbListMess){ ?>
                 <div id="messageLegall<?php echo $messFollow->id;?>" style="display:none;  width:95%; height:100%; overflow-y:auto;"> 
              <?php }else{ ?> 
                 <div id="messageLegall<?php echo $messFollow->id;?>" style="display:block;  width:95%; height:100%; overflow-y:auto;"> 
              <?php }?>
                  <div id="messageLegal<?php echo $messFollow->id;?>" style="font-size:12pt; margin-top:15px; min-height:200px; margin:15px 40px 5px 40px;">  
                    <?php echo $val;?>
                  </div>
                  <div style="width:97%;  bottom:5px; text-align:right;">
                      <?php if($cptMess != 1){?>
                  	   <span style="font-size:12pt;cursor:pointer; text-decoration:underline;" id="plusTard<?php echo $messFollow->id;?>" onclick="dojo.byId('messageLegall<?php echo $oldValue;?>').style.display='block';dojo.byId('messageLegall<?php echo $messFollow->id;?>').style.display='none'";>
                      <?php }else{?>   
                        <span style="font-size:12pt;cursor:pointer; text-decoration:underline;" id="buttonLater<?php echo $messFollow->id;?>" onclick="dojo.byId('dialogMessageLegal').style.visibility='hidden'";>
                      <?php } 
                      echo i18n("buttonLater");?></span>
                     &nbsp;&nbsp;&nbsp;
                     <?php 
                        if($messLegal->name != 'newGui'){
                           if($cptMess != 1){?>
                  	   <button style="font-size:12pt;position:relative;top:-5px;" dojoType="dijit.form.Button" id="markOK<?php echo $messFollow->id;?>" onclick="setReadMessageLegalFollowup(<?php echo $messFollow->id;?>);dojo.byId('messageLegall<?php echo $oldValue;?>').style.display='block';dojo.byId('messageLegall<?php echo $messFollow->id;?>').style.display='none'";>
                     <?php }else{?>
                       <button style="font-size:12pt;position:relative;top:-5px;" dojoType="dijit.form.Button" id="markOK<?php echo $messFollow->id;?>" onclick="setReadMessageLegalFollowup(<?php echo $messFollow->id;?>);dojo.byId('dialogMessageLegal').style.visibility='hidden'";>
                     <?php } echo i18n("buttonAgree");?>
                       </button>
                     <?php }else{?>
                        <button style="font-size:12pt;position:relative;top:-5px;" dojoType="dijit.form.Button" id="activateNewgui<?php echo $messFollow->id;?>" onclick="setNewGui(<?php echo $messFollow->id;?>, 1);dojo.byId('dialogMessageLegal').style.visibility='hidden'";>
                          <?php echo i18n("cronExecutionActivate");?>
                        </button>
                        <button style="font-size:12pt;position:relative;top:-5px;" dojoType="dijit.form.Button" id="desactiveNewgui<?php echo $messFollow->id;?>" onclick="setNewGui(<?php echo $messFollow->id;?>, 0);dojo.byId('dialogMessageLegal').style.visibility='hidden'";>
                          <?php echo i18n("cronExecutionDesactivate");?>
                        </button>
                     <?php }?>
                   </div>
                   
                 </div>
            <?php 
              $oldValue = $messFollow->id;
            }
            Sql::commitTransaction(); ?>
    </div>
 <?php  } ?>
 
 
  <?php 
    if(!isNewGui()){
     $leftWidth=Parameter::getUserParameter('contentPaneLeftDivWidth');
     $leftWidth=($leftWidth and $leftWidth>35)?$leftWidth.'px':'20%';
     if ($hideMenu){
       $leftWidth="32px";
     }
     
     //$IconSizeMenuHide = 16;
    $IconSizeMenuHide=Parameter::getUserParameter('paramIconSize');
    if (!$IconSizeMenuHide) $IconSizeMenuHide=22;
    $IconSizeMenuHide2 = $IconSizeMenuHide+5;
   ?>
  <div id="menuBarShow" class="dijitAccordionTitle2 reportTableColumnHeader2 largeReportHeader2"  style="position:absolute;left:0px; top:81px; bottom:0px; width:<?php echo $IconSizeMenuHide2;?>px;">
    <?php include "menuHideMenu.php"; ?> 
    
   <div id="hideMenuBarShowButton" style="cursor:pointer;position:absolute; right:-22px; bottom:2px;z-index:949;display:<?php echo (isset($showModuleScreen))?"none":"block";?>;">
		  <a onClick="hideMenuBarShowMode();" id="buttonSwitchedMenuBarShow" title="" >
		    <span style='top:0px;display:inline-block;width:22px;height:22px;'>
		      <div class='iconHideStream22 iconHideStream iconSize22' style='' >&nbsp;</div>
		    </span>
		  </a>
		</div>
  </div> 
  
  <div id="hideMenuBarShowButton2" style="cursor:pointer;position:absolute;display:<?php echo (isset($showModuleScreen))?"none":"block";?>;left:<?php echo $IconSizeMenuHide2 ?>; bottom:2px;z-index:949">
	<?php if (! isset($showModuleScreen)) {?>
	  <a onClick="hideMenuBarShowMode();" id="buttonSwitchedMenuBarShow" title="" >
	    <span style='top:0px;display:inline-block;width:22px;height:22px;'>
	      <div class='iconHideStream22 iconHideStream iconSize22' style='' >&nbsp;</div>
	    </span>
	  </a>
	<?php }?>  
	</div>
  <?php }?>
  <div id="globalContainer" class="container" dojoType="dijit.layout.BorderContainer" liveSplitters="false"> 
    <?php 
    //$iconSize=Parameter::getUserParameter('paramTopIconSize');
    //$showMenuBar=Parameter::getUserParameter('paramShowMenuBar');
    //$showMenuBar='NO';
    $iconSize=(isNewGui())?22:32;
    $showMenuBar='YES';
    //if (! $iconSize or $showMenuBar=='NO') $iconSize=16;
    $iconSize+=9;?>
    
    <div id="toolBarDiv" style="height:30px" dojoType="dijit.layout.ContentPane" region="top"  >
      <?php include "menuBar.php";?>
    </div>
    <?php if(isNewGui()){
      $isMenuLeftOpen=Parameter::getUserParameter('isMenuLeftOpen');
      if($isMenuLeftOpen=='')$isMenuLeftOpen='true';
      ?>
    <div id="menuTop" class="menuTop">
      <div id="globalTopCenterDiv" class="container" region="center" dojoType="dijit.layout.BorderContainer" liveSplitters="false">
      <input id="selectedScreen" value="" hidden >
      <input id="isMenuLeftOpen" value="<?php echo $isMenuLeftOpen?>" hidden >
      <div id="right-pane" class="right-pane">
    <?php } if (!isNewGui()) {?>
     <div id="statusBarDiv" dojoType="dijit.layout.ContentPane" region="top" style="height:48px;position:absolute; top:30px;">
      <table width="100%"><tr>
      
       <td width="220px" id="menuBarLeft" >
      
        <div style="overflow:hidden;position: absolute; left:2px; top: 8px;width:205px; background: transparent; color: #FFFFFF !important; border:<?php echo (isNewGui())?'0':'1';?>px solid #FFF;vertical-align:middle;" 
        onChange="menuFilter(this.value);" id="menuSelector" id="menuSelector"
        onMouseEnter="showMenuList();" onMouseLeave="hideMenuList(300);"
        dojoType="dijit.form.Select" class="input filterField rounded menuSelect" 
        ><?php foreach ($allMenuClass as $cl=>$clVal) {
          $selected=($defaultMenu==$cl)?' selected=selected ':'';
          echo '<option value="'.$cl.'" '.$selected.' style="color:#fff !important;">';
          echo '<div style="z-index:9999;height:25px;vertical-align:middle;top:2px;width:190px;" value="'.$cl.'" '.$selected.' class="menuSelectList" onMouseOver="clearTimeout(closeMenuListTimeout);" onMouseLeave="hideMenuList(200,\''.$cl.'\');">';
          echo '  <div style="z-index:9;position:absolute;height:23px;width:25px;left:1px;background-color:#ffffff;border-radius:5px;opacity: 0.5;">&nbsp;</div>';
          echo '  <span style="z-index:10;position:absolute;height:22px;left:2px;" class="icon'.ucfirst($cl).'22 icon'.ucfirst($cl).' iconSize22">&nbsp;</span>';
          echo '  <span style="z-index:11;position:absolute;left:30px;top:9px;">'. i18n('menu'.ucfirst($clVal)).'</span>';
          echo '</div>';
          echo '</option>';
      }?></div>
      
      <?php if ($showMenuBar!='NO') {?>    
      
        <button id="menuBarMoveLeft" dojoType="dijit.form.Button" showlabel="false"
         title="<?php echo i18n('menuBarMoveLeft');?>" class="buttonMove"
         iconClass="leftBarIcon" style="position:relative; left:232px; width: 14px;top:-2px;height:48px;margin:0;vertical-align:middle">
           <script type="dojo/method" event="onMouseDown">         
           menuBarMove=true;
           moveMenuBar('left');
         </script>
           <script type="dojo/method" event="onMouseUp">
           moveMenuBarStop();
         </script>
           <script type="dojo/method" event="onClick">
           moveMenuBarStop();
         </script>
        </button>    
      </td>
     
     <td width="85%">       
          <div id="menuBarVisibleDiv" style="height:<?php echo $iconSize+9;?>px;width:<?php echo ($cptAllMenu*56);?>px; position: absolute; top: 0px; left:248px; z-index:0">
          <div style="width: 100%; height:48px; position: absolute; left: 0px; top:1px; overflow:hidden; z-index:0">
    	    <div name="menubarContainer" id="menubarContainer" style="width:<?php echo ($cptAllMenu*56);?>px; position: relative; left:0px; overflow:hidden;z-index:0">
    	      <table><tr>
    	       <?php drawAllMenus($menuList);?>
    	     </tr></table>
    	    </div>
          </div>
          </div>
      </td>
<?php } else {?>
    <td style="width:80%"><div id="menuBarVisibleDiv"></div></td>
<?php }?>
    <td width="25px" align="center" id="menuBarRight" class="statusBar" style="right:0;position:absolute;z-index:30;">
      <table><tr><td rowspan="2">
<?php if ($showMenuBar!='NO') {?>   
           <button id="menuBarMoveRight" dojoType="dijit.form.Button" showlabel="false"
         title="<?php echo i18n('menuBarMoveRight');?>" class="buttonMove"
         iconClass="rightBarIcon" style="position:relative; left:-3px; top:-2px;width: 14px;height:48px;margin:0;vertical-align:middle">
             <script type="dojo/method" event="onMouseDown">         
           menuBarMove=true;
           moveMenuBar('right');
         </script>
             <script type="dojo/method" event="onMouseUp">
           moveMenuBarStop();

         </script>
             <script type="dojo/method" event="onClick">
           moveMenuBarStop();
         </script>
          </button>
          <?php }?>
      </td><tr>
      </table>
    </td></tr>
    </table>
    </div>
    <?php 
          $hideMenuLeftParam = Parameter::getGlobalParameter ( 'MenuBarLeft' ); 
      if (sessionValueExists('MenuBarLeft') and getSessionValue('MenuBarLeft')=='false'){
          $hideMenuLeftParam = 'true';
      }
        if($hideMenuLeftParam == 'false' and ! isset($showModuleScreen)) { ?>
        <script type="text/javascript">
           hideShowMenu(true);
        </script>
     <?php } ?>
    <?php }else{
      include 'menuNewGuiTop.php'; 
      $nbFavoriteRow = 5;
      ?>
      <div dojoType="dijit.layout.ContentPane" id="anotherBarContainer" name="anotherBarContainer" region="center" style="width: 100%;z-index: 999;top:46px;background-color: rgb(181 181 181 / 50%);display:none"
      onClick="dojo.byId('anotherBarContainer').style.display = 'none';dojo.byId('isEditFavorite').value = 'false';">
        <table style="width:100%;"><tr>
          <td id="hideMenuLeftMargin" style="width:37px;<?php echo ($isMenuLeftOpen=='false')?'display:block;':'display:none;'?>"></td>
          <td style="width:120px;">
            <div style="margin: 5px 5px 5px 5px;height: 43px;width: auto;border: 1px solid var(--color-dark);border-radius: 5px;background: white;overflow:hidden;">
            <?php $menuBarTopMode = Parameter::getUserParameter('menuBarTopMode');?>
              <table style="width:100%;height:100%;">
                     <tr>
                       <td class="<?php if($menuBarTopMode=='ICON'){echo 'imageColorNewGuiSelected';}else{ echo 'imageColorNewGui';}?>" style="padding-left:8px;" onclick="saveUserParameter('menuBarTopMode', 'ICON');menuNewGuiFilter('menuBarCustom', null);" title="<?php echo i18n('setToIcon');?>"><?php echo formatNewGuiButton('FavorisIcon', 22, true);?></td>
                       <td class="<?php if($menuBarTopMode=='ICONTXT'){echo 'imageColorNewGuiSelected';}else{ echo 'imageColorNewGui';}?>" onclick="saveUserParameter('menuBarTopMode', 'ICONTXT');menuNewGuiFilter('menuBarCustom', null);" title="<?php echo i18n('setToIconTxt');?>"><?php echo formatNewGuiButton('FavorisIconTxt', 22, true);?></td>
                       <td class="<?php if($menuBarTopMode=='TXT'){echo 'imageColorNewGuiSelected';}else{ echo 'imageColorNewGui';}?>" onclick="saveUserParameter('menuBarTopMode', 'TXT');menuNewGuiFilter('menuBarCustom', null);" title="<?php echo i18n('setToTxt');?>"><?php echo formatNewGuiButton('FavorisTxt', 22, true);?></td>
                     </tr>
              </table>
            </div>
            <div title="<?php echo i18n('removeMenu');?>" id="removeMenuDiv" dojoType="dojo.dnd.Source" data-dojo-props="accept: ['menuBar']" style="margin: 0px 5px 0px 5px;height: 141px;width: auto;border: 1px solid var(--color-dark);border-radius: 5px;background: white;overflow:hidden;visibility: hidden;">
              <table style="width:100%;height:100%;">
               <tr>
                 <td align="center" style="font-style: italic;font-size: 11px;color: #9c9c9c;"><?php echo i18n('removeMenu');?></td>
               </tr>
               <tr>
                 <td class="imageColorNewGui" align="center" title="<?php echo i18n('removeMenu');?>" style="padding-bottom: 20px;"><?php echo formatNewGuiButton('Remove', 32, false);?></td>
               </tr>
              </table>
            </div>
          </td>
          <td>
            <div id="anotherMenubarList" name="anotherMenubarList" style="width:100%;z-index:9999999;">
             <?php
             if($defaultMenu == 'menuBarCustom'){
             	$startRow = $idRow+1;
             }else{
             	$idRow = 1;
             	$startRow = $idRow;
             } 
              for($i=$startRow; $i<=($idRow+4); $i++){
                  if($i > 5){
                    $idAnotherRow = $i-5;
                  }else{
                    $idAnotherRow = $i;
                  }
                 $idDiv = "menuBarDndSource$idAnotherRow";
                 $idInput = "idFavoriteRow$idAnotherRow";
                 ?>
              <div id="<?php echo 'anotherBar'.$idAnotherRow;?>" class="anotherBar" style="margin-top: 5px;height: 43px;width:100%;border: 1px solid var(--color-dark);border-radius: 5px;background: white;overflow:hidden;">
                <input type="hidden" id="<?php echo $idInput;?>" name="<?php echo $idInput;?>" value="<?php echo $idAnotherRow;?>"> 
                <table style="width:100%;height:100%;" onWheel="wheelFavoriteRow(<?php echo $idRow;?>, event, <?php echo $nbFavoriteRow;?>);" oncontextmenu="event.preventDefault();editFavoriteRow(false);">
                     <tr>
                     <td style="font-weight: bold;font-size: 13pt;text-align: center;color: var(--color-dark);width: 50px;border-right: 1px solid var(--color-dark);cursor:pointer;" onclick="saveUserParameter('idFavoriteRow', <?php echo $idAnotherRow;?>);gotoFavoriteRow(<?php echo $idRow;?>,<?php echo $idAnotherRow;?>);">
                       <?php echo $idAnotherRow; ?>
                     </td>
                      <td style="height:100%;">
                        <div dojoType="dojo.dnd.Source" class="anotherBarDiv" id="<?php echo $idDiv;?>" jsId="<?php echo $idDiv;?>" name="<?php echo $idDiv;?>" data-dojo-props="accept: ['menuBar'], horizontal: true" style="width: 1000%;height: 43px;vertical-align:middle;">
      	                  <?php Menu::drawAllNewGuiMenus('menuBarCustom', null, $idAnotherRow,true);?>
  	                    </div>
                      </td>
                     </tr>
                </table>
              </div>
              <?php 
              }?>
            </div>
          </td>
          <td style="width:70px;"></td>
        </tr></table>
      </div>
      <?php }

      $hideMenuTopParam = (Parameter::getGlobalParameter ( 'MenuBarTop' )=='NO')?'YES':'NO';
      if (sessionValueExists('hideMenuTop') and getSessionValue('hideMenuTop')=='YES'){
        $hideMenuTopParam = 'YES';
      }
      if ( $hideMenuTopParam=='YES' and !isNewGui() ) {?>
      <script>
        dojo.byId('statusBarDiv').style.height="0px";
        dojo.byId('statusBarDiv').style.padding="0px";
        dojo.byId('leftDiv').style.top='30px';
        dojo.byId('centerDiv').style.top='30px';
        dojo.byId('menuBarShow').style.top='30px';
        var height=parseInt(dojo.byId('mainDiv').offsetHeight)-30;
      </script>
    <?php }?>
        <div id="centerDiv" dojoType="dijit.layout.ContentPane" region="center">
    </div>
    <div id="statusBarDivBottom" dojoType="dijit.layout.ContentPane" region="bottom" style="overflow:visible;display:block;height:0px; position:absolute; bottom:0px;">
       <div id="dialogReminder" >
         <div id="reminderDiv" style="width:100%;height: 150px"></div>
          <div style="width:100%; height:15%; text-align:right">
            <?php echo i18n("remindMeIn");?>
           <input type="input" dojoType="dijit.form.TextBox" id="remindAlertTime" name="remindAletTime" value="15" style="width:25px" />
            <?php echo i18n("shortMinute");?>
           <button dojoType="dijit.form.Button" onclick="setAlertRemindMessage();">
                    <?php echo i18n("remind");?>
           </button>
         </div>
         <div style="width:100%; height:50px; text-align:right">
           <table><tr><td width="80%">
           <span id="markAllAsReadButtonDiv" >
        	 <button  dojoType="dijit.form.Button" id="markAllAsReadButton" onclick="setAllAlertReadMessage();">
        	          <?php echo i18n("markAllAsRead");?>
        	 </button>
        	 &nbsp;
        	 </span>
        	 </td><td>
        	 <button  dojoType="dijit.form.Button" onclick="setAlertReadMessage();">
        	          <?php echo i18n("markAsRead");?>
        	 </button>
        	 </td></tr></table>
         </div>
        </div>
       
    </div>
    
    <!--the left div must be created after the central div for the dynamism of the left menu on the new interface -->
 <?php if(isNewGui()){?> </div></div></div>
    <div id="leftMenu" class="menu-left"> 
    <?php 
      }
    ?>
    <div id="leftDiv" dojoType="dijit.layout.ContentPane" region="left"  splitter="<?php echo (isNewGui())?'false':'true';?>" style="width:<?php echo ((!isNewGui())?$IconSizeMenuHide2:(($isMenuLeftOpen=='false')?'0px':'250px'));?><?php echo (isNewGui())?';dispaly:none;':'';?>" >
      <?php if(!isNewGui()){?>
      <script type="dojo/connect" event="resize" args="evt">
         if (hideShowMenuInProgress) return;
         if (dojo.byId("leftDiv").offsetWidth>52) 
         saveContentPaneResizing("contentPaneLeftDivWidth", dojo.byId("leftDiv").offsetWidth, true);
         dojo.byId("hideMenuBarShowButton2").style.left=dojo.byId("leftDiv").offsetWidth+3+"px";
      </script>
      
      <div id="menuBarShow" class="dijitAccordionTitle " onMouseover="tempShowMenu('mouse');" onClick="tempShowMenu('click');">
        <div id="menuBarIcon" valign="middle"></div>
      </div>       
      <div class="container" dojoType="dijit.layout.BorderContainer" liveSplitters="false">

        <div id="logoDiv" dojoType="dijit.layout.ContentPane" region="top">
          <?php 
            $width=300;
            if (sessionValueExists('screenWidth')){
              $width = getSessionValue('screenWidth') * 0.2;
            }
            $zoom=round($width/300*100, 0);  
          ?>
          <div id="logoTitleDiv" 
               style="background-image: url(<?php 
               if (file_exists("../logo.gif")) echo '../logo.gif';
	    		          else if (file_exists("../logo.jpg")) echo '../logo.jpg';
	    		          else if (file_exists("../logo.png")) echo '../logo.png';
	    		          else echo 'img/titleWhiteSmall.png';?>); background-repeat: no-repeat; height: 50px; width:100%;background-position:center" 
               onclick="showAbout(aboutMessage);" title="<?php echo i18n('aboutMessage');?>" > 
          </div>
          <div style="position:absolute; right:0px; bottom:0px" id="helpbutton" style="text-align:right;" onclick="showHelp();">
            <div width="32px" height="32px" class="iconHelpTitle" title="<?php echo i18n('help');?>" onclick="showHelp();">&nbsp;</div>
          </div>
        </div>
        <div id="mapDiv" dojoType="dijit.layout.ContentPane" region="center" style="padding: 0px; margin:0px">
          <div dojoType="dijit.layout.AccordionContainer" style="height: 300px;" >
          <?php $selectedAccordionTop=Parameter::getUserParameter('accordionPaneTop');
                if (! $selectedAccordionTop) $selectedAccordionTop='menuTree';?>
            <div dojoType="dijit.layout.ContentPane" title="<?php echo i18n('menu');?>" 
              style="overflow: hidden !important;" <?php if ($selectedAccordionTop=='menuTree') echo 'selected="true"';?>>
              <?php include "menuTree.php"; ?>
              <script type="dojo/connect" event="onShow" args="evt">
                saveDataToSession("accordionPaneTop","messageDiv",true);
              </script>
            </div>
            <?php if (securityCheckDisplayMenu(null,'Document')) {?>
            <div dojoType="dijit.layout.ContentPane" title="<?php echo i18n('document');?>" <?php if ($selectedAccordionTop=='document') echo 'selected="true"';?>>
              <div dojoType="dojo.data.ItemFileReadStore" id="directoryStore" jsId="directoryStore" url="../tool/jsonDirectory.php"></div>
              <?php if (securityCheckDisplayMenu(null,'DocumentDirectory')) {?>
              <div style="position: absolute; float:right; right: 5px; cursor:pointer;"
                title="<?php echo i18n("menuDocumentDirectory");?>"
                onclick="if (checkFormChangeInProgress()){return false;};loadContent('objectMain.php?objectClass=DocumentDirectory','centerDiv');"
                class="iconDocumentDirectory22">
              </div>
              <?php }?>
              <div dojoType="dijit.tree.ForestStoreModel" id="directoryModel" jsId="directoryModel" store="directoryStore"
               query="{id:'*'}" rootId="directoryRoot" rootLabel="Documents"
               childrenAttrs="children">
              </div>             
              <div dojoType="dijit.Tree" id="documentDirectoryTree" model="directoryModel" openOnClick="false" showRoot='false'>
                <script type="dojo/method" event="onClick" args="item">;
                  if (checkFormChangeInProgress()){return false;}
                  loadContent("objectMain.php?objectClass=Document&Directory="+directoryStore.getValue(item, "id"),"centerDiv");
                </script>
              </div>
              <script type="dojo/connect" event="onShow" args="evt">
                saveDataToSession("accordionPaneTop", "document", true);
              </script>
            </div>
            <?php }?>
          </div>
        </div>
        <?php
           $leftBottomHeight=Parameter::getUserParameter('contentPaneLeftBottomDivHeight');
           $leftBottomHeight=($leftBottomHeight)?$leftBottomHeight.'px':'300px';?>
        <div dojoType="dijit.layout.ContentPane" id="leftBottomDiv" region="bottom" splitter="true" style="height:<?php echo $leftBottomHeight;?>;">
          <script type="dojo/connect" event="resize" args="evt">
             saveContentPaneResizing("contentPaneLeftBottomDivHeight", dojo.byId("leftBottomDiv").offsetHeight, true);
          </script>
          <div id="accordionLeftBottomDiv" dojoType="dijit.layout.AccordionContainer" persists="true">
            <?php $selectedAccordionBottom=Parameter::getUserParameter('accordionPaneBottom');
                if (! $selectedAccordionBottom) $selectedAccordionBottom='projectLinkDiv';?>
<!-- BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM -->
            <?php if (securityCheckDisplayMenu(null,'Notification') and isNotificationSystemActiv() ) {?>
            <div id="notificationAccordion"
                 dojoType="dijit.layout.ContentPane" 
                 class="background" 
                 title="<?php echo i18n('accordionNotification');?>" 
                 <?php if ($selectedAccordionBottom=='notification') echo 'selected="true"';?>>
                <div dojoType="dojo.data.ItemFileReadStore" 
                     id="notificationStore" 
                    jsId="notificationStore" url="../tool/jsonNotification.php">
                </div>
                <div style="position: absolute; float:right; right: 5px; cursor:pointer;"
                     title="<?php echo i18n("notificationAccess");?>"
                     onclick="if (checkFormChangeInProgress()){return false;};loadContent('objectMain.php?objectClass=Notification','centerDiv');"
                     class="iconNotification22">
                </div>
                <div style="position: absolute; float:right; right: 45px; cursor:pointer;"
                     title="<?php echo i18n("notificationRefresh");?>"
                     onclick="if (checkFormChangeInProgress()){return false;};refreshNotificationTree(true);"
                     class="iconNotificationRefresh22">
                </div>
                <div dojoType="dijit.tree.ForestStoreModel" id="notificationModel" jsId="notificationModel" store="notificationStore"
                     query="{id:'*'}" rootId="notificationRoot" rootLabel="Notifications"
                     childrenAttrs="children">
                </div>             
                <div dojoType="dijit.Tree" id="notificationTree" model="notificationModel" openOnClick="false" showRoot='false'>
                    <script type="dojo/method" event="onLoad" args="evt">;
                        var cronCheckNotification = <?php echo Parameter::getGlobalParameter('cronCheckNotifications'); ?>;
                        var intervalNotificationTreeDelay = cronCheckNotification*1000;
                        var intervalNotificationTree = setInterval(function() {
                                                                                refreshNotificationTree(true);
                                                                              },
                                                                   intervalNotificationTreeDelay);
                    </script>
                    <script type="dojo/method" event="onClick" args="item">;
                        if (notificationStore.getValue(item, "objClass")==="") {return false;}
                        if (checkFormChangeInProgress()){return false;}
                        var objectId = "";
                        var objClass = notificationStore.getValue(item, "objClass");
                        if (objClass=="NotificationManual") {
                                objClass="Notification";                            
                        }
                        if (notificationStore.getValue(item, "objId")!=="") {
                            objectId = notificationStore.getValue(item, "objId");
                            gotoElement(objClass, objectId, true);
                        } else {
                            loadContent("objectMain.php?objectClass="+objClass,"centerDiv");
                        }                            
                    </script>
                    <script type="dojo/method" event="getIconClass" args="item">
                        if (item == this.model.root) {
                          return "checkBox";
                        } else {
                            var isTotal = notificationStore.getValue(item,"isTotal");
                            if (isTotal==="YES") {
                                var totalCount = notificationStore.getValue(item,"count");
                                totalUnreadNotificationsCount = totalCount;
                                var ac = dijit.byId('accordionLeftBottomDiv');
                                if (totalCount>0) {
                                    ac.selectChild(dijit.byId('notificationAccordion'));
                                } else {
//                                    ac.selectChild(dijit.byId('messageDiv'));                                    
                                }
                                // Update the Title Panel
                                var titlePane = totalCount + ' ';
                                titlePane += i18n('accordionNotification');
                                var pane = dijit.byId('notificationAccordion');
                                pane.set("title",titlePane);
                                
                                // Hide menuBarNotificationCount if totalCount=0
                                if (totalCount==0) {
                                    document.getElementById("notificationTree").style.visibility = "hidden";
                                    document.getElementById("menuBarNotificationCount").style.visibility = "hidden";
                                    document.getElementById("drawNotificationUnread").style.visibility = "hidden";
                                    document.getElementById("countNotifications").style.visibility="hidden";
                                } else {
                                    // Show and Update the Notification count in menuBar
                                    document.getElementById("notificationTree").style.visibility = "visible";
                                    document.getElementById("countNotifications").style.visibility="visible";
                                    document.getElementById("menuBarNotificationCount").style.visibility = "visible";
                                    document.getElementById("countNotifications").innerHTML = totalCount;
                                }
                                loadContent("../view/menuNotificationRead.php", "drawNotificationUnread");  
                            }
                            
                            return notificationStore.getValue(item, "iconClass");
                        }
                    </script>
                </div>
                <script type="dojo/connect" event="onShow" args="evt">
                    saveDataToSession("accordionPaneBottom", "notification", true);
                </script>
            </div>
            <?php }?>
<!-- END - ADD BY TABARY - NOTIFICATION SYSTEM -->
            <div id="projectLinkDiv" class="background" dojoType="dijit.layout.ContentPane" <?php if ($selectedAccordionBottom=='projectLinkDiv') echo 'selected="true"';?> title="<?php echo i18n('ExternalShortcuts');?>">
              <?php include "../view/shortcut.php"?>
              <script type="dojo/connect" event="onShow" args="evt">
                
                saveDataToSession("accordionPaneBottom", "projectLinkDiv", true);
              </script>
            </div>
            <div id="messageDiv" dojoType="dijit.layout.ContentPane" title="<?php echo i18n('Console');?>" <?php if ($selectedAccordionBottom=='messageDiv') echo 'selected="true"';?>>
              <script type="dojo/connect" event="onShow" args="evt">
                saveDataToSession("accordionPaneBottom", "messageDiv", true);
              </script>
            </div>
          </div>
        </div>
      </div> 
      <?php
      }else{
         include 'menuNewGuiLeft.php'; 
      } 
      ?>
    </div>
     <?php if(isNewGui()){?></div><?php }?>
    <div id="dialogAlert" dojoType="dijit.Dialog" title="<?php echo i18n("dialogAlert");?>">
      <table>
        <tr>
          <td width="50px">
               <?php echo formatIcon('Alert', 32);?>
          </td>
          <td>
            <div id="dialogAlertMessage">
            </div>
          </td>
        </tr>
        <tr><td colspan="2" align="center">&nbsp;</td></tr>
        <tr>
          <td colspan="2" align="center">
            <button class="smallTextButton" dojoType="dijit.form.Button" type="submit" onclick="dijit.byId('dialogAlert').acceptCallback();dijit.byId('dialogAlert').hide();">
              <?php echo i18n("buttonOK");?>
            </button>
          </td>
        </tr>
      </table>
    </div>


<div id="dialogInfo" dojoType="dijit.Dialog" title="<?php echo i18n("dialogInformation");?>">
  <table>
    <tr>
      <td width="50px">
        <?php echo formatIcon('Info', 32);?>
      </td>
      <td>
        <div id="dialogInfoMessage">
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2" align="center">
        <br/>
        <button class="smallTextButton" dojoType="dijit.form.Button" type="submit" onclick="dijit.byId('dialogInfo').acceptCallback();dijit.byId('dialogInfo').hide();">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogError" dojoType="dijit.Dialog" title="<?php echo i18n("dialogError");?>">
  <table>
    <tr>
      <td width="50px">
        <?php echo formatIcon('Error',32);?>
      </td>
      <td>
        <div id="dialogErrorMessage">
        </div>
      </td>
    </tr>
    <tr height="50px">
      <td colspan="2" align="center">
        <?php echo i18n("contactAdministrator");?>
      </td>
    </tr>
    <tr><td colspan="2" align="center">&nbsp;</td></tr>
    <tr>
      <td colspan="2" align="center">
        <button class="smallTextButton" dojoType="dijit.form.Button" type="submit" onclick="dijit.byId('dialogError').hide();">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>



<div id="dialogQuestion" dojoType="dijit.Dialog" title="<?php echo i18n("dialogQuestion");?>">
  <table>
    <tr>
      <td width="50px">
        <img src="img/confirm.png" />
      </td>
      <td>
        <div id="dialogQuestionMessage"></div>
      </td>
    </tr>
    <tr><td colspan="2" align="center">&nbsp;</td></tr>
    <tr>
      <td colspan="2" align="center">
        <button class="smallTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogQuestion').acceptCallbackNo();dijit.byId('dialogQuestion').hide();">
          <?php echo i18n("buttonNo");?>
        </button>
        <button class="smallTextButton" id="dialogQuestionSubmitButton" dojoType="dijit.form.Button" type="submit" onclick="protectDblClick(this);dijit.byId('dialogQuestion').acceptCallbackYes();dijit.byId('dialogQuestion').hide();">
          <?php echo i18n("buttonYes");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogConfirm" dojoType="dijit.Dialog" title="<?php echo i18n("dialogConfirm");?>">
  <table>
    <tr>
      <td width="50px">
           <?php echo formatIcon('Confirm',32);?>
      </td>
      <td>
        <div id="dialogConfirmMessage"></div>
      </td>
    </tr>
    <tr><td colspan="2" align="center">&nbsp;</td></tr>
    <tr>
      <td colspan="2" align="center">
        <input type="hidden" id="dialogConfirmAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogConfirm').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" id="dialogConfirmSubmitButton" dojoType="dijit.form.Button" type="submit" onclick="protectDblClick(this);dijit.byId('dialogConfirm').acceptCallback();dijit.byId('dialogConfirm').hide();">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogPrint" dojoType="dijit.Dialog" title="<?php echo i18n("dialogPrint");?>" onHide="window.document.title=applicationName;dojo.byId('printFrame').src='../view/preparePreview.php';" >
  <?php 
    $printHeight=600;
    $printWidth=1010;
    if (sessionValueExists('screenHeight')) {
      $printHeight=round(getSessionValue('screenHeight')*0.65);
    }
    if (sessionValueExists('screenWidth') and getSessionValue('screenWidth')<1160) {
      $printWidth=round(getSessionValue('screenWidth')*0.87);
    }
  ?> 
  <div style="widht:100%" id="printPreview" dojoType="dijit.layout.ContentPane" region="center">
    <table style="widht:100%">
      <tr>
        <td width="<?php echo $printWidth;?>px" align="right">
          <div id="sentToPrinterDiv">
            <table width="100%"><tr><td width="300px" align="right">
              <button  id="sendToPrinter" dojoType="dijit.form.Button" showlabel="false" class="notButton"
                title="<?php echo i18n('sendToPrinter');?>" 
                iconClass="dijitButtonIcon dijitButtonIconPrint imageColorNewGui" >
                <script type="dojo/connect" event="onClick" args="evt">
                  sendFrameToPrinter();
                </script>
              </button>
            </td>
            <td align="left" width="<?php echo $printWidth - 300;?>px" class="textColorNewGui">
              &nbsp;<?php echo i18n('sendToPrinter')?>
            </td></tr></table>
          </div>
        </td>
      </tr>
      <tr>
        <td>   
          <iframe width="100%" height="<?php echo $printHeight;?>px"
            scrolling="auto" frameborder="0px" name="printFrame" id="printFrame" src="" onload="hideWait();" onreadystatechange="hideWait();">
          </iframe>
        </td>
      </tr>
    </table>
  </div>
</div>

<div class="dijitDialog dijitDialogFocused dijitFocused"" id="dialogShowHtml" dojoType="dijit.Dialog" onHide="if (window.frames['showHtmlFrame']) window.frames['showHtmlFrame'].location.href='../view/preparePreview.php';" title="">
  <?php 
    $printHeight=600;
    $printWidth=1010;
    if (sessionValueExists('screenHeight')) {
      $printHeight=round(getSessionValue('screenHeight')*0.50);
    }
  ?> 
  
  <div style="widht:100%" id="showHtmlLink" dojoType="dijit.layout.ContentPane" region="center">
    <table style="width:100%">
      <tr>
        <td width="<?php echo $printWidth;?>px">   
          <iframe width="100%" height="<?php echo $printHeight;?>px"
            scrolling="auto" frameborder="0px" name="showHtmlFrame" id="showHtmlFrame" src="">
          </iframe>
        </td>
      </tr>
    </table>
  </div>
</div>

<div id="showHtmlLink8" dojoType="dijit.Dialog"  id="divPrintFullScreenShowHtml2" style="top:0;z-index:99999;display:none;position:absolute;width:99%;height:99%;">test
<iframe scrolling="auto" frameborder="0px" name="showHtmlFrame3" id="showHtmlFrame3" src="">
</iframe>
</div>

<div id="dialogDetail" dojoType="dijit.Dialog" title="<?php echo i18n("dialogDetailCombo");?>" class="background" onHide="window.document.title=applicationName;">
  <?php 
    $detailHeight=600;
    $detailWidth=1010;
    if ( sessionValueExists('screenWidth') and getSessionValue('screenWidth')<1160) {
       $detailWidth = getSessionValue('screenWidth') * 0.87;
    }
    if ( sessionValueExists('screenHeight')) {
      $detailHeight=round(getSessionValue('screenHeight')*0.70);
    }
  ?> 
  <div id="detailView" dojoType="dijit.layout.ContentPane" region="center" style="overflow:hidden" class="background">
    <table style="width:100%;height:100%">
      <?php if (!isNewGui()) {?><tr style="height:10px;"><td></td></tr><?php }?>
      <tr>
        <?php if (isNewGui()) {?>
        <td></td>
        <td align="left" style="width:<?php echo ($detailWidth - 400);?>px; position:relative;">
          <div style="width:100%;font-size:8pt" dojoType="dijit.layout.ContentPane" region="center" name="comboDetailResult" id="comboDetailResult"></div>
        </td>
        
        <?php }?>
        <td width="32px" align="left" style="white-space:nowrap;<?php if (isNewGui()) echo "position:relative;top:-5px;right:12px;"?>">
          <input type="hidden" name="canCreateDetail" id="canCreateDetail" />
          <input type="hidden" id='comboName' name='comboName' value='' />
          <input type="hidden" id='comboClass' name='comboClass' value='' />
          <input type="hidden" id='comboMultipleSelect' name='comboMultipleSelect' value='' />
          <button id="comboSearchButton" dojoType="dijit.form.Button" showlabel="false"
            title="<?php echo i18n('comboSearchButton');?>" 
            iconClass="dijitButtonIcon dijitButtonIconSearch" class="dialogDetailButton">
            <script type="dojo/connect" event="onClick" args="evt">
              displaySearch();
            </script>
          </button>
          <button id="comboSelectButton" dojoType="dijit.form.Button" showlabel="false"
            title="<?php echo i18n('comboSelectButton');?>" 
            iconClass="dijitButtonIcon dijitButtonIconSelect" class="dialogDetailButton">
            <script type="dojo/connect" event="onClick" args="evt">
              selectDetailItem();
            </script>
          </button>
          <button id="comboNewButton" dojoType="dijit.form.Button" showlabel="false"
            title="<?php echo i18n('comboNewButton');?>" 
            iconClass="dijitButtonIcon dijitButtonIconNew" class="dialogDetailButton">
            <script type="dojo/connect" event="onClick" args="evt">
              newDetailItem();
            </script>
          </button>
          <button id="comboSaveButton" dojoType="dijit.form.Button" showlabel="false"
            title="<?php echo i18n('comboSaveButton');?>" 
            iconClass="dijitButtonIcon dijitButtonIconSave" class="dialogDetailButton">
            <script type="dojo/connect" event="onClick" args="evt">
              saveDetailItem();
            </script>
          </button>
         <button id="comboCloseButton" dojoType="dijit.form.Button" showlabel="false"
            title="<?php echo i18n('comboCloseButton');?>" 
            iconClass="dijitButtonIcon dijitButtonIconUndo" class="dialogDetailButton">
            <script type="dojo/connect" event="onClick" args="evt">
              hideDetail();
            </script>
          </button>
        </td>
        <?php if (!isNewGui()) {?>
        <td align="left" style="width:<?php echo ($detailWidth - 400);?>px; position:relative;">
          <div style="width:100%;font-size:8pt" dojoType="dijit.layout.ContentPane" region="center" name="comboDetailResult" id="comboDetailResult"></div>
        </td>
        <td></td><?php }?>
      </tr>
      <?php if (!isNewGui()) {?><tr><td colspan="3">&nbsp;</td></tr><?php }?>
      <tr>
        <td width="<?php echo $detailWidth;?>px" colspan="3">   
          <iframe width="100%" height="<?php echo $detailHeight;?>px"
            scrolling="auto" frameborder="0px" name="comboDetailFrame" id="comboDetailFrame" src="" >
          </iframe>
        </td>
      </tr>
    </table>
  </div>
</div>

<input type="hidden" id="noFilterSelected" name="noFilterSelected" value="true" />

<div id="dialogOtherVersion" dojoType="dijit.Dialog" title="<?php echo i18n("dialogOtherVersion");?>">
  <table>
    <tr>
      <td>
       <form id='otherVersionForm' name='otherVersionForm' onSubmit="return false;">
         <input id="otherVersionRefType" name="otherVersionRefType" type="hidden" value="" />
         <input id="otherVersionRefId" name="otherVersionRefId" type="hidden" value="" />
         <input id="otherVersionType" name="otherVersionType" type="hidden" value="" />
         <input id="otherVersionId" name="otherVersionId" type="hidden" value="" />
         <table>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel" >
               <label for="otherVersionId" ><?php echo i18n("colOtherVersions") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <table><tr><td>
               <div id="dialogOtherVersionList" dojoType="dijit.layout.ContentPane" region="center">
                 <input id="otherVersionIdVersion" name="otherVersionIdVersion" type="hidden" value="" />
               </div>
               </td><td style="vertical-align: top">
               <button id="otherVersionDetailButton" dojoType="dijit.form.Button" showlabel="false"
                 title="<?php echo i18n('showDetail')?>"
                 iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                 <script type="dojo/connect" event="onClick" args="evt">
                   showDetailOtherVersion();
                 </script>
               </button>
               </td></tr></table>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogOtherVersionAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogOtherVersion').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogOtherVersionSubmit" onclick="protectDblClick(this);saveOtherVersion();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogOtherClient" dojoType="dijit.Dialog" title="<?php echo i18n("dialogOtherClient");?>">
  <table>
    <tr>
      <td>
       <form id='otherClientForm' name='otherClientForm' onSubmit="return false;">
         <input id="otherClientRefType" name="otherClientRefType" type="hidden" value="" />
         <input id="otherClientRefId" name="otherClientRefId" type="hidden" value="" />
         <input id="otherClientId" name="otherClientId" type="hidden" value="" />
         <table>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel" >
               <label for="otherClientId" ><?php echo i18n("colOtherClients") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <table><tr><td>
               <div id="dialogOtherClientList" dojoType="dijit.layout.ContentPane" region="center">
                 
                 <select id="otherClientIdClient" size="14" name="otherClientIdClient[]" multiple
                  onchange="selectOtherClientItem();"  ondblclick="saveOtherClient();"
                  class="selectList" >
                 </select>
               </div>
               </td><td style="vertical-align: top">
               <button id="otherClientDetailButton" dojoType="dijit.form.Button" showlabel="false"
                 title="<?php echo i18n('showDetail')?>"
                 iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                 <script type="dojo/connect" event="onClick" args="evt">
                   showDetailOtherClient();
                 </script>
               </button>
               </td></tr></table>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogOtherClientAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogOtherClient').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogOtherClientSubmit" onclick="protectDblClick(this);saveOtherClient();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<!-- ADD BY Marc TABARY - 2017-02-23 - CHOICE OBJECTS LINKED BY ID TO MAIN OBJECT -->
<!-- DIALOG - What is show on Add submit -->
<div id="dialogObject" dojoType="dijit.Dialog" title="<?php echo i18n("dialogObject");?>">
  <table>
    <tr>
      <td>
        <!-- FORM -->
        <form id='objectFormDialog' name='objectFormDialog' onSubmit="return false;">
          <!-- Store the class name of the main object -->  
          <input id="mainObjectClass" name="mainObjectClass" type="hidden" value="" />
          <!-- Store the id of the instance of the main object -->            
          <input id="idInstanceOfMainClass" name="idInstanceOfMainClass" type="hidden" value="" />
          <!-- Store the linked object class Name -->  
          <input id="linkObjectClassName" name="linkObjectClassName" type="hidden" value="" />
         <table>
            <tr>
              <td class="dialogLabel" >
                <label for="dialogObjectList" ><?php echo i18n("item") ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <table><tr><td>
                  <div id="dialogObjectList" dojoType="dijit.layout.ContentPane" region="center">
                    <!-- Place of select construct dynamicaly by (dymanicListObject) -->  
                    <input id="linkedObjectId" name="linkedObjectId" type="hidden" value="" />
                  </div>
                </td></tr></table>
              </td>
            </tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          </table>
         </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="objectAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogObject').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogObjectSubmit" onclick="protectDblClick(this);saveLinkObject();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>
<!-- END ADD BY Marc TABARY - 2017-02-23 - CHOICE OBJECTS LINKED BY ID TO MAIN OBJECT -->

<!-- BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM -->
<!-- DIALOG - What is show on Change Status submit -->
<div id="dialogChangeStatus" dojoType="dijit.Dialog" title="<?php echo i18n("dialogChangeStatus");?>">
  <table>
    <tr>
      <td>
        <!-- FORM -->
        <form id="changeStatusForm" name="changeStatusForm" onSubmit="return false;">
          <!-- Store the class name of the object -->  
          <input id="objectClassChangeStatus" name="objectClassChangeStatus" type="hidden" value="" />
          <!-- Store the id of the instance of the object -->            
          <input id="idInstanceOfClassChangeStatus" name="idInstanceOfClassChangeStatus" type="hidden" value="" />
          <!-- Store the idStatus of the instance of the object -->  
          <input id="idStatusOfInstanceOfClassChangeStatus" name="idStatusOfInstanceOfClassChangeStatus" type="hidden" value="" />
          <!-- Store the idType of the instance of the object -->  
          <input id="idTypeOfInstanceOfClassChangeStatus" name="idTypeOfInstanceOfClassChangeStatus" type="hidden" value="" />
         <table>
            <tr>
              <td class="dialogLabel" >
                <label for="dialogChangeStatusList" ><?php echo i18n("colChangeStatus") ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <table><tr><td>
                  <div id="dialogChangeStatusList" dojoType="dijit.layout.ContentPane" region="center">
                    <!-- Place of select construct dynamicaly by (dymanicListObject) -->  
                    <input id="changeStatusId" name="changeStatusId" type="hidden" value="" />
                  </div>
                </td></tr></table>
              </td>
            </tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          </table>
         </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="changeStatusAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogChangeStatus').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogChangeStatusSubmit" onclick="protectDblClick(this);saveChangedStatusObject();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>
<!-- END - ADD BY TABARY - NOTIFICATION SYSTEM -->

<div id="dialogApprover" dojoType="dijit.Dialog" title="<?php echo i18n("dialogApprover");?>">
  <table>
    <tr>
      <td>
        <form id='approverForm' name='approverForm' onSubmit="return false;">
          <input id="approverRefType" name="approverRefType" type="hidden" value="" />
          <input id="approverRefId" name="approverRefId" type="hidden" value="" />
          <input id="approverItemId" name="approverItemId" type="hidden" value="" />
          <table>
            <tr>
              <td class="dialogLabel" >
                <label for="approverId" ><?php echo i18n("approver") ?>&nbsp;:&nbsp;</label>
              </td>
              <td>
                <table><tr><td>
                  <div id="dialogApproverList" dojoType="dijit.layout.ContentPane" region="center">
                    <input id="approverId" name="approverId" type="hidden" value="" />
                  </div>
                </td><td style="vertical-align: top">
                  <button id="approverIdDetailButton" dojoType="dijit.form.Button" showlabel="false"
                          title="<?php echo i18n('showDetail')?>"
                          iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                    <script type="dojo/connect" event="onClick" args="evt">
                      showDetailApprover();
                    </script>
                  </button>
                </td></tr></table>
              </td>
            </tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          </table>
         </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="approverAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogApprover').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogApproverSubmit" onclick="protectDblClick(this);saveApprover();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogOrigin" dojoType="dijit.Dialog" title="<?php echo i18n("dialogOrigin");?>">
  <table>
    <tr>
      <td>
       <form id='originForm' name='originForm' onSubmit="return false;">
         <input id="originId" name="originId" type="hidden" value="" />
         <input id="originRefId" name="originRefId" type="hidden" value="" />
         <input id="originRefType" name="originRefType" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="originOriginType" ><?php echo i18n("originType") ?>&nbsp;<?php if (!isNewGui()) echo ':&nbsp;';?></label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
               <?php echo autoOpenFilteringSelect();?>
                id="originOriginType" name="originOriginType" 
                onchange="refreshOriginList();"
                class="input" value="" >
                 <?php htmlDrawOptionForReference('idOriginable', null, null, true);?>
               </select>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel" >
               <label for="OriginOriginId" ><?php echo i18n("originElement") ?>&nbsp;<?php if (!isNewGui()) echo ':&nbsp;';?></label>
             </td>
             <td>
               <table><tr><td>
               <div id="dialogOriginList" dojoType="dijit.layout.ContentPane" region="center">
                 <input id="originOriginId" name="originOriginId" type="hidden" value="" />
               </div>
               </td><td style="vertical-align: top;max-width:25px">
               <button id="originDetailButton" dojoType="dijit.form.Button" showlabel="false"
                 title="<?php echo i18n('showDetail')?>" class="notButton notButtonRounded"
                 iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui">
                 <script type="dojo/connect" event="onClick" args="evt">
                    showDetailOrigin();
                 </script>
               </button>
               </td></tr></table>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogOriginAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogOrigin').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogOriginSubmit" onclick="protectDblClick(this);saveOrigin();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogCreationInfo" dojoType="dijit.Dialog" title="<?php echo i18n("dialogCreationInfo");?>">
  <table>
    <tr>
      <td>
        <table >
          <tr id="dialogCreationInfoCreatorLine">
            <td class="dialogLabel"  >
              <label for="dialogCreationInfoCreator" ><?php echo i18n("colIssuer") ?>&nbsp;:&nbsp;</label>
            </td>
            <td>
              <select dojoType="dijit.form.FilteringSelect" id="dialogCreationInfoCreator" 
              <?php echo autoOpenFilteringSelect();?>
              class="input" value="" >
                <?php htmlDrawOptionForReference('idUser', null, null, true);?>
              </select>
            </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          <tr id="dialogCreationInfoDateLine">
            <td class="dialogLabel" >
              <label for="dialogCreationInfoDate" ><?php echo i18n("colCreationDate") ?>&nbsp;:&nbsp;</label>
            </td>
            <td>
              <div id="dialogCreationInfoDate" dojoType="dijit.form.DateTextBox" 
                 constraints="{datePattern:browserLocaleDateFormatJs}"
                 invalidMessage="<?php echo i18n('messageInvalidDate');?> " 
                 type="text" maxlength="10" 
                 style="width:100px; text-align: center;" class="input"
                 required="true" hasDownArrow="true" 
                 missingMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
                 invalidMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
                 >
              </div>
              <span id="dialogCreationInfoTimeLine">
              <div id="dialogCreationInfoTime" dojoType="dijit.form.TimeTextBox" 
                 invalidMessage="<?php echo i18n('messageInvalidTime');?>"
                 type="text" maxlength="8"
                 style="width:65px; text-align: center;" class="input"
                 required="true" 
                 >
              </div>
              </span>
            </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
        </table>
      </td>
    </tr>
    <tr>
      <td align="center">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogCreationInfo').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogCreationInfoSubmit" onclick="protectDblClick(this);saveCreationInfo();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogAttachment" dojoType="dijit.Dialog" title="<?php echo i18n("dialogAttachment");?>"></div>
<form id='attachmentAckForm' name='attachmentAckForm'> 
   <input type='hidden' id="resultAck" name="resultAck" />
</form>   
	   
<div id="dialogDocumentVersion" dojoType="dijit.Dialog" title="<?php echo i18n("dialogDocumentVersion");?>"></div>
  
<div id="dialogExpenseDetail" dojoType="dijit.Dialog" title="<?php echo i18n("dialogExpenseDetail");?>">
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='expenseDetailForm' jsid='expenseDetailForm' name='expenseDetailForm' onSubmit="return false;">
         <input id="expenseDetailId" name="expenseDetailId" type="hidden" value="" />
         <input id="idExpense" name="idExpense" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel" >
               <label for="expenseDetailDate" ><?php echo i18n("colDate");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="expenseDetailDate" name="expenseDetailDate"
                 dojoType="dijit.form.DateTextBox" 
                 constraints="{datePattern:browserLocaleDateFormatJs}"
                 invalidMessage="<?php echo i18n('messageInvalidDate');?> " 
                 type="text" maxlength="10" 
                 style="width:100px; text-align: center;" class="input"
                 required="false"
                 hasDownArrow="true"
                 missingMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
                 invalidMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
                 >
             </div>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="expenseDetailReference" ><?php echo i18n("colReference");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <input id="expenseDetailReference" name="expenseDetailReference" value="" 
                 dojoType="dijit.form.TextBox" class="input"
                 style="width:200px" 
                 required="false"             
               />
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="expenseDetailName" ><?php echo i18n("colName");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <input id="expenseDetailName" name="expenseDetailName" value="" 
                 dojoType="dijit.form.TextBox" class="input required"
                 style="width:400px" 
                 required="true" 
                 missingMessage="<?php echo i18n('messageMandatory',array('colName'));?>" 
                 invalidMessage="<?php echo i18n('messageMandatory',array('colName'));?>"              
               />
             </td>
           </tr>
 
           <tr>
             <td class="dialogLabel" >
               <label for="expenseDetailType" ><?php echo i18n("colType");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
              <select dojoType="dijit.form.FilteringSelect" 
              <?php echo autoOpenFilteringSelect();?>
                id="expenseDetailType" name="expenseDetailType"
                style="width:200px" 
                class="input" value="" 
                onChange="expenseDetailTypeChange();" >                
                 <?php htmlDrawOptionForReference('idExpenseDetailType', null, null, false);?>            
               </select>  
             </td>
           </tr>
           <tr>
            <td colspan="2">
              <div id="expenseDetailDiv" dojoType="dijit.layout.ContentPane" region="center" >    
              </div>
            </td> 
           </tr>

           <tr>
             <td class="dialogLabel" >
               <label for="expenseDetailAmount" ><?php echo i18n("colAmount");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <?php echo ($currencyPosition=='before')?$currency:''; ?>
               <div id="expenseDetailAmount" name="expenseDetailAmount" value="" 
                 dojoType="dijit.form.NumberTextBox" class="input required"
                 constraints="{min:0,places:2}"
                 style="width:97px"
                  >
                 <?php echo $keyDownEventScript;?>
                 </div>
               <?php echo ($currencyPosition=='after')?$currency:'';?>
             </td>
           </tr> 
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogExpenseDetailAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogExpenseDetail').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogExpenseDetailSubmit" onclick="protectDblClick(this);saveExpenseDetail();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogPlan" dojoType="dijit.Dialog" title="<?php echo i18n("dialogPlan");?>">
  <table>
    <tr>
      <td>
       <form id='dialogPlanForm' name='dialogPlanForm' onSubmit="return false;">
         <table>
           <tr>
             <td class="dialogLabel">
               <label for="idProjectPlan" ><?php echo i18n("colIdProject") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
             <?php 
                $proj=null; 
                if (sessionValueExists('project')) {
                    $proj=getSessionValue('project');
                    if(strpos($proj, ",")){
                    	$proj="*";
                    }
                } else {
                  $defaultProject=Parameter::getUserParameter('defaultProject');
                  if (is_numeric($defaultProject)) $proj=$defaultProject;
                }
                if ($proj=="*" or ! $proj) $proj=null;
                ?>
                <div dojoType="dijit.layout.ContentPane" id="selectProjectList" style="overflow:unset">
                 <select dojoType="dojox.form.CheckedMultiSelect"  class="selectPlan" multiple="true" style="border:1px solid #A0A0A0;width:initial;height:218px;max-height:218px;"
                  id="idProjectPlan" name="idProjectPlan[]" onChange="changedIdProjectPlan(this.value);"
                  value="<?php echo ($proj)?$proj:' ';?>" >
                   <option value=" "><strong><?php echo i18n("allProjects");?></strong></option>
                   <?php 
                      $user=getSessionUser();
                      $wbsList=SqlList::getList('Project','sortOrder',$proj, true );
                      $sepChar=Parameter::getUserParameter('projectIndentChar');
                      if (!$sepChar) $sepChar='__';
                      $wbsLevelArray=array();
                      $inClause=" idProject in ". transformListIntoInClause(getSessionUser()->getListOfPlannableProjects());
                      $inClause.=" and idProject not in " . Project::getAdminitrativeProjectList();
                      $inClause.=" and refType= 'Project'";
                      $inClause.=" and idle=0";
                      $order="wbsSortable asc";
                      $pe=new PlanningElement();
                      $list=$pe->getSqlElementsFromCriteria(null,false,$inClause,$order,null,true);
                      foreach ($list as $projOb){
                        if (isset($wbsList[$projOb->idProject])) {
                          $wbs=$wbsList[$projOb->idProject];
                        } else {
                          $wbs='';
                        }
                        $wbsTest=$wbs;
                        $level=1;
                        while (strlen($wbsTest)>3) {
                          $wbsTest=substr($wbsTest,0,strlen($wbsTest)-6);
                          if (array_key_exists($wbsTest, $wbsLevelArray)) {
                            $level=$wbsLevelArray[$wbsTest]+1;
                            $wbsTest="";
                          }
                        }
                        $wbsLevelArray[$wbs]=$level;
                        $sep='';
                        for ($i=1; $i<$level;$i++) {$sep.=$sepChar;}
                        $val = $sep.$projOb->refName;
                        ?>
                        <option value="<?php echo $projOb->idProject; ?>"><?php echo $val; ?></option>      
                       <?php
                     }
                   ?>
                 </select>
              </div>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel"  >
               <label for="startDatePlan" ><?php echo i18n("colStartDate") ?>&nbsp;:&nbsp;</label>
             </td>
             <td >
               <div dojoType="dijit.form.DateTextBox" 
                 id="startDatePlan" name="startDatePlan" 
                 constraints="{datePattern:browserLocaleDateFormatJs}"
                 invalidMessage="<?php echo i18n('messageInvalidDate')?>" 
                 type="text" maxlength="10" 
                 style="width:100px; text-align: center;" class="input"
                 required="true"
                 hasDownArrow="false"
                 missingMessage="<?php echo i18n('messageMandatory',array(i18n('colStartDate')));?>"
                 value="<?php echo date('Y-m-d');?>" >
               </div>
             </td>
           </tr>
           <?php 
            $canPlanWithOveruse=false; 
            $right=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$user->getProfile(),'scope'=>'planningWithOveruse'));
            if($right->rightAccess==1){
              $canPlanWithOveruse=true;
            }
            if ($canPlanWithOveruse) {?>
		       <tr style="height:30px">
				     <td class="dialogLabel" >		   
				     </td>
             <td title="<?php echo i18n("helpPlanWithInfiniteCapacity");?>">
               <table>
                <tr>
                  <td>
                    <div dojoType="dijit.form.CheckBox" type="checkbox" role="checkbox" class="dijit dijitReset dijitInline dijitCheckBox"
                     id="infinitecapacity" name="infinitecapacity"
                     style="user-select: none;margin-right: 5px;" class="input"></div>
                   </td>
                   <td class="dialogLabel" style="white-space:nowrap"><label for="infinitecapacity" style="width:50px"><?php echo i18n("planWithInfiniteCapacity");?></label></td>
                </tr>
               </table>             
             </td>
           </tr>
           <?php }?>
           <tr style="height:30px">
             <td>
             </td>
             <td>
               <table>
                <tr>
                  <td>
                    <div dojoType="dijit.form.CheckBox" type="checkbox" id="onlyCheckedProject" name="onlyCheckedProject" 
                      style="margin-right: 5px;" onChange="showSelectedProject(this.checked);"></div>
                  </td>
                  <td class="dialogLabel" style="white-space:nowrap"><label for="onlyCheckedProject" style="width:50px"><?php echo i18n("showSelectedProject"); ?></label></td>
                </tr>
               </table>
             </td>
           </tr>
           <?php 
           $user=getSessionUser();
           $priority=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$user->idProfile,'scope'=>'feedingOfTheReal'));
           if( $priority and ($priority->rightAccess == 1)){
           ?>
           <tr style="height:30px">

             <td class="dialogLabel" >
               <label style="width:200px;display:none;" for="allowAutomaticFeedingOfTheReal" ><?php echo i18n("allowAutomaticFeedingOfTheReal").'&nbsp;:' ?></label>
             </td>
             <td width="200px;" >
               <div title="<?php echo i18n('allowAutomaticFeedingOfTheReal')?>" dojoType="dijit.form.CheckBox" style="margin-left:5px;margin-top:2px;display:none;"
                    class="" type="checkbox" id="allowAutomaticFeedingOfTheReal" name="allowAutomaticFeedingOfTheReal"   
                    <?php if (Parameter::getGlobalParameter('automaticFeedingOfTheReal')=='YES') { echo ' checked="checked" '; }?> >
		           </div>&nbsp;
             </td>
           </tr>
           <?php } ?>
           <tr><td></td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogPlanAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="cancelPlan();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogPlanSubmit" onclick="protectDblClick(this);plan();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>


<div id="dialogDependency" dojoType="dijit.Dialog" title="<?php echo i18n("dialogDependency");?>">
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='dependencyForm' name='dependencyForm' onSubmit="return false;">
         <input id="dependencyId" name="dependencyId" type="hidden" value="" />
         <input id="dependencyRefType" name="dependencyRefType" type="hidden" value="" />
         <input id="dependencyRefId" name="dependencyRefId" type="hidden" value="" />
         <input id="dependencyType" name="dependencyType" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="dependencyRefTypeDep" ><?php echo i18n("linkType") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
               <?php echo autoOpenFilteringSelect();?>
                id="dependencyRefTypeDep" name="dependencyRefTypeDep" 
                onchange="refreshDependencyList();"
                missingMessage="<?php echo i18n('messageMandatory',array(i18n('linkType')));?>"
                class="input" value="" >
                 <?php htmlDrawOptionForReference('idDependable', null, null, true);?>
               </select>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
         <div id="dependencyAddDiv" >
         <table>
           <tr>
             <td class="dialogLabel" >
               <label for="dependencyRefIdDep" ><?php echo i18n("linkElement") ?>&nbsp;:&nbsp;</label>
             </td>
             <td><table><tr><td>
               <div id="dialogDependencyList" dojoType="dijit.layout.ContentPane" region="center" >
                 <input id="dependencyRefIdDep" name="dependencyRefIdDep" type="hidden" value="" />
               </div>
               </td><td style="vertical-align: top">
               <button id="dependencyDetailButton" dojoType="dijit.form.Button" showlabel="false"
                 title="<?php echo i18n('showDetail')?>"
                 iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                 <script type="dojo/connect" event="onClick" args="evt">
                    showDetailDependency();
                 </script>
               </button>
               </td></tr></table>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
         </div>
         <div id="dependencyEditDiv">
           <table>
             <tr>
               <td class="dialogLabel"  >
                 <label for="dependencyRefIdDepEdit" ><?php echo i18n("linkElement") ?>&nbsp;:&nbsp;</label>
               </td>
               <td>
                 <select dojoType="dijit.form.FilteringSelect" 
                 <?php echo autoOpenFilteringSelect();?>
                  id="dependencyRefIdDepEdit" name="dependencyRefIdDepEdit" 
                  class="input" value="" size="10">
                 </select>
               </td>
             </tr>
              <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           </table>  
         </div>
         <div id="dependencyDelayDiv">
	         <table>
	           <tr>
	             <td class="dialogLabel" >
	               <label for="dependencyDelay" ><?php echo i18n("colDependencyDelay");?>&nbsp;:&nbsp;</label>
	             </td>
	             <td><span class="nobr">
	               <input id="dependencyDelay" name="dependencyDelay" value="0" 
	                 dojoType="dijit.form.NumberTextBox" 
                   constraints="{min:-999, max:999}" 
	                 style="width:50px; text-align: center;" 
	                 missingMessage="<?php echo i18n('messageMandatory',array(i18n('colDependencyDelay')));?>" 
	                 required="true" />&nbsp;
	               <?php echo i18n('colDependencyDelayComment'); ?>
	               </span>
	             </td>
	           </tr>
	           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
	         </table>
          </div>
          <div id="dependencyTypeDiv">
	         <table>
	           <tr>
	             <td class="dialogLabel" >
	               <label for="dependencyType" ><?php echo i18n("colType");?>&nbsp;:&nbsp;</label>
	             </td>
	             <td><span class="nobr">
                <select dojoType="dijit.form.FilteringSelect" class="input" name="typeOfDependency" id="typeOfDependency"
                  <?php echo autoOpenFilteringSelect();?> style="width:115px;height:20px">
                  <?php $depType=array('E-S','E-E','S-S');
                  foreach ($depType as $type) {
                    $lib=( (substr($type,0,1)=='E')?i18n('colEnd'):i18n('colStart') ).' - '.( (substr($type,-1)=='E' )?i18n('colEnd'):i18n('colStart') );
                    echo "<option value='$type'>$lib</option>";
                  }?>
                </select>
                 </td>
	           </tr>
	           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
	         </table>
          </div>
          <!--KEVIN TICKET #2038 -->
          	<table>
							<tr>
								<td><label for="dependencyComment"><?php echo i18n("colComment");?>&nbsp;:&nbsp;</label></td>															
								<td><input id="dependencyComment" name="dependencyComment" value="" dojoType="dijit.form.Textarea" class="input"/></td>
							</tr>
							<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
						</table>
					</form>
				</td>
			</tr>         
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogDependencyAction">
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogDependency').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="dialogDependencySubmit" onclick="protectDblClick(this);saveDependency();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="xdialogShowImage" dojoType="dojox.image.LightboxDialog" >
</div>
<form  method="POST" style="display:none" id="directAccessForm" action="../view/main.php">
  <input type="hidden" name="directAccessPage" id="directAccessPage" value="" />
  <input type="hidden" name="menuActualStatus" id="menuActualStatus" value="" />
  <input type="hidden" name="p1name" id="p1name" value="" />
  <input type="hidden" name="changeCurrentLocale" id="changeCurrentLocale" value="" />
  <input type="hidden" name="p1value" id="p1value" value="" />
</form>
<form id='favoriteForm' name='favoriteForm' onSubmit="return false;">
  <input type="hidden" id="page" name="page" value=""/>
  <input type="hidden" id="print" name="print" value=true />
  <input type="hidden" id="report" name="report" value=true />
  <input type="hidden" id="outMode" name="outMode" value='html' />
  <input type="hidden" id="reportName" name="reportName" value="test" />
</form>
<div id="deleteMultipleResultDiv" dojoType="dijit.layout.ContentPane" region="none" style="display:none"></div>
<div id="resultDivMain"           dojoType="dijit.layout.ContentPane" region="none" style="display:none"></div>
<div id="disconnectionMessage" dojoType="dijit.layout.ContentPane" region="none" class="resultDiv" style="display:none;opacity:1">
  <div id="disconnectionMessageText" style="text-align:center;cursor:pointer;" onClick="quitConfirmed = true;window.location = '../index.php';"></div>
</div>
<div id="textFullScreenCKdiv" style="width:300px;height:200px;position:absolute;display:none;" class="">
  <textarea style="width:100%; height:100%" name="textFullScreenCK" id="textFullScreenCK"></textarea>
</div>
</body>
</html>
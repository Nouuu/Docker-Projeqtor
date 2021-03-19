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
 * Presents left menu of application. 
 */
  require_once "../tool/projeqtor.php";

$displayMode=Parameter::getUserParameter('menuLeftDisplayMode');
 $viewSelect=Parameter::getUserParameter('bottomMenuDivItemSelect');
?>

<div id="menuLeftBarContaineur" class="container"  dojoType="dijit.layout.BorderContainer" liveSplitters="false" >

  <div id="menuBArNewGuiAcces"  class="container"  dojoType="dijit.layout.BorderContainer" region="left"  style="width:32px;height:100%;">
    <div id="breadScrumb"  dojoType="dijit.layout.ContentPane"  region="center" style="height:65%;" >
    </div>
    <div id="menuPersonalAcces"  onresize="showBottomLeftMenu();"  dojoType="dijit.layout.ContentPane" region="bottom" style="height:35%;overflow: hidden;" >
      <div id="iconsBottomSize" onresize="">
        <div id="buttonParameter" class="iconParameter iconSize22 iconBreadSrumb <?php echo ($viewSelect=='Parameter')? 'iconBreadSrumbSelect':'';?>" onclick="showBottomContent('Parameter')"  title="<?php echo i18n('menuParameter');?>"></div>
        <div id="buttonLink" class="iconButtonLink16 iconSize22 iconBreadSrumb <?php echo ($viewSelect=='Link')? 'iconBreadSrumbSelect':'';?>" onclick="showBottomContent('Link')" title="<?php echo i18n('ExternalShortcuts');?>"></div>
        <?php if (securityCheckDisplayMenu(null,'Document')) {?>
        <div title="<?php echo i18n('document');?>" id="buttonDocument" class="iconDocumentDirectory iconSize22 iconBreadSrumb <?php echo ($viewSelect=='Document')? 'iconBreadSrumbSelect':'';?>" onclick="showBottomContent('Document')"></div>
        <?php }?>
        <div id="buttonConsole" class="iconConsole iconSize22 iconBreadSrumb <?php echo ($viewSelect=='Console')? 'iconBreadSrumbSelect':'';?>" onclick="showBottomContent('Console')"  title="<?php echo i18n('Console');?>"></div>
        <?php if(securityCheckDisplayMenu(null,'Notification') and isNotificationSystemActiv()){?>
        <div id="buttonNotification" class="iconNotification  iconSize22 iconBreadSrumb <?php echo ($viewSelect=='Notification')? 'iconBreadSrumbSelect':'';?>" onclick="showBottomContent('Notification')"  title="<?php echo i18n('accordionNotification');?>"></div>
        <?php }?>
      </div>
    </div>
  </div>
  <div id="menuBarAccesLeft"  class="container"  dojoType="dijit.layout.BorderContainer" region="center"  oncontextmenu="event.preventDefault();showIconLeftMenu('Icon');">
    <div id="menuBarAccesTop" class="" dojoType="dijit.layout.ContentPane"  region="center" style="height:65%;overflow-x: hidden;" >
      <nav id="ml-menu" class="menu">
      <input id="displayModeLeftMenu" value="<?php echo $displayMode;?>" hidden />
            <?php // draw Menus
             echo drawLeftMenuListNewGui($displayMode);
            ?>
            <div class="menu__searchMenuDiv" style="display:none;"></div>
      </nav>

	<?php if(Parameter::getUserParameter('helpDisplayIconMesagediv')!='yes'){ ?>
	 <div id="helpDisplayIcon" class="helpDisplayIcon" onclick="helpDisplayIconIsRead('yes')" style="vertical-align: middle;"
	   onmouseover="dojo.byId('textDisplayIcon').style.display='none';dojo.byId('hideTextDisplayIcon').style.display='block';"
	  onmouseout="dojo.byId('textDisplayIcon').style.display='block';dojo.byId('hideTextDisplayIcon').style.display='none';" >
	   <div id="textDisplayIcon" style="color: rgb(180 180 180);padding-top:2px;"><span><?php echo i18n('helpDisplayIconMenuLeft');?></span></div>
	   <div id="hideTextDisplayIcon" style="display:none;padding-top: 8px;" ><span><?php echo i18n('clickIntoToCloseHelpMessage');?></span></div>
	 </div>
	<?php } ?>
    </div>
    <div id="menuBarAccesBottom" dojoType="dijit.layout.ContentPane" region="bottom" style="height:35%;">
      <div class="container" style="height:98%;width:100%;">
      <input id="selectedViewMenu" value="<?php echo $viewSelect;?>" hidden />
         <div id="loadDivBarBottom" style="height:100%;padding-top:10px;display:<?php echo ($viewSelect=='Console')?'none':'block';?>;">
           <div id="parameterDiv" class="menuBottomDiv" dojoType="dijit.layout.ContentPane" style="display:<?php echo ($viewSelect=='Parameter')?'block':'none';?>;">
              <?php include '../tool/drawBottomParameterMenu.php';?>
           </div>
           <div id="projectLinkDiv" class="menuBottomDiv" dojoType="dijit.layout.ContentPane" style="display:<?php echo ($viewSelect=='Link')?'block':'none';?>;">
              <?php include "../view/shortcut.php";?>
           </div>
           <div id="documentsDiv"  class="menuBottomDiv" dojoType="dijit.layout.ContentPane" style="display:<?php echo ($viewSelect=='Document')?'block':'none';?>;">
              <div dojoType="dojo.data.ItemFileReadStore" id="directoryStore" jsId="directoryStore" url="../tool/jsonDirectory.php"></div>
              <?php if (securityCheckDisplayMenu(null,'DocumentDirectory')) {?>
              <div style="position: absolute; float:right; right: 5px; cursor:pointer;"
                title="<?php echo i18n("menuDocumentDirectory");?>"
                onclick="if (checkFormChangeInProgress()){return false;};loadContent('objectMain.php?objectClass=DocumentDirectory','centerDiv');"
                class="iconDocumentDirectory22 iconDocumentDirectory iconSize22">
              </div>
              <?php }?>
              <div dojoType="dijit.tree.ForestStoreModel" id="directoryModel" jsId="directoryModel" store="directoryStore"
               query="{id:'*'}" rootId="directoryRoot" rootLabel="Documents"
               childrenAttrs="children">
              </div>             
              <div dojoType="dijit.Tree" id="documentDirectoryTree" style="margin-top:10px;" model="directoryModel" openOnClick="false" showRoot='false'>
                <script type="dojo/method" event="onClick" args="item">;
                  if (checkFormChangeInProgress()){return false;}
                  loadContent("objectMain.php?objectClass=Document&Directory="+directoryStore.getValue(item, "id"),"centerDiv");
                </script>
              </div>
           </div>
           <?php 
           if( securityCheckDisplayMenu(null,'Notification') and isNotificationSystemActiv()){
           ?>
           <div id="notificationBottom" class="menuBottomDiv" dojoType="dijit.layout.ContentPane" style="display:<?php echo ($viewSelect=='Notification')?"block":"none";?>;height:100%" >
                <div dojoType="dojo.data.ItemFileReadStore" 
                     id="notificationStore" 
                    jsId="notificationStore" url="../tool/jsonNotification.php" >
                </div>
                <div style="position: absolute; float:right; right: 5px; cursor:pointer;"
                     title="<?php echo i18n("notificationAccess");?>"
                     onclick="if (checkFormChangeInProgress()){return false;};loadContent('objectMain.php?objectClass=Notification','centerDiv');"
                     class=" iconNotification iconSize22" >
                </div>
                <div style="position: absolute; float:right; right: 45px; cursor:pointer;"
                     title="<?php echo i18n("notificationRefresh");?>"
                     onclick="if (checkFormChangeInProgress()){return false;};refreshNotificationTree(true);"
                     class="iconRefresh iconSize22">
                </div>
                <div dojoType="dijit.tree.ForestStoreModel" id="notificationModel" jsId="notificationModel" store="notificationStore"
                     query="{id:'*'}" rootId="notificationRoot" rootLabel="Notifications"
                     childrenAttrs="children" > 
                </div>             
                 <div dojoType="dijit.Tree" id="notificationTree" model="notificationModel" openOnClick="false" showRoot='false' style="height:92.5%;margin-top:20px;">
                    <script type="dojo/method" event="onLoad" args="evt">;
                        var cronCheckNotification = <?php echo Parameter::getGlobalParameter('cronCheckNotifications'); ?>;
                        var intervalNotificationTreeDelay = cronCheckNotification*1000;
                        var intervalNotificationTree = setInterval(function() { refreshNotificationTree(true);},intervalNotificationTreeDelay);
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
              </div>
           <?php 
           }           
           ?>
           </div>
           <div id="messageDivNewGui" class="messageDivNewGui" style="display:<?php echo ($viewSelect=='Console')?'block':'none';?>;height:95%;"></div>
      </div>
    </div>
  </div>
</div>

<?php 
// functions

function sortMenus(&$listMenus, &$result, $parent,$level,$rightPluginAcces=false ){
  $level++;
  $hr=new HabilitationReport();
  $user=getSessionUser();
  $lst=$hr->getSqlElementsFromCriteria(array('idProfile'=>$user->idProfile, 'allowAccess'=>'1'), false);
  $allowedCategory=array();
  foreach ($lst as $h) {
    $reportHb=$h->idReport;
    $nameReport=SqlList::getNameFromId('Report', $reportHb, false);
    if (! Module::isReportActive($nameReport)) continue;
    $category=SqlList::getFieldFromId('Report', $reportHb, 'idReportCategory',false);
    $allowedCategory[$category]=$category;
  }
  foreach ($listMenus as $id=>$menu){
    if(!$rightPluginAcces && $menu->name=='navPlugin')continue;
    if($menu->idParent == $parent){
      if ($menu->idParent=='') {
      	$menu->idParent=0;
      }
      $key=$level.'-'.numericFixLengthFormatter($menu->idParent,5).'-'.numericFixLengthFormatter($menu->sortOrder,5);
      $isMenu=true;
      if($menu->idReport!=0){
        $report=new Report($menu->idReport);
        if(in_array($report->idReportCategory, $allowedCategory)){
          $isMenu=false;
        }else{
          continue;
        }
      }
      $result[$key] = array('level'=>$level,'objectType'=>($isMenu)?'menu':'reportDirectInMenu','object'=>$menu);
      sortMenus($listMenus, $result, $menu->id,$level,$rightPluginAcces);
    }
  }
}

function storReports($listReport, &$res, $lstNewNavMenu, $idMenuReport, $level ) { //store report 
    $count=array();
    $isNewPId=array();
    $levelParent=$level-1;
    $levelSub=$level+1;
    foreach ($listReport as $id=>$report){
        $file=substr($report->file, 0,strpos($report->file, '.php'));
        $idParent=$idMenuReport->id.$levelParent.$report->idReportCategory;
        if(in_array($file, $lstNewNavMenu)){
            if(!isset($count[$file])){
              
              $keyParent=$level.'-'.numericFixLengthFormatter($report->idReportCategory,5).'-'.numericFixLengthFormatter($report->sortOrder,5);
              $isNewPId[$file]=$report->idReportCategory.$level.$report->sortOrder;
              $obj= array('id'=>$isNewPId[$file],'name'=>ucfirst($file),'idParent'=>$idParent);
              $res[$keyParent]=array('level'=>$level,'objectType'=>'reportSubMenu','object'=>$obj);
            }
            $count[$file]=1;
            $key=$levelSub.'-'.numericFixLengthFormatter($isNewPId[$file],5).'-'.numericFixLengthFormatter($report->sortOrder,5);
            $obj= array('id'=>$report->id,'name'=>$report->name,'idParent'=>$isNewPId[$file],'idMenu'=>$report->idReportCategory);
            $res[$key]=array('level'=>$level,'objectType'=>'reportDirect','object'=>$obj);
         }else{
          $keyParent=$level.'-'.numericFixLengthFormatter($report->idReportCategory,5).'-'.numericFixLengthFormatter($report->sortOrder,5);
          $obj= array('id'=>$report->id,'name'=>$report->name,'idParent'=>$idParent,'idMenu'=>$report->idReportCategory);
          $res[$keyParent]=array('level'=>$level,'objectType'=>'reportDirect','object'=>$obj);
         }
    }
}


function getReportsMenu(){
  // ===============list of all reportCategories by user profil;
  $level=2;
  $hr=new HabilitationReport();
  $user=getSessionUser();
  //$allowedReport=array();
  $allowedCategory=array();
  $lst=$hr->getSqlElementsFromCriteria(array('idProfile'=>$user->idProfile, 'allowAccess'=>'1'), false);
  $res=array();
  $listCateg=SqlList::getList('ReportCategory');
  $idMenuReport=SqlElement::getSingleSqlElementFromCriteria('Navigation', array('name'=>'navReports'));
  $menuReport=SqlElement::getSingleSqlElementFromCriteria('Menu', array('name'=>'menuReports'));
  foreach ($lst as $h) {
    $report=$h->idReport;
    $nameReport=SqlList::getNameFromId('Report', $report, false);
    if (! Module::isReportActive($nameReport)) continue;
    //$allowedReport[$report]=$report;
    $category=SqlList::getFieldFromId('Report', $report, 'idReportCategory',false);
    $allowedCategory[$category]=$category;
  }
  $c=1;
  $lstIdCate=array();
  $idReportMenu=$idMenuReport->id.$level.$menuReport->id;
  $menuReportKey=$level.'-'.numericFixLengthFormatter($idMenuReport->id,5).'-'.numericFixLengthFormatter($c,5);
  $object= array('id'=>$idReportMenu,'name'=>$menuReport->name,'idParent'=>$idMenuReport->id,'idMenu'=>$menuReport->id);
  $res[$menuReportKey]=array('level'=>$level,'objectType'=>'reportDirect','object'=>$object);
  foreach ($listCateg as $id=>$name) {
    if (isset($allowedCategory[$id])) {
      $c++;
      $cat=new ReportCategory($id);
      $lstIdCate[]=$id;
      $idReport=$idMenuReport->id.$level.$id;
      $key=$level.'-'.numericFixLengthFormatter($idMenuReport->id,5).'-'.numericFixLengthFormatter($c,5);
      $obj= array('id'=>$idReport,'name'=>$cat->name,'idParent'=>$idMenuReport->id);
      $res[$key]=array('level'=>$level,'objectType'=>'report','object'=>$obj);
    }
  }
  //===================
  //=================== lis of all report dependant of this categoryies

   $level++;
   $reportDirect= new Report();
   $lstCatId=(empty($lstIdCate))?"0":"0".implode(",", $lstIdCate);
   $where=" idReportCategory in (".$lstCatId.")";
   $reportList= $reportDirect->getSqlElementsFromCriteria(null,false,$where,"file");
   $nameFile=SqlList::getList('Report','file');
   $lstReportName=array();
   $lstNewNavMenu=array();
   foreach ($nameFile as $idN=>$nameFile){
    $lstReportName[]=substr($nameFile, 0,strpos($nameFile, '.php'));
   }
   $countNameFil=array_count_values($lstReportName);
   foreach ($countNameFil as $name=>$val){
    if($val==1)unset($countNameFil[$name]);
    else $lstNewNavMenu[]=$name;
   }
   storReports($reportList,$res, $lstNewNavMenu,$idMenuReport, $level);
  return $res;
 
}

function getPlugins (){
  $level=2;
  $result=array();
  $idMenu='';
  $menu=new Menu;
  $exist=false;
  $idMenuPlugin=SqlElement::getSingleSqlElementFromCriteria('Navigation', array('name'=>'navPlugin'));
  $plList=Plugin::getActivePluginList();
  foreach ($plList as $intalPlugin){
    $plInstal[$intalPlugin->uniqueCode]=$intalPlugin->uniqueCode;
    $plInstalVersion[$intalPlugin->uniqueCode]=$intalPlugin->pluginVersion;
  }
  $where="id > 100000 and id <> 100006001"; // Ids for plugin menu, except Kanban (not a plugin any more)
  $pluginsInstal=$menu->getSqlElementsFromCriteria(null,null,$where);
  $c=10;
  foreach ($pluginsInstal as $id=>$menuPlugin){
    if(strlen($menuPlugin->id)==9)$idMenu=substr($menuPlugin->id,0,-3);
    else  $idMenu=$menuPlugin->id;
    if (!$menuPlugin->canDisplay() ){
  	   unset($plInstal[$idMenu]);
      continue;
    }
    if (securityCheckDisplayMenu($menuPlugin->id, null, getSessionUser())==false) {
      //unset($plInstal[$idMenu]);
      continue;
    }
    if(!empty($plInstal)){
      foreach ($plInstal as $valId){
        if($idMenu==$valId){
          $exist=true;
          break;
        }
      }
    }
    if(!$exist)continue;
    $c=$menuPlugin->sortOrder;
    $key=$level.'-'.numericFixLengthFormatter($idMenuPlugin->id,5).'-'.numericFixLengthFormatter($c,5);
    $obj= array('id'=>$menuPlugin->id,'name'=>$menuPlugin->name,'idParent'=>$idMenuPlugin->id,'idMenu'=>$menuPlugin->id,'menuType'=>$menuPlugin->type);
    $result[$key]=array('level'=>$level,'objectType'=>'pluginInst','object'=>$obj);
  }
  $urlPlugins = "http://projeqtor.org/admin/getPlugins.php";
  $currentVersion=null;
  $json=null;
  $getYesNo=Parameter::getGlobalParameter('getVersion');
  if ($getYesNo=='NO') {
    return $result;
  }
  if (ini_get('allow_url_fopen')) {
    enableCatchErrors();
    enableSilentErrors();
    $currentVersion=file_get_contents($urlPlugins);
    $json = file_get_contents($urlPlugins);
    disableCatchErrors();
    disableSilentErrors();
  }
  if (!$json) return $result;
  $object = json_decode($json);
  $plugins=$object->items;
  $userLang = getSessionValue('currentLocale');
  $lang = "en";
  $user=getSessionUser();
  if(substr($userLang,0,2)=="fr")$lang="fr";
  if(!empty($plugins)){
    foreach ($plugins as $id=>$val){
      if($user->idProfile==1 and !empty($plInstal) ){
        if(in_array($val->id, $plInstal)){
          $idPinstal=$plInstalVersion[$val->id];
          if($val->version!=$idPinstal){
              $asNotif=SqlElement::getSingleSqlElementFromCriteria('Notification', array("idPluginIdVersion" => "".$val->id."/".$idPinstal.""));
              if($asNotif->id==''){
                $notif=new Notification();
                $notif->name=i18n('newVersion',array('')).' - '.$val->code;
                $notif->idUser=getCurrentUserId();
                $notif->content=i18n('newVersionForPluginName',array((($lang=='fr')?$val->nameFr:$val->nameEn),$idPinstal,$val->version,));
                $notif->emailSent=0;
                $notif->idNotificationType=SqlList::getFirstId('NotificationType');
                $notif->idResource=$user->id;
                $notif->notificationDate=date("Y-m-d");
                $notif->idStatusNotification = 1;
                $notif->title=i18n('newPluginVersion',array((($lang=='fr')?$val->nameFr:$val->nameEn)));
                $notif->idPluginIdVersion=$val->id."/".$idPinstal;
                $res=$notif->save();
              }
          }
          continue;
        }   
      }
      $c++;
      $k=$level.'-'.numericFixLengthFormatter($idMenuPlugin->id,5).'-'.numericFixLengthFormatter($c,5);
      $result[$k]=array('level'=>$level,'objectType'=>'pluginNotInst','object'=>$val);
    }
  }
  return $result;
}

function getNavigationMenuLeft (){
  $level=0;
  $result=array();
  $nav=new Navigation();
  $isLanguageActive=(Parameter::getGlobalParameter('displayLanguage')=='YES')?true:false;
  $contexctMenuMain=$nav->getSqlElementsFromCriteria(null, false,null,'id asc');
  $menuPlugin=SqlElement::getSingleSqlElementFromCriteria('Menu', array('name'=>'menuPlugin'));
  $menuReport=SqlElement::getSingleSqlElementFromCriteria('Menu', array('name'=>'menuReports'));
  $rightPluginAcces=securityCheckDisplayMenu($menuPlugin->id,substr($menuPlugin->name,4));
  $rightReportAcces=securityCheckDisplayMenu($menuReport->id,substr($menuReport->name,4));
  sortMenus($contexctMenuMain,$result,0,$level,$rightPluginAcces);
  $navTa=array();
  $allNavSect=array();
  foreach ($result as $id=>$context){
      $context=$context['object'];
      if($context->idMenu){
        if($context->idMenu!=0 ){
          $unset=false;
          $menu=new Menu($context->idMenu);
          if (!isNotificationSystemActiv() and strpos($menu->name, "Notification")!==false) $unset=true; 
          if (! $menu->canDisplay() )  $unset=true;
          if (!$isLanguageActive and $menu->name=="menuLanguage")  $unset=true;
          if (!Module::isMenuActive($menu->name))  $unset=true;
          if (!securityCheckDisplayMenu($menu->id,substr($menu->name,4))) $unset=true;
          if($unset==true){
            unset($result[$id]);
            continue;
          }
          $lstMenuId[]=$context->idParent;
        }else if($context->id!=6 and $rightReportAcces ){
           $navTa[$id]=$context->id;
           $allNavSect[$context->id]=$context->idParent;
        }
      }
  }

  $exist=array();
  foreach ($lstMenuId as $idMenu){
    foreach ($navTa as $idArray=>$val){
      if($val==$idMenu){
        unset($navTa[$idArray]);
        $exist[]=$val;
        continue;
      }
    }
  }
  asort ($allNavSect);
  ksort($exist);
  $exist=array_reverse($exist);
  foreach ($allNavSect as $idN=>$idP){
    foreach ($exist as $idT=>$valT){
      if($idN==$valT and array_search($idP, $navTa)){
         unset($navTa[array_search($idP, $navTa)]);
      }
    }
  }
  foreach ($navTa as $idM=>$m){
    unset($result[$idM]);
  }
  if($rightReportAcces)$result=array_merge ($result,getReportsMenu());
  if($rightPluginAcces)$result=array_merge($result,getPlugins());
  ksort($result);
  foreach ($result as $idx=>$res) {
    $nav=$res['object'];
    if (is_object($nav) and property_exists($nav, 'idMenu') and $nav->idMenu==0 and property_exists($nav, 'idReport') and $nav->idReport==0) {
      if (navMenuHasItem($nav->id,$result)==false) {
        unset($result[$idx]);
      }
    }
  }
  return $result;
}
function navMenuHasItem($id,$result) {
  $hasItem=false;
  foreach($result as $res) {
    $nav=$res['object'];
    $idReport=0;
    $idMenu=0;
    $idParent=0;
    $idItem=0;
    if (! is_object($nav)) {
      $idParent=(isset($nav['idParent']))?$nav['idParent']:0;
      $idMenu=(isset($nav['idMenu']))?$nav['idMenu']:0;
      $idReport=(isset($nav['idReport']))?$nav['idReport']:0;
      $idItem=(isset($nav['id']))?$nav['id']:0;
    } else {
      $idParent=(property_exists($nav, 'idParent'))?$nav->idParent:0;
      $idReport=(property_exists($nav, 'idReport'))?$nav->idReport:0;
      $idMenu=(property_exists($nav, 'idMenu'))?$nav->idMenu:0;
      $idItem=(property_exists($nav, 'id'))?$nav->id:0;
    }
    if ($idParent==$id) {
      if ($idMenu!=0 or $idReport!=0) {
        $hasItem=true;
        break;
      } else if ($idItem and navMenuHasItem($idItem,$result)) {
        $hasItem=true;
        break;
      } 
    }
  }
  return $hasItem;
}

function drawLeftMenuListNewGui($displayMode){
  $result='';
  $old="";
  $idP="";
  $maineDraw=false;
  $allMenu=getNavigationMenuLeft();
  $result.='<div class="menu__wrap">';
  $displayIcon=($displayMode=='TXT')?"display:none;":"display:block;";
  $user=getCurrentUserId();
  foreach ($allMenu as $id=>$menu){
    // creat object Navigation if is report or plugin 
    if($menu['objectType']=='report' or $menu['objectType']=='reportSubMenu' or $menu['objectType']=='reportDirect'){
      $obj=new Navigation();
      $obj->id=$menu['object']['id'];
      $obj->idParent=$menu['object']['idParent'];
      $obj->name=$menu['object']['name'];
      $obj->idMenu=($menu['objectType']=='reportDirect')?$menu['object']['idMenu']:0;
      if($menu['objectType']=='reportDirect'){
        $file=(isset($menu['object']['file']))?$menu['object']['file']:'';
      }
    }else if($menu['objectType']=='pluginInst' or $menu['objectType']=='pluginNotInst'){
      if($menu['objectType']=='pluginInst'){
        $obj->id=$menu['object']['id'];
        $obj->idParent=$menu['object']['idParent'];
        $obj->name=$menu['object']['name'];
        $obj->idMenu=$menu['object']['idMenu'];
      }else{
        $object=$menu['object'];
        $userLang = getSessionValue('currentLocale');
        $lang = "en";
        if(substr($userLang,0,2)=="fr")$lang="fr";
        $objName=($lang=='fr')?$object->nameFr:$object->nameEn;
        $objectID=$object->id;
        $objectCode=$object->code;
        
      }
    }else{
      $obj=$menu['object'];
      if( $menu['objectType']=='reportDirectInMenu'){
         $report=New Report($obj->idReport);
         $file=$report->file;
      }
    }
    //
    // draw ul element
    if($old!=$menu['level'] and $menu['level']==1 and $maineDraw!=true){
      $maineDraw=true;
      $result.='<ul data-menu="main" class="menu__level" tabindex="-1" role="menu" >';
    }
    if ( ($old!=$menu['level'] or ($old==$menu['level'] and $idP!=$obj->idParent)) and $menu['level']!=1 ){
      $result.='</ul>';
      $nameLink='submenu-'.$obj->idParent;
      $result.='<ul data-menu="'.$nameLink.'" id="'.$nameLink.'" class="menu__level" tabindex="-1" role="menu" >';
    }
    //
    //draw menu in li element
    if( (($obj->idMenu!=0 and $obj->idReport==0) or ($obj->idMenu==0 and $obj->idReport!=0) ) and $menu['objectType']!='pluginNotInst'){
      if( $menu['objectType']!='reportDirect' and $menu['objectType']!='reportDirectInMenu'){
        $realMenu=new Menu($obj->idMenu);
        $menuName=$realMenu->name;
        $menuNameI18n = i18n($menuName);
        $menuTag=$obj->tag;
        $menuName2 = addslashes(i18n($menuName));
        $classEl=substr($menuName,4);
        $isFav=SqlElement::getSingleSqlElementFromCriteria('MenuCustom', array("name"=>$menuName,"idUser"=>$user));
        if($realMenu->type=='item'){
          $funcOnClick="loadMenuBarItem('".$classEl."','".htmlEncode($menuName2,'quotes')."','bar');showMenuBottomParam('".$classEl."','false')";
        }elseif ($realMenu->type=='plugin'){
        	$funcOnClick="loadMenuBarPlugin('".$classEl."','".htmlEncode($menuName2,'quotes')."','bar');showMenuBottomParam('".$classEl."','true')";
        }else{
          $funcOnClick="loadMenuBarObject('".$classEl."','".htmlEncode($menuName2,'bar')."','bar');showMenuBottomParam('".$classEl."','true')";
        }
        if($isFav->id==''){
          $mode='add';
          $class="menu__add__Fav";
          $styleDiv="display:none;";
        }else{
          $mode='remove';
          $class="menu__as__Fav";
        }
        $styleDiv="display:none;";
        $funcuntionFav="addRemoveFavMenuLeft('div".$obj->name."', '".$obj->name."','".$mode."','".$menu['objectType']."');";
        
        $result.='<li class="menu__item" role="menuitem" onmouseenter="checkClassForDisplay(this,\'div'.$obj->name.'\',\'enter\');" onmouseleave="checkClassForDisplay(this,\'div'.$obj->name.'\',\'leave\');">'; //li
        $result.='<a class="menu__linkDirect" onclick="'.$funcOnClick.'" href="#" id="'.$obj->name.'" ><div class="icon'.$classEl.' iconSize16" style="'.$displayIcon.'position:relative;float:left;margin-right:10px;"></div>';
        $menuNameWithTags=ucfirst($menuNameI18n).'<span style="display:none">'.strtolower(replace_accents($menuNameI18n)).';'.$menuTag.';'.i18n($menuTag);
        $result.='<div class="divPosName" style="'.(($displayMode!='TXT')?"max-width: 155px !important;":"max-width: 180px !important;").'float: left;">'.$menuNameWithTags.'</span></div></a>'; 
        $result.='<div id="div'.$obj->name.'" style="'.$styleDiv.'" class="'.$class.'" onclick="'.$funcuntionFav.'" ></div></li>';
      }else{
        $classEl="Reports";
        if($menu['objectType']=='reportDirectInMenu'){
            $reportDirectInMenu=new Report($obj->idReport);
        }
        $idMenu=($menu['objectType']=='reportDirectInMenu')?$reportDirectInMenu->idReportCategory:$obj->idMenu;
        $objId=($menu['objectType']=='reportDirectInMenu')?$reportDirectInMenu->id:$obj->id;
        if($obj->name!='menuReports'){
          $funcOnClick="loadMenuReportDirect(".$idMenu.",".$objId.");showMenuBottomParam('Report','true')";
        }else{
          $classEl=substr($obj->name,4);
          $menuName = addslashes(i18n($obj->name));
          $funcOnClick="loadMenuBarItem('".$classEl."','".htmlEncode($menuName,'quotes')."','bar');showMenuBottomParam('".$classEl."','false')";
        }
        if($isFav->id==''){
          $mode='add';
          $class="menu__add__Fav";
          $styleDiv="display:none;";
        }else{
          $mode='remove';
          $class="menu__as__Fav";
        }
        $menu['objectType']=($menu['objectType']=='reportDirectInMenu')?'reportDirect':$menu['objectType'];
        $funcuntionFav="addRemoveFavMenuLeft('div".ucfirst($obj->name)."', '".$obj->name."','".$mode."','".$menu['objectType']."');";
        $styleDiv="display:none;";
        
        $class="menu__add__Fav";
        $result.='<li class="menu__item" role="menuitem" onmouseenter="checkClassForDisplay(this,\'div'.ucfirst($obj->name).'\',\'enter\');" onmouseleave="checkClassForDisplay(this,\'div'.ucfirst($obj->name).'\',\'leave\');">';
        $result.='<input type="hidden" id="reportFileMenu" value="'.$file.'"/>';
        $result.='<a class="menu__linkDirect" onclick="'.$funcOnClick.'" href="#" id="'.$obj->name.'" ><div class="icon'.$classEl.' iconSize16" style="'.$displayIcon.'position:relative;float:left;margin-right:10px;"></div>';
        $menuNameWithTags=ucfirst(i18n($obj->name)).'<span style="display:none">'.strtolower(replace_accents(ucfirst(i18n($obj->name))));
        $result.='<div class="divPosName" style="'.(($displayMode!='TXT')?"max-width: 155px !important;":"max-width: 180px !important;").'float: left;">'.$menuNameWithTags.'</div></a>';
        $result.='<div id="div'.ucfirst($obj->name).'" style="'.$styleDiv.'" class="'.$class.'" onclick="'.$funcuntionFav.'" ></div></li>';
      }
    }else if($menu['objectType']=='pluginNotInst'){
      $result.='<li class="menu__item" role="menuitem" >';
      $result.='<a class="menu__linkDirect" onclick="loadPluginView(\''.$objectID.'\');" href="#" id="'.$objectCode.'" ><div class="iconButtonDownload iconSize16" style="'.$displayIcon.'position:relative;float:left;margin-right:10px;"></div>';
      $result.='<div class="divPosName menuPluginToInstlal" style="'.(($displayMode!='TXT')?"max-width: 165px !important;":"max-width: 200px !important;").'float: left;">'.ucfirst($objName).'</div></a>';
      $result.='</li>';
    }else{
      if($menu['objectType']=='report' ){
        $idName=substr($obj->name,14);
      }else if($menu['objectType']=='reportSubMenu'){
        if($obj->name=='../tool/jsonPlanning')$idName='GanttPlan';
        else $idName=$obj->name;
      }
      $sub='submenu-'.$obj->id;
      $result.='<li class="menu__item" role="menuitem">';
      $result.='<a class="menu__link" data-submenu="'.$sub.'" aria-owns="'.$sub.'" href="#" id="'.(($menu['objectType']=='menu')?$obj->name:"rep".$idName).'">';
      if($menu['objectType']=='report' ){
        $iconName=$idName;
        switch ($idName){
        	case 'Plan':
        	  $iconName='Planning';
        	  break;
        	case 'Bill':
        	  $iconName='Financial';
        	  break;
      	    case 'Resources':
      	       $iconName='Resource';
      	       break;
        }
        $idName=$obj->name;
      }else if($menu['objectType']=='reportSubMenu'){
         if($idName=='GanttPlan')$iconName='Planning';
         else $iconName=$idName;
      }
      $result.='<div class="icon'.(($menu['objectType']=='menu')?substr($obj->name,3):$iconName).' iconSize16" style="'.$displayIcon.'position:relative;float:left;margin-right:10px;"></div>';   
      if ($menu['objectType']=='menu') {
        if(substr(i18n($obj->name),0,1)!='[') $navMenuName=i18n($obj->name);
        else $navMenuName=i18n('menu'.substr($obj->name,3));
      } else {
        $navMenuName=i18n($idName);
      }
      $result.='<div class="divPosName" style="'.(($displayMode!='TXT')?"max-width: 155px !important;":"max-width: 180px !important;").'float: left;">'.ucfirst($navMenuName).'</div></a>';
      $result.='<div id="currentDiv'.$obj->name.'" class="div__link" ></div></li>';
    }
    $old=$menu['level'];
    $idP=$obj->idParent;
  }
  $result.='</ul>';
  $result.='</div>';
  return $result;
}
  

?>

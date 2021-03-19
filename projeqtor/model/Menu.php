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
 * Menu defines list of items to present to users.
 */ 
require_once('_securityCheck.php');
class Menu extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $idMenu;
  public $type;
  public $sortOrder=0;
  public $menuClass;
  public $idle;
  
  public $_isNameTranslatable = true;
  public $_noHistory=true; // Will never save history for this object
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }
    
  // Will hide menu for disabled plugins
  public static function canDisplayMenu($menu) {
    $plgName=lcfirst(substr($menu,4));
    $listPlugin=Plugin::getLastVersionPluginList();
    if (!isset($listPlugin[$plgName])) return true;
    $plg=$listPlugin[$plgName];
    if ($plg->idle) return false;
    return true;
  }
  public function canDisplay() {
    return self::canDisplayMenu($this->name);
  }
  public static function getMenuNameFromPage($page) {
    if (substr($page,0,27)=='objectMain.php?objectClass=') {
      $class=substr($page,27);
      if (strpos($class,'&')>0) $class=substr($class,0,strpos($class,'&'));
      return $class;
    } else {
      $class=str_replace('ViewMain.php','',$page);
      $class=str_replace('Main.php','',$class);
      $class=str_replace('.php','',$class);
      $class=str_replace('../view/','',$class);
      $class=ucfirst($class);     
      return $class;
    }
  }
  
  public static function drawAllNewGuiMenus($defaultMenu, $historyTable, $idFavoriteRow,$isAnotherBar=false) {
    $isNotificationSystemActiv = isNotificationSystemActiv();
    $isLanguageActive=(Parameter::getGlobalParameter('displayLanguage')=='YES')?true:false;
    $customMenu = new MenuCustom();
    $obj=new Menu();
    $where=null;
    $menuList = array();
    if($defaultMenu == 'menuBarCustom'){
      $customMenuArray=$customMenu->getSqlElementsFromCriteria(array('idUser'=>getSessionUser()->id, 'idRow'=>$idFavoriteRow), false, null, 'sortOrder');
      // $where = "idUser=".getSessionUser()->id." and idRow != ".$idFavoriteRow;
      // $otherCustomArray = $customMenu->getSqlElementsFromCriteria(null, false, $where);
      $customArray= array();
      $reportArray=array();
      foreach ($customMenuArray as $custom){
        if(trim(strpos($custom->name, 'menu'))==''){
          $reportArray[$custom->sortOrder]=$custom->name;
        }else{
          $customArray[$custom->sortOrder]=$custom->name;
        }
        
      }
      $where = "name in ('".implode("','", $customArray)."')";
      $menuList=$obj->getSqlElementsFromCriteria(null, false, $where);
      if(!empty($reportArray)){
        $clause="name in ('".implode("','", $reportArray)."')";
        $report=new Report();
        $reportList=$report->getSqlElementsFromCriteria(null,false, $clause);
      }
      $menuListOrder = array();
      foreach ($customArray as $id=>$name){
        foreach ($menuList as $menu){
          if($menu->name == $name){
            $menuListOrder[$id]=$menu;
          }
        }
      }
      if(!empty($reportArray)){
        foreach ($reportArray as $id=>$name){
          foreach ($reportList as $report){
            if($report->name == $name){
              $menuListOrder[$id]=$report;
            }
          }
        }
      }
      $menuList=$menuListOrder;
      ksort($menuList);
      $customMenuArray=$customArray;
      
    }else if ($defaultMenu == 'menuBarRecent'){
      $customMenuArray=$customMenu->getSqlElementsFromCriteria(array('idUser'=>getSessionUser()->id), false, null, 'sortOrder');
      $customArray= array();
      foreach ($customMenuArray as $custom){
        array_push($customArray, $custom->name);
      }
      $customMenuArray=$customArray;
      
      $historyTable = explode(',', $historyTable);
      $reverseArray = array_reverse($historyTable);
      $where = ($reverseArray)?"name in ('".implode("','", $reverseArray)."')":"";
      $menuList=($where)?$obj->getSqlElementsFromCriteria(null, false, $where):array();
      $sortHistoryTable = array();
      foreach ($reverseArray as $name){
        foreach ($menuList as $menu){
          if($menu->name == $name){
            $sortHistoryTable[$menu->name] = $menu;
            break;
          }
        }
      }
      $menuList=$sortHistoryTable;
    }
    $pluginObjectClass='Menu';
    $lstPluginEvt=Plugin::getEventScripts('list',$pluginObjectClass);
    foreach ($lstPluginEvt as $script) {
      require $script; // execute code
    }
    //$lastType='';
    foreach ($menuList as $menu) {
      if(get_class($menu)=='Menu' or $defaultMenu == 'menuBarRecent'){
        if (! $isLanguageActive and $menu->name=="menuLanguage") { continue; }
        if (! $isNotificationSystemActiv and strpos($menu->name, "Notification")!==false) { continue; }
        if (! $menu->canDisplay() ) { continue;}
        if (securityCheckDisplayMenu($menu->id,substr($menu->name,4)) ) {
          Menu::drawNewGuiMenu($menu, $defaultMenu, $customMenuArray,false,$isAnotherBar);
          //$lastType=$menu->type;
        }
      }else{
        $menuTestRigthAcces = SqlElement::getSingleSqlElementFromCriteria('menu', array('name'=>'menuReports'));
        if (! $menuTestRigthAcces->canDisplay() ) { continue;}
        if (securityCheckDisplayMenu($menuTestRigthAcces->id,substr($menuTestRigthAcces->name,4)) ) {
          Menu::drawNewGuiMenu($menu, $defaultMenu, $reportArray,true);
        }
      }
    }
  }
  
  public static function drawNewGuiMenu($menu, $defaultMenu, $customMenuArray,$isReportMenu=false,$isAnotherBar=false) {
  	$drawMode=Parameter::getUserParameter('menuBarTopMode');
  	if(!$drawMode)$drawMode='ICONTXT';
  	$marginTop = 'margin-top: 3px;';
  	if($drawMode != 'ICON')$marginTop = 'margin-top: 7px;';
  	$style='width:auto;height:100%;padding:5px 10px 5px 10px !important;color: var(--color-dark);filter:unset !important;white-space:nowrap;';
    if($isReportMenu==true){
      $menuClass=' menuBarItem ';
      if (in_array($menu->name,$customMenuArray)) $menuClass.=' menuBarCustom';
      $class='Reports';
      echo '<div id="dndItem'.$menu->name.'" name="dndItem'.$menu->name.'" title="' .i18n($menu->name) . '" class="dojoDndItem itemBar" dndType="menuBar" style="float:left;'.$marginTop.'">';
      echo '<div class="'.$menuClass.'" style="'.$style.'" id="iconMenuBar'.$menu->name.'" ';
      echo 'oncontextmenu="event.preventDefault();hideReportFavoriteTooltip(0);';
      if($defaultMenu == 'menuBarRecent' and !in_array($menu->name,$customMenuArray) or ($defaultMenu == 'menuBarCustom')){
        echo 'showFavoriteTooltip(\''.$menu->name.'\');"';
      }else{
        echo '"';
      }
      echo ' onMouseLeave="hideFavoriteTooltip(0,\''.$menu->name.'\');"';
      echo 'onClick="loadMenuReportDirect(\''.$menu->idReportCategory.'\',\''.$menu->id.'\');refreshSelectedMenuLeft(\''.$menu->name.'\');showMenuBottomParam(\'' . $class .  '\',\'true\');">';
      Menu::drawIconMenuNewGui($drawMode, $class, $menu,true);
      $class=$menu->name;
      Menu::drawNewGuiDialogueAddRemoveFav($menu,$customMenuArray,$defaultMenu,$class,false,true);
      echo '</div>';
      echo '</div>';
    }else {
      $menuName=$menu->name;
      $menuClass=' menuBarItem '.$menu->menuClass;
      if (in_array($menu->name,$customMenuArray)) $menuClass.=' menuBarCustom';
      $idMenu=$menu->id;
      $class=substr($menuName,4);
      if ($menu->type=='item') {
      	echo '<div id="dndItem'.$menuName.'" name="dndItem'.$menuName.'" title="' .i18n($menuName) . '" class="dojoDndItem itemBar" dndType="menuBar" style="float:left;'.$marginTop.'">';
      	echo '<div class="'.$menuClass.'" style="'.$style.'" id="iconMenuBar'.$class.'" ';
      	echo 'onClick="hideReportFavoriteTooltip(0);loadMenuBarItem(\'' . $class .  '\',\'' . htmlEncode(i18n($menuName),'quotes') . '\',\'bar\');refreshSelectedMenuLeft(\''.$menuName.'\');showMenuBottomParam(\'' . $class .  '\',\'false\');"';
      	echo 'oncontextmenu="event.preventDefault();hideReportFavoriteTooltip(0);';
    	if($defaultMenu == 'menuBarRecent' and !in_array($menuName,$customMenuArray) or ($defaultMenu == 'menuBarCustom')){
          echo 'showFavoriteTooltip(\''.$class.'\');"';
    	}else{
    	    echo '"';
    	}
    	if ($menuName=='menuReports' and isHtml5() ) {
        echo ' onMouseEnter="showReportFavoriteTooltip();hideFavoriteTooltip(0,\''.$class.'\');"';
    	    echo ' onMouseLeave="hideReportFavoriteTooltip(0);hideFavoriteTooltip(0,\''.$class.'\');"';
    	}else{
    	    echo ' onMouseLeave="hideFavoriteTooltip(0,\''.$class.'\');"';
    	}
    	echo '>';
    	Menu::drawIconMenuNewGui($drawMode, $class, $menu);        
        Menu::drawNewGuiDialogueAddRemoveFav($menu,$customMenuArray,$defaultMenu,$class,$menuName,false,$isAnotherBar);
        echo '</div>';
        echo '</div>'; 
      }else if ($menu->type=='plugin') {
        echo '<div id="dndItem'.$menuName.'" name="dndItem'.$menuName.'" title="' .i18n($menuName) . '" class="dojoDndItem itemBar" dndType="menuBar" style="float:left;'.$marginTop.'">';
        echo '<div class="'.$menuClass.'" style="'.$style.'" id="iconMenuBar'.$class.'"';
        echo 'oncontextmenu="event.preventDefault();hideReportFavoriteTooltip(0);showFavoriteTooltip(\''.$class.'\');"';
        echo ' onMouseLeave="hideFavoriteTooltip(0,\''.$class.'\');"';
        echo 'onClick="loadMenuBarPlugin(\'' . $class .  '\',\'' . htmlEncode(i18n($menuName),'quotes') . '\',\'bar\');refreshSelectedMenuLeft(\''.$menuName.'\');showMenuBottomParam(\'' . $class .  '\',\'false\');">';
        Menu::drawIconMenuNewGui($drawMode, $class, $menu);
        Menu::drawNewGuiDialogueAddRemoveFav($menu,$customMenuArray,$defaultMenu,$class);
        echo '</div>';
        echo '</div>';
      }else if ($menu->type=='object') { 
        if (securityCheckDisplayMenu($idMenu, $class)) {
        	echo '<div id="dndItem'.$menuName.'" name="dndItem'.$menuName.'" title="' .i18n('menu'.$class) . '" class="dojoDndItem itemBar" dndType="menuBar" style="float:left;'.$marginTop.'">';
        	echo '<div class="'.$menuClass.'" style="'.$style.'" id="iconMenuBar'.$class.'" ';
            echo 'oncontextmenu="event.preventDefault();hideReportFavoriteTooltip(0);';
    		if($defaultMenu == 'menuBarRecent' and !in_array($menu->name,$customMenuArray) or ($defaultMenu == 'menuBarCustom')){
    		  echo 'showFavoriteTooltip(\''.$class.'\');"';
    		}else{
    		  echo '"';
    		}
        	echo ' onMouseLeave="hideFavoriteTooltip(0,\''.$class.'\');"';
        	echo 'onClick="loadMenuBarObject(\'' . $class .  '\',\'' . htmlEncode(i18n($menuName),'quotes') . '\',\'bar\');refreshSelectedMenuLeft(\''.$menuName.'\');showMenuBottomParam(\'' . $class .  '\',\'true\');">';
             Menu::drawIconMenuNewGui($drawMode, $class, $menu);
        	Menu::drawNewGuiDialogueAddRemoveFav($menu,$customMenuArray,$defaultMenu,$class);
        	echo '</div>';
        	echo '</div>';
        }
      }
    }
  } 

      
      public static function drawIconMenuNewGui($drawMode,$class,$menu,$isReport=false){
        if($drawMode=='ICON'){
          if($isReport==false and $menu->type=='plugin'){
            echo  '<img src="../view/css/images/icon'.$class.'22.png" />';
          }else{
            echo  '<div class="icon'.$class.'22 icon'.$class.' iconSize22 imageColorNewGui" style="width:22px;height:22px"></div>';
          }
        }else if($drawMode=='TXT'){
          echo  i18n($menu->name);
        }else if($drawMode=='ICONTXT'){
          echo  '<table><tr>';
          if($isReport==false and $menu->type=='plugin'){
            echo '<td><img src="../view/css/images/icon'.$class.'22.png" /></td>';
          }else{
            echo  '<td><div class="icon'.$class.'16 icon'.$class.' iconSize16 imageColorNewGui" style="width:16px;height:16px"></div></td>';
          }
          echo  '<td style="padding-left:5px;">'.i18n($menu->name).'</td>';
          echo  '</tr></table>';
        }
      }
      
      public static function drawNewGuiDialogueAddRemoveFav($menu,$customMenuArray,$defaultMenu,$class,$menuName=false,$isReport=false,$isAnotherBar=false){
        $currentBar=Parameter::getUserParameter('defaultMenu');
        if  (($isReport==false and $menu->type=='item' and $menuName=='menuReports' and isHtml5()) and (($isAnotherBar and $currentBar=='menuBarCustom') or !$isAnotherBar)) {
          echo '<div class="comboButtonInvisible" dojoType="dijit.form.DropDownButton" id="listFavoriteReports" name="listFavoriteReports" style="position:absolute;top:22px;left:0px;height: 0px; overflow: hidden; ">';
          echo '<div dojoType="dijit.TooltipDialog" id="favoriteReports" style="position:absolute;"href="../tool/refreshFavoriteReportList.php" onMouseEnter="clearTimeout(closeFavoriteReportsTimeout);"
              onMouseLeave="hideReportFavoriteTooltip(200)" onDownloadEnd="checkEmptyReportFavoriteTooltip()">';
          Favorite::drawReportList();
          echo ' </div></div>';
        }
        
        if($defaultMenu == 'menuBarRecent' and !in_array($menu->name,$customMenuArray) or ($defaultMenu == 'menuBarCustom')){
          echo '<div class="comboButtonInvisible" dojoType="dijit.form.DropDownButton"id="addFavorite'.$class.'" name="addFavorite'.$class.'" style="position:absolute;top:22px;left:0px;height: 0px; overflow: hidden; ">';
          echo '<div dojoType="dijit.TooltipDialog" id="dialogFavorite'.$class.'" style="cursor:pointer;"onMouseEnter="clearTimeout(closeFavoriteTimeout);"onMouseLeave="hideFavoriteTooltip(200,\''.$class.'\')"';
          
          if (!in_array($menu->name,$customMenuArray)){
            $mode="add";
            $classAttr="menuBar_add_Fav";
            $lib=i18n('customMenuAdd');
          }else{
            $mode="remove";
            $classAttr="menuBar_remove_Fav";
            $lib=i18n('customMenuRemove');
          }
          echo 'onClick="addRemoveFavMenuLeft(\'div'.(($isReport==true)?ucfirst($menu->name):$menu->name).'\', \''.$menu->name.'\',\''.$mode.'\',\''.(($isReport==true)?"reportDirect":"menu").'\');">';
          echo'<div class="'.$classAttr.'" style="white-space:nowrap;padding-right:10px;">'.$lib.'</div>';
          echo '</div></div>';
        }
      }

}
?>
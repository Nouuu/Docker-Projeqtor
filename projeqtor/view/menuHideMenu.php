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
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/menuHideMenu.php');
  
  $menuNextIsFirst=true; // is next element fisrt of a group
  $level=0;
  $menuLevel=array('0'=>'0');
  
  $obj=new Menu();
  $sortOrder = 'sortOrder asc';
  $menuList=$obj->getSqlElementsFromCriteria(null, false,null,$sortOrder,true);
  $isLanguageActive=(Parameter::getGlobalParameter('displayLanguage')=='YES')?true:false;
  // Clean empty parents
  foreach (array_reverse($menuList,true) as $idMenu=>$menu) {
    if ($menu->type=='menu') {
      $found=false;
      foreach ($menuList as $child) {
        if ($child->idMenu==$menu->id and Module::isMenuActive($child->name)) {
          $found=true;
          break;
        } 
      }
      if (! $found) {
        unset($menuList[$idMenu]);
      }
    }
  }
  
  foreach ($menuList as $menu) {
    if (!isNotificationSystemActiv() and strpos($menu->name, "Notification")!==false) { continue; }
    if (! $menu->canDisplay() ) { continue;}
    if (!$isLanguageActive and $menu->name=="menuLanguage") { continue; }
    if (!Module::isMenuActive($menu->name)) {continue;}
    if ($level>0 and securityCheckDisplayMenu($menu->id,substr($menu->name,4))) {
      while ($level>0 and $menu->idMenu!= $menuLevel[$level]) {
        drawMenuIconCloseChildren();
      }
    }
    if ($menu->type=='class') {
      drawMenuItemClass2($menu->idMenu,$menu->id,$menu->name);
    } else if ($menu->type=='menu') {
      drawMenuIcon($menu->idMenu,$menu->id,$menu->name,'menu', true);
    } else if ($menu->type=='item') {
      drawMenuIcon($menu->idMenu,$menu->id,$menu->name,'item', false);
    } else if ($menu->type=='object') {
      drawMenuIcon($menu->idMenu,$menu->id,$menu->name,'object', false);
    }
  }
  while ($level>0) {
    drawMenuIconCloseChildren();
  }
  
//Functions  
  function drawMenuIcon($idMenuParent,$idMenu,$menuName,$type,$hasChildren=false,$force=false, $class=null){
  global  $menuNextIsFirst, $level, $menuLevel;
  $menuNameI18n = i18n($menuName);
  $menuName2 = addslashes(i18n($menuName));
  $paramIconSize=Parameter::getUserParameter('paramIconSize');
  if (!$paramIconSize) $paramIconSize=22;
  $isUnderMenu = false;
 // $paramIconSize = 16;
    $paramIconSize2 = $paramIconSize+13;
    $paramIconSize3 = 182-$paramIconSize;
    
    if($paramIconSize==16){
      $marginTopUnderMenu = -20;
      $marginRight = -1 ;
      $marginRight2 = -1 ;
      $marginLeft = 20;
      $marginTop = 4.5;
      $marginTopUnder = $marginTop;
      $height = 16;
    } 
    elseif($paramIconSize == 22) {
      $marginTopUnderMenu = -26;
      $marginRight = -10 ;
      $marginRight2 = -10 ;
      $marginLeft = 28;
      $marginTop = 3;
      $marginTopUnder = $marginTop;
      $height = 22;
    } elseif($paramIconSize == 32){
      $marginTopUnderMenu = -36;
      $marginTopUnder = -25;
      $marginRight = -18 ;
      $marginRight2 = 5 ;
      $marginLeft = 45;
      $marginTop = 8;
      $height = 32;
    }

    $menu=substr($menuName,4);
    if (securityCheckDisplayMenu($idMenu,$menu) or $force) {
      if (! $menuNextIsFirst) {
        // display something after menu
//        echo"<div id='Menu$idMenu' >";
//        echo formatSmallButton('ArrowRight');
//        echo"</div>";
      }
      $name= ucfirst($menu);
      if ($class) {
        $icon=$class;
      } else {
        $icon=$name;
      }
      $indent=substr("                               ",0,2*$level);
     if($idMenuParent == 0){
       //echo  "$level$indent<div id='Menu$idMenu' name='$icon' parent='$idMenuParent'>"."\n";
       if (! $hasChildren and $type=='item') {
        echo  $indent.'<div title="'.$menuNameI18n.'" onclick="loadMenuBarItem(\'' . $menu .  '\',\'' . htmlEncode($menuName2,'quotes') . '\',\'bar\');"   id="Menu'.$idMenu.'" data-dojo-attach-point="rowNode" class="" role="presentation" title="" style="margin-left: -4px; margin-top:5px; margin-bottom:5px; position:relative;">
               <span role="presentation" class="dijitInline dijitIcon dijitTreeIcon icon'.$icon.$paramIconSize.' icon'.$icon.' iconSize'.$paramIconSize.'" data-dojo-attach-point="iconNode"> </span>'."\n";
       }elseif (! $hasChildren and $type=='object'){
         echo  $indent.'<div title="'.$menuNameI18n.'" onclick="loadMenuBarObject(\'' . $menu .  '\',\'' . htmlEncode($menuName2,'bar') . '\',\'bar\');"   id="Menu'.$idMenu.'" data-dojo-attach-point="rowNode" class="" role="presentation" title="" style="margin-left: -4px; margin-top:5px; margin-bottom:5px; position:relative;">
               <span role="presentation" class="dijitInline dijitIcon dijitTreeIcon icon'.$icon.$paramIconSize.' icon'.$icon.' iconSize'.$paramIconSize.'" data-dojo-attach-point="iconNode"> </span>'."\n";
       } else {
         echo  $indent.'<div id="Menu'.$idMenu.'" data-dojo-attach-point="rowNode" class="" role="presentation" title="" style="margin-left: -4px; margin-top:5px; margin-bottom:5px; position:relative;width:44px position:relative;" onMouseEnter="displayMenu(\''.$idMenu.'\');" onMouseLeave="hideMenu(\''.$idMenu.'\',300);" >
               <span role="presentation" class="dijitInline dijitIcon dijitTreeIcon icon'.$icon.$paramIconSize.' icon'.$icon.' iconSize'.$paramIconSize.'" data-dojo-attach-point="iconNode">  </span>'."\n";
               echo' <a style="left:2px; width:8px; float:right; position:relative; top:'.$marginTop.'px;"> '.formatIcon('ArrowShowHideMenu',16).'  </a>';
       }
     }else{    
       //echo  "$level$indent<div id='Menu$idMenu' name='$icon' parent='$idMenuParent'>"."\n";
       if ($type=='item') {
        echo $indent.'<div onMouseEnter="hidePreviewUnderMenu(\'' . $idMenuParent .  '\');" onclick="loadMenuBarItem(\'' . $menu .  '\',\'' . htmlEncode($menuName2,'quotes') . '\',\'bar\');hideUnderMenu(\''.$idMenuParent.'\');" data-dojo-attach-point="rowNode" class="dijitTreeRow2" role="presentation" title="" style="padding-left: 5px; margin-top:0px; margin-bottom:0px;">
              <span  style="vertical-align: middle;display: table-cell;min-width:210px;  margin-top:3px; height:'.$height.'px;" role="presentation" class="dijitInline dijitIcon dijitTreeIcon icon'.$icon.$paramIconSize.' icon'.$icon.' iconSize'.$paramIconSize.'" data-dojo-attach-point="iconNode"> <div style="float:left; max-width:210px; margin-left:'.$marginLeft.'px;vertical-align: middle;display: table-cell;"> '.$menuNameI18n.' </div>   </span>';
       }elseif ($type=='object'){
         echo $indent.'<div onMouseEnter="hidePreviewUnderMenu(\'' . $idMenuParent .  '\');" onclick="loadMenuBarObject(\'' . $menu .  '\',\'' . htmlEncode( $menuName2 ,'bar') . '\',\'bar\');hideUnderMenu(\''.$idMenuParent.'\');" data-dojo-attach-point="rowNode" class="dijitTreeRow2" role="presentation" title="" style="padding-left: 5px; margin-top:0px;margin-bottom:0px;">
              <span style="vertical-align: middle;display: table-cell;min-width:210px; margin-top:3px; height:'.$height.'px;" role="presentation" class="dijitInline dijitIcon dijitTreeIcon icon'.$icon.$paramIconSize.' icon'.$icon.' iconSize'.$paramIconSize.'" data-dojo-attach-point="iconNode"><div style="float:left; max-width:210px; margin-left:'.$marginLeft.'px;vertical-align: middle;display: table-cell;"> '.$menuNameI18n.' </div>  </span>';     
       }elseif($type=='plugin'){
         echo $indent.'<div  onclick="loadMenuBarPlugin(\'' . $menu .  '\',\'' . htmlEncode($menuName2,'quotes') . '\',\'bar\');hideUnderMenu(\''.$idMenuParent.'\');" data-dojo-attach-point="rowNode" class="dijitTreeRow2" role="presentation" title="" style="padding-left: 5px; margin-top:0px;margin-bottom:0px;">
              <span style="vertical-align: middle;display: table-cell;min-width:210px;  margin-top:3px; height:'.$height.'px;" role="presentation" class="dijitInline dijitIcon dijitTreeIcon icon'.$icon.$paramIconSize.' icon'.$icon.' iconSize'.$paramIconSize.'" data-dojo-attach-point="iconNode"><div style="float:left; max-width:210px; margin-left:'.$marginLeft.'px;vertical-align: middle;display: table-cell;"> '.$menuNameI18n.' </div> </span>';
       }else{
         //Under menu case
         echo $indent.'<div onMouseEnter="displayUnderMenu(\''.$idMenu.'\',\''.$idMenuParent.'\');" onMouseLeave="hideMenu(\''.$idMenu.'\',300);"  data-dojo-attach-point="rowNode" class="dijitTreeRow2" role="presentation" title="" style="padding-left: 5px; margin-top:0px; margin-bottom:0px;">
              <span style="min-width:210px; margin-top:3px; height:'.$height.'px;" role="presentation" class="dijitInline dijitIcon dijitTreeIcon icon'.$icon.$paramIconSize.' icon'.$icon.' iconSize'.$paramIconSize.'" data-dojo-attach-point="iconNode"> <div style="float:left; max-width:210px; margin-left:'.$marginLeft.'px;"> '.$menuNameI18n.' </div> </span>';
         echo' <a style="float:right; width:8px; position:relative; top:'.$marginTop.'px;"> '.formatIcon('ArrowShowHideMenu',16).'  </a>';
         $isUnderMenu = true;
       }
     }
     if ($hasChildren) {
      $menuNextIsFirst=true;
      if(!$isUnderMenu){
        echo "$indent<div id='UnderMenu$idMenu'  class='dijitAccordionTitle2 reportTableColumnHeader2 largeReportHeader2' style='display:none;  font-size:100%; position:absolute; left:".$paramIconSize2."px; top:0px; width:230px;' >";
        echo '<div style="margin-left:5px;padding-top:3px;margin-bottom:8px;"> <span  style="min-width:210px;" role="presentation" class=" dijitTreeRow3" >'.$menuNameI18n.'</span> </div>';
      }else{
        // to do for all size
        echo "$indent<div id='UnderMenu$idMenu' class='hideUndermenu$idMenuParent dijitAccordionTitle2 reportTableColumnHeader2 largeReportHeader2' style='display:none;font-size:100%; overflow-y:auto; position:absolute;margin-top:".$marginTopUnderMenu."px;left:238px; width:230px;' >";
      }
      $level+=1;
      $menuLevel[$level]=$idMenu;
     }else {
      echo "$indent</div>";
     }
   }
 } 

function drawMenuItemClass2($idMenuParent,$idMenu, $menuName) {
   $class=substr($menuName,4);
   if (securityCheckDisplayMenu($idMenu, $class)) {
     drawMenuIcon($idMenuParent,$idMenu,$class, 'menu', true);
     drawMenuIcon($idMenuParent,$idMenu, 'All' . $class, 'class', false, true, $class);
   }
 }
 
 function drawMenuIconCloseChildren() {
   global  $menuNextIsFirst, $level, $menuLevel;
   echo " </div></div>"."\n";
   unset($menuLevel[$level]);
   $level-=1;
   $menuNextIsFirst=false;
 }
 
?>
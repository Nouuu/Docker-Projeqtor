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

$screen=(RequestHandler::isCodeSet('currentScreen'))?RequestHandler::getValue('currentScreen'):'';
$isObject=(RequestHandler::isCodeSet('isObject'))?RequestHandler::getValue('isObject'):'false';
$isLanguageActive=(Parameter::getGlobalParameter('displayLanguage')=='YES')?true:false;
$displayMode=Parameter::getUserParameter('menuLeftDisplayMode');
$displayIcon=($displayMode=='TXT')?"display:none;":"display:block;";
$result='';
$allMenu=array();
$allMenuSort=array();

if($isObject=='true' and $screen!=''){
  $obj=new $screen();
  $menu= new Menu();
  $lstParam=array();
  $sortMenu=array();
  $lstId=array();
  foreach ( $obj as $key=>$val){
    if(substr($key, 0,2)=='id' and $key!='idle' and $key!='idleDateTime' and $key!='id' and $key!='id'.$screen and $key!='idStatus'){
      if(strpos($key,'idContext')!==false){
        $lstParam[]="'menuContext'";
        $sortMenu[]="menuContext";
      }else{
        $lstParam[]="'menu".substr($key,2)."'";
        $sortMenu[]="menu".substr($key,2);
      }
    }
  }
  $lstString=implode(',', $lstParam);
  $sortMenu=array_flip($sortMenu);
  if (! $lstString) $lstString="''";
  $clause="name in ($lstString) and (level not in ('','Project'))";
  $allMenu=$menu->getSqlElementsFromCriteria(null,null,$clause);
  foreach ($allMenu as $menu){
    if(array_key_exists($menu->name,$sortMenu)){
      $keySort=$sortMenu[$menu->name];
      $allMenuSort[$keySort]=$menu;
    }
  }
  ksort($allMenuSort);
}else{
  switch ($screen){
  	case 'Today':
  	  break;
  	case 'Planning':
  	    break;
  	case  'PortfolioPlanning':
  	    break;
    case 'ResourcePlanning': 
        break;
    case  'GlobalPlanning':
        break;
    case  'HierarchicalBudget':
        break;
    case  'GanttClientContract' :
        break;
    case 'GanttSupplierContract':
        break;
    case  'Imputation':
        break;
    case  'Diary':
        break;
    case  'ActivityStream':
        break;
    case  'ImportData':
        break;
    case  'Reports':
        break;
    case  'Absence':
        break;
    case  'PlannedWorkManual' :
        break;
    case 'ConsultationPlannedWorkManual':
        break;
    case  'ImputationValidation':
        break;
    case 'ConsultationValidation':
        break;
    case  'AutoSendReport':
        break;
    case  'DataCloning':
        break;
    case 'DataCloningParameter': 
      break;
    case 'VersionsPlanning':   
      break;
    case 'VersionsComponentPlanning':
      break;
    case 'UserParameter': 
      break;
    case 'ProjectParameter': 
      break;
    case 'GlobalParameter': 
      break;
    case 'Habilitation': 
      break;
    case 'HabilitationReport': 
      break;
    case 'HabilitationOther': 
      break;
    case 'AccessRight': 
      break;
    case 'AccessRightNoProject': 
      break;
    case 'Admin': 
      break;
    case 'Plugin':  
      break;
    case 'PluginManagement': 
      break;
    case 'Calendar': 
      break;
    case 'Gallery': 
      break;
    case 'DashboardTicket': 
      break;
    case 'DashboardRequirement': 
       break;
    case "LeaveCalendar": 
      break;
    case "LeavesSystemHabilitation": 
      break;
    case "DashboardEmployeeManager" :
       break;
    case "Module" :
       break;
    case "Kanban": 
      break;
    default:
      break;
  }
}
if(empty($allMenuSort)){
  $result.='<div class="noMenuToDisplay" style="font-style:italic;">'.i18n("explainParameterMenu").'</div>';
}else{
  $result.='<ul id="parameterMenu" class="paramMenuBottom">';
  $result.='<input id="menuParamDisplay" value="'.$screen.'" hidden>';
  foreach ($allMenuSort as $menu){
          $unset=false;
          if (!isNotificationSystemActiv() and strpos($menu->name, "Notification")!==false) $unset=true; 
          if (! $menu->canDisplay() )  $unset=true;
          if (!$isLanguageActive and $menu->name=="menuLanguage")  $unset=true;
          if (!Module::isMenuActive($menu->name))  $unset=true;
          if (!securityCheckDisplayMenu($menu->id,substr($menu->name,4))) $unset=true;
          if($unset==true)continue;
          
          $menuName=$menu->name;
          $menuNameI18n = i18n($menu->name);
          $menuName2 = addslashes(i18n($menuName));
          $classEl=substr($menuName,4);
          $funcOnClick="refreshSelectedMenuLeft('$menu->name');";
          if($menu->type=='item'){
            $funcOnClick.="loadMenuBarItem('".$classEl."','".htmlEncode($menuName2,'quotes')."','bar');";
          }else{
            $funcOnClick.="loadMenuBarObject('".$classEl."','".htmlEncode($menuName2,'bar')."','bar');";
          }
          $result.='<li class="menu__item" role="menuitem" >';
          $result.='<a class="menu__linkDirect" onclick="'.$funcOnClick.'" href="#" id="'.$menu->name.'Param" ><div class="icon'.$classEl.' iconSize16" style="'.$displayIcon.'position:relative;float:left;margin-right:10px;"></div>';
          $result.='<div class="divPosName" style="'.(($displayMode!='TXT')?"max-width: 155px !important;":"max-width: 180px !important;").'float: left;">'.ucfirst($menuNameI18n).'</div></a>';
          $result.='</li>';
  }
  $result.='</ul>';
}


echo $result;
?>
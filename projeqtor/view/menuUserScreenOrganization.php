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

include_once("../tool/projeqtor.php");
include_once '../tool/formatter.php';
$iconSize=32;
$showMenuBar=Parameter::getUserParameter('paramShowMenuBar');
$showMenuBar='YES';
if (! $iconSize or $showMenuBar=='NO') $iconSize=16;
//Param
// TODO : Verify that this code is nonsense
// if(sessionValueExists("paramScreen")){
//   if(getSessionValue("paramScreen")=='switch') {
//   	  Parameter::storeUserParameter("paramScreen", 'top');
  	  
//   }
//   setSessionValue("paramScreen", "0");
// }
$objectExist="";
 if(RequestHandler::isCodeSet('objectExist')){
   $objectExist=RequestHandler::getValue('objectExist');
 }else if (Parameter::getUserParameter('startPage')){
   $objectExist='true';
   $tabPage=array( "today.php", "startGuide.php", "diaryMain.php","imputationMain.php","dashBoardTicketMain.php");
     foreach ($tabPage as $page){
       if(Parameter::getUserParameter('startPage')== $page){
          $objectExist='false';
       }
     }
 }
$paramScreen=Parameter::getUserParameter('paramScreen');
$paramRightDiv=Parameter::getUserParameter('paramRightDiv');
$paramObjectDetail=Parameter::getUserParameter('paramLayoutObjectDetail');
if(RequestHandler::isCodeSet('currentScreen')){
  $currentSceen=RequestHandler::getValue('currentScreen');
}

if(RequestHandler::isCodeSet('paramActiveGlobal')){
  $activModeStream=RequestHandler::getValue('paramActiveGlobal');
  Parameter::storeUserParameter('modeActiveStreamGlobal', $activModeStream);
  // Purge parameters
  $par=new Parameter();
  $clause="idUser=".getCurrentUserId()." and (parameterCode like 'contentPaneRightDetailDivHeight%' or parameterCode like 'contentPaneRightDetailDivWidth%')";
  $res=$par->purge($clause); // Purge parameters
  // Purge Session
  foreach (getSessionValue('userParamatersArray') as $code=>$val) {
    if (substr($code,0,25)=='contentPaneRightDetailDiv') {
      setSessionTableValue('userParamatersArray', $code,'');
    }
    
  }
}else{
  $activModeStream=Parameter::getUserParameter('modeActiveStreamGlobal');
}

if($paramRightDiv=='trailing'){
  $globalActivityStreamSize=getDefaultLayoutSize('contentPaneRightDetailDivWidth');
}else{
  $globalActivityStreamSize=getDefaultLayoutSize('contentPaneRightDetailDivHeight');
}

?>

<div id="mainDivMenu" class="container" >
 <input type="hidden" id="objectExist" name="objectExist" value="<?php echo $objectExist;?>" />
 <table width="100%">
    <tr height="<?php echo $iconSize+8; ?>px">  
      <td width="<?php echo (isIE())?37:35;?>px" > 
        <div id="changeLayout" class="pseudoButton"  style="height:28px; position:relative;top:-5px; z-index:30; width:30px;
        <?php if( $paramScreen=='switch'){echo 'Background:#D1D1D1;border-radius:4px;cursor:not-allowed;';}?>" title="<?php echo i18n("buttonSwitchedMode");?>"
         onclick="<?php if($paramScreen!='switch') {echo 'switchModeLayout(\'switch\')';}?>">
          <table >
            <tr>
              <td style="width:28x;text-align:center">
                <div class="iconChangeLayout22 iconChangeLayout iconSize22" style="position:absolute;top:2px;left:3px" ></div>
              </td>
            </tr>
          </table>    
       </div>
      </td>
      <td width="<?php echo (isIE())?37:35;?>px"  > 
        <div id="horizontalLayout"  class="pseudoButton"  style="height:28px; position:relative;top:-5px; z-index:30; width:30px; right:0px;
        <?php if($paramScreen=='top'){echo 'Background:#D1D1D1;border-radius:4px;cursor:not-allowed;';}?>" title="<?php echo i18n("showListTop");?>"
        onclick="<?php if($paramScreen!='top' ){echo 'switchModeLayout(\'top\');';}?>">
          <table >
            <tr>
              <td style="width:28x;text-align:center">
                <div class="horizontalLayoutClass iconSize22" style="position:absolute;top:2px;left:3px" ></div>
              </td>
            </tr>
          </table>    
       </div>
      </td>
      <td width="<?php echo (isIE())?37:35;?>px"  > 
        <div id="verticalLayout" lass="pseudoButton"  style="height:28px; position:relative;top:-5px; z-index:30; width:30px; right:0px;
        <?php if($paramScreen=='left'){echo 'Background:#D1D1D1;border-radius:4px;cursor:not-allowed;';}?>" title="<?php echo i18n("showListLeft"); ?>"
        onclick="<?php if($paramScreen!='left' ){echo 'switchModeLayout(\'left\');';}?>">
          <table >
            <tr>
              <td style="width:28x;text-align:center">
                <div class="verticalLayoutClass iconSize22" style="position:absolute;top:2px;left:3px" ></div>
              </td>
            </tr>
          </table>    
       </div>
      </td>
    </tr>
    <tr height="<?php echo $iconSize+8; ?>px">  
      <td width="<?php echo (isIE())?37:35;?>px"> 
        <div id="layoutList" class="pseudoButton"  style="height:28px; position:relative;top:-5px; z-index:30; width:30px; right:0px;
        <?php if($paramObjectDetail=='col'){echo 'Background:#D1D1D1;border-radius:4px;cursor:not-allowed;';}?>" title="<?php echo i18n("sectionMode");?>"
        onclick="<?php if($paramObjectDetail!='col'){echo 'switchModeLayout(\'col\');';}?>">
          <table >
            <tr>
              <td style="width:28x;text-align:center">
                <div class="iconLayoutList22 iconLayoutList iconSize22" style="position:absolute;top:2px;left:4px" ></div>
              </td>
            </tr>
          </table>    
       </div>
      </td>
      <?php if (! isIE()) {?>
      <td width="<?php echo (isIE())?37:35;?>px"  > 
        <div id="layoutTab" class="pseudoButton"   style="height:28px; position:relative;top:-5px; z-index:30; width:30px; right:0px;
        <?php if($paramObjectDetail=='tab'){echo 'Background:#D1D1D1;border-radius:4px;cursor:not-allowed;';}?>" 
        title="<?php echo i18n("tabularMode");?>"
        onclick="<?php if($paramObjectDetail!='tab'){echo 'switchModeLayout(\'tab\');';}?>">
          <table >
            <tr>
              <td style="width:28x;text-align:center">
                <div class="iconLayoutTab22 iconLayoutTab iconSize22 " style="position:absolute;top:2px;left:4px" ></div>
              </td>
            </tr>
          </table>    
       </div>
      </td>
      <?php }?>
      <td width="<?php echo (isIE())?37:35;?>px"  > 
      <?php if (Module::isModuleActive('moduleActivityStream')) {?>
        <div id="hideStreamButtonGlobal" class="pseudoButton"  style="height:28px; position:relative;top:-5px; z-index:30; width:30px; right:0px;" 
        onclick="hideStreamMode('<?php echo ($activModeStream!='true')?'true':'false';?>','<?php echo $paramRightDiv?>','<?php echo $globalActivityStreamSize;?>',true);">
          <table >
            <tr>
              <td >
              <?php if( $activModeStream!='true'){ ;?>
                <div class="iconActivityStream22 iconActivityStream iconSize22 " style="position:absolute;top:2px;left:3px" title="<?php echo i18n("showAllActivityStream")."\n".i18n("resetActivityStreamParams");?>"></div>
              <?php }else {?>
                <div class="iconActivityStreamClose22 iconActivityStreamClose iconSize22 " style="position:absolute;top:2px;left:3px" title="<?php echo i18n("hideAllActivityStream")."\n".i18n("resetActivityStreamParams");?>"></div>
              <?php }?>
              </td>
            </tr>
          </table>    
       </div>
       <?php }?>
      </td>
    </tr>
    <tr>
    <?php if (! isNewGui()) {?>
    <td width="<?php echo (isIE())?37:35;?>px">
	   <div id="hideMenuBarBottom" class="pseudoButton" onClick="hideMenuBarShowMode();" title="<?php echo i18n('buttonShowLeftMenu')?>" style="height:28px; position:relative;top:-5px; z-index:30; width:30px; right:0px;"  >
	     <?php if (! isset($showModuleScreen)) {?>
		  <table>
          <tr>
            <td style="width:28x;text-align:center">
              <div class="iconHideStreamLeft22 iconHideStreamLeft iconSize22 " style="position:absolute;top:2px;left:3px" ></div>
            </td>
          </tr>
        </table>  
       <?php }?>  
   </div>
    </td>
    <td width="<?php echo (isIE())?37:35;?>px">   
      <div id="hideMenuBarShowButtonTop" class="pseudoButton"   onClick="hideMenuBarShowModeTop();" title="<?php echo i18n('buttonShowTopMenu')?>" style="height:28px; position:relative;top:-5px; z-index:30; width:30px; right:0px;"  >
          <table >
            <tr>
              <td style="width:28x;text-align:center">
                <div class="iconHideStreamTop22 iconHideStreamTop iconSize22 " style="position:absolute;top:2px;left:3px" ></div>
              </td>
            </tr>
          </table>    
	  </div>
    </td>
    <?php }?>  
    <?php if (! isIE()) {?>
    <td width="<?php echo (isIE())?37:35;?>px;" style="<?php echo (isNewGui())?'padding-left: 4px;':'';?>"> 
      <div  class="pseudoButton<?php if (!isNewGui()) echo 'FullScreen';?>" style="height:28px; position:relative;top:0px; z-index:30; width:30px; right:0px;" onclick="toggleFullScreen()" >
        <table>
          <tr>
            <td style="width:28px" >
              <?php echo formatIcon('FullScreen', 22,i18n("fullScreen"));?>
            </td>
          </tr>
        </table>
      </div>
    </td>
     <?php }?> 
    
    </tr>
  </table>
</div>
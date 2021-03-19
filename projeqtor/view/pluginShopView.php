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
 * Management of PlugIns
 */
  require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
  scriptLog('   ->/view/pluginManagement.php');
  $user=getSessionUser();
  $idPlugin=RequestHandler::getValue('objectId');
  $urlPlugins = "http://projeqtor.org/admin/getPlugins.php";
  $currentVersion=null;
  $getYesNo=Parameter::getGlobalParameter('getVersion');
  if ($getYesNo=='NO') {
    echo "Cannot access remote information for the plugin";
    exit;
  }
  if (ini_get('allow_url_fopen')) {
    enableCatchErrors();
    enableSilentErrors();
    $currentVersion=file_get_contents($urlPlugins);
    disableCatchErrors();
    disableSilentErrors();
  }
  $json = file_get_contents($urlPlugins);
  $object = json_decode($json);
  $plugins=$object->items;
  foreach ($plugins as $val){
    if($val->id==$idPlugin){
      $obj=$val;
      break;
    }
  }
  
  $userLang = getSessionValue('currentLocale');
  $lang = "en";
  if(substr($userLang,0,2)=="fr")$lang="fr";
  $pluginName=($lang=='fr')?$obj->nameFr:$obj->nameEn;
  $shortDec=($lang=='fr')?$obj->shortDescFr:$obj->shortDescEn;
  $longDesc=($lang=='fr')?$obj->longDescFr:$obj->longDescEn;
  $page=($lang=='fr')?$obj->pageFr:$obj->pageEn;
  $imgLst=$obj->images;
  $firstImg=$obj->images[0];
  $urlSite='https://www.projeqtor.net/';
  $version=$obj->version;
  unset($imgLst[0]);
  $userManual=$obj->userManual;
  if($obj->id='100012' and strpos($longDesc,"<a href=")!==false){
    $strat=substr($longDesc, strpos($longDesc,"<a"));
    $end=substr($strat,0,strpos($strat,"</a>"));
    $search=substr($strat,0,strpos($strat,"a>")+2);
//     $url=substr($end,(strpos($strat,">")+1));
    $link='<iframe width="800" height="500" src="https://www.youtube.com/embed/jnPZlpEheUg" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen "style="border:none !important;"></iframe>';
    $longDesc=str_replace (  $search , $link,$longDesc ) ;
  }
?>  

<input type="hidden" name="objectClassManual" id="objectClassManual" value="Plugin" />
<div class="container" dojoType="dijit.layout.BorderContainer">
  <div dojoType="dijit.layout.ContentPane" region="center" style="overflow-y:auto;">
    <div class="container" dojoType="dijit.layout.BorderContainer">
      <div id="pluginShopDiv" class="listTitle" dojoType="dijit.layout.ContentPane" region="top" style="z-index:3;overflow:visible">
        <table style='width:100%;' >
          <tr>
            <td style="vertical-align: top;width:300px!important;">
              <div style="vertical-align: middle;float:left;width:300px;text-align:center;margin-top:25px;">
                <span  class="title" style="font-size:20px;white-space: unset;"><?php echo $pluginName;?>&nbsp;</span>
                <span  class="title" style="font-size:14px;white-space: unset;"><br><?php echo i18n('pluginVersion',array($version));?>&nbsp;</span>    
              </div>
              <img style="border:none !important;float:left;width:250px;height:250px;margin-left:25px;margin-right:25px;"  src="<?php echo $urlSite.$firstImg->url;?>"></img>
            </td>
            <td style="vertical-align:bottom;" >
                <?php if(!empty($imgLst)){?>
              <div style="height:100%;margin-bottom:50px;float:right;">
                  <?php 
                    foreach ($imgLst as $imgUrl){
                      echo '<img style="border:none !important;float:left;width:200px;margin-left:15px;margin-right:25px;cursor:pointer;" 
                        onClick="showImage(\'Note\',\''.$urlSite.$imgUrl->url.'\',\' \');" src="'.$urlSite.$imgUrl->url.'"></img>';
                     }
                  
                  ?>
              </div>
              <?php }?>
           </td>
          </tr>
        </table>
        <div style="margin-top:45px;margin-left:35px;margin-bottom:25px;">
        <div class="roundedVisibleButton roundedButton generalColClass pluginShopButton" title="<?php echo('goToThePage'); ?>"  onclick="directionExternalPage('<?php echo $page?>')">
          <div style="position: relative;width:100%;height:100%;"><span style="top:12px;vertical-align:middle;"><?php echo i18n('goToThePage');?></span></div>
          <!--  <div style="float:right;position: relative;" class="imageColorNewGui iconGoto iconSize32"></div>  -->          
        </div>
        <div class="roundedVisibleButton roundedButton generalColClass pluginShopButton" title="<?php echo('technicalDoc'); ?>"   onclick="directionExternalPage('<?php echo $userManual?>')">
          <div style="position: relative;"><span style="top:12px;"><?php echo i18n('technicalDoc');?></span></div>
          <!-- <div style="float:right;position: relative;margin-top: 2px;margin-right: 2px;" class="imageColorNewGui iconPdf iconSize32"></div>    -->         
        </div>
        <span class="listTitle" style="font-size:14px;font-weight:bold;" ><?php echo $shortDec;?></span>
        <div style="height:20px;">&nbsp;</div>
        </div>
      </div>
      <div dojoType="dijit.layout.ContentPane" region="center" style="height:48px;margin-left:40px;margin-top:25px;" >

        <div class="longDescPlugin" style="padding: 10px;" ><?php echo $longDesc;?></div>
      </div>
    </div>
  </div>
  <div dojoType="dijit.layout.ContentPane" region="right" style="width:30%;text-align:center;">
    <div class="container" style="margin:0 auto;">
      <iframe   width="100%" height="100%" src="<?php echo $userManual?>" ></iframe>
    </div>
  </div>
</div>
  
</div>


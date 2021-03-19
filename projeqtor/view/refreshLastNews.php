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
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/view/refreshLastNews.php');
$userLang = getSessionValue('currentLocale');
$lang = "en";
if(substr($userLang,0,2)=="fr")$lang="fr";
$stringScr ='src="images';
$stringNewScr = 'src="https://www.projeqtor.org/images/';
$stringHref = '<a href="';
$stringNewHref = '<a target="#" href="https://www.projeqtor.org/';
$getYesNo=Parameter::getGlobalParameter('getVersion');
if ($getYesNo=='NO') {
  //echo "Cannot access remote information from ProjeQtOr web site";
  exit;
}
?>
<div class="swapView" data-dojo-type="dojox/mobile/SwapView"  id="divNewsPage1" name="divNewsPage1">
        <table>
          <tr><?php 
            $urlGetNews = "http://projeqtor.org/admin/getNews.php";
            $currentVersion=null;
            if (ini_get('allow_url_fopen')) {
              enableCatchErrors();
              enableSilentErrors();
              $currentVersion=file_get_contents($urlGetNews);
              disableCatchErrors();
              disableSilentErrors();
             }
           $json = file_get_contents($urlGetNews);
           $obj = json_decode($json);
           $i=1;
           foreach ($obj as $objV=>$val){
              if($val!="id"){
                foreach ($val as $value){
                 if($value->lang!=$lang )continue;
                 if($i==5)break;
                 if($i==3){?><tr><?php } 
                   $valueIntrotext = $value->introtext;
                   $valueFullText= $value->fulltext;
                   if (strpos($valueFullText,$stringScr) !== FALSE) {
                      $valueFullText = str_replace($stringScr, $stringNewScr, $valueFullText);
                   }
                   if (strpos($valueIntrotext,$stringHref) !== FALSE) {
                     $valueIntrotext = str_replace($stringHref, $stringNewHref, $valueIntrotext);
                   }
                   if (strpos($valueFullText,$stringHref) !== FALSE) {
                     $valueFullText = str_replace($stringHref, $stringNewHref, $valueFullText);
                   }
                 ?>
                <td>
                  <table>
                    <tr>
                      <td>
                        <div style="position:relative;border-top-left-radius:5px;border-top-right-radius:5px;color:var(--color-dark);font-weight:bold;cursor:pointer;text-align:center;display:flex;flex-direction:column;justify-content:center;overflow:hidden;<?php if($i==1 or $i==3){?>margin-right:10px; <?php } ?>margin-bottom:10px;height:155px;width:165px;border:1px solid var(--color-medium);background:var(--color-light);border-radius:5px;" id="divMsgTitle<?php echo $i;?>" name="divMsgTitle<?php echo $i;?>" onmouseout="hideIntrotext(<?php echo $i;?>)" onmouseover="showIntrotext(<?php echo $i;?>)" onClick="showMsg(<?php echo $i;?>,<?php echo $i/4;?>);">
                         <div id="divMsgtextTitle<?php echo $i;?>" style="padding:15px;"> <?php echo $value->title;?> </div> 
                         <div id="arrowNewsDown<?php echo $i;?>" style="position:absolute;left:76px;bottom:4px;" class="arrow-down"></div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                          <div  onmouseout="hideIntrotext(<?php echo $i;?>)" onmouseover="showIntrotext(<?php echo $i;?>)" 
                              style="margin-bottom:10px;position:relative;cursor:pointer;display:none;border-bottom-left-radius:5px;border-bottom-right-radius:5px;width:165px;overflow-y:auto;background:var(--color-lighter);border:1px solid var(--color-medium);" 
                              id="divSubTitle<?php echo $i;?>" name="divSubTitle<?php echo $i;?>" onClick="showMsg(<?php echo $i;?>,<?php echo $i/4;?>);">
                              <div style="position:relative;padding:12px;"><?php echo $valueIntrotext;?></div>   
                               <?php  if($value->fulltext){
                                      $leftBottom = "left:76px;bottom:215px;";
                                      if($i==2)$leftBottom = "left:250px;bottom:215px;";
                                      if($i==3)$leftBottom = "left:76px;bottom:47px;";
                                      if($i==4)$leftBottom = "left:250px;bottom:47px;";?>
                                <div id="arrowNewsUp<?php echo $i;?>" style="position:fixed;<?php echo $leftBottom ;?>" class="arrow-up"></div>
                              <?php }?>       
                          </div>
                      </td>
                    </tr>
                     <tr>
                      <td><div style="cursor:pointer;display:none;border-bottom-left-radius:5px;border-bottom-right-radius:5px;margin-bottom:10px;width:165px;overflow-y:auto;background:var(--color-lighter);border:1px solid var(--color-medium);"
                               id="divMsgFull<?php echo $i;?>" name="divMsgFull<?php echo $i;?>" onClick="hideMsg(<?php echo $i;?>,<?php echo $i/4;?>);">
                            <div style="padding:10px;"><?php echo $valueIntrotext.$valueFullText?></div>
                          </div>
                      </td>
                    </tr>
                  </table>
                </td>
                <?php $i++;
                  if($i==3){?></tr><?php }
                }
               }
              }?>
        </tr>
        </table>
        <div id="arrowRight1" style="position:absolute;top:335px;left:247px;" class="imageColorNewGui dijitButtonIcon dijitButtonIconNext"> </div>
    </div>
    <div class="swapView" data-dojo-type="dojox/mobile/SwapView"  id="divNewsPage2" name="divNewsPage2">
        <table>
          <tr><?php
          $i=0;
           foreach ($obj as $objV=>$val){
              if($val!="id"){
                foreach ($val as $value){
                 if($value->lang!=$lang )continue;
                 $i++;
                 if($i<5)continue;
                 if($i==9)break;
                 if($i==7){?><tr>
                 <?php } 
                   $valueIntrotext = $value->introtext;
                   $valueFullText= $value->fulltext;
                   if (strpos($valueFullText,$stringScr) !== FALSE) {
                      $valueFullText = str_replace($stringScr, $stringNewScr, $valueFullText);
                   }
                   if (strpos($valueIntrotext,$stringHref) !== FALSE) {
                     $valueIntrotext = str_replace($stringHref,$stringNewHref,$valueIntrotext);
                   }
                   if (strpos($valueFullText,$stringHref) !== FALSE) {
                     $valueFullText = str_replace($stringHref,$stringNewHref,$valueFullText);
                   }
                 ?>
                <td>
                  <table>
                    <tr>
                      <td>
                        <div style="position:relative;border-top-left-radius:5px;border-top-right-radius:5px;color:var(--color-dark);font-weight:bold;cursor:pointer;text-align:center;display:flex;flex-direction:column;justify-content:center;overflow:hidden;<?php if($i==5 or $i==7){?>margin-right:10px; <?php } ?>margin-bottom:10px;height:155px;width:165px;border:1px solid var(--color-medium);background:var(--color-light);border-radius:5px;" id="divMsgTitle<?php echo $i;?>" name="divMsgTitle<?php echo $i;?>" onmouseout="hideIntrotext(<?php echo $i;?>)" onmouseover="showIntrotext(<?php echo $i;?>)" onClick="showMsg(<?php echo $i;?>,<?php echo $i/4;?>);">
                         <div id="divMsgtextTitle<?php echo $i;?>" style="padding:15px;"> <?php echo $value->title;?> </div> 
                         <div id="arrowNewsDown<?php echo $i;?>" style="position:absolute;left:76px;bottom:4px;" class="arrow-down"></div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div  onmouseout="hideIntrotext(<?php echo $i;?>)" onmouseover="showIntrotext(<?php echo $i;?>)" 
                              style="margin-bottom:10px;position:relative;cursor:pointer;display:none;border-bottom-left-radius:5px;border-bottom-right-radius:5px;width:165px;overflow-y:auto;background:var(--color-lighter);border:1px solid var(--color-medium);" 
                              id="divSubTitle<?php echo $i;?>" name="divSubTitle<?php echo $i;?>" onClick="showMsg(<?php echo $i;?>,<?php echo $i/4;?>);">
                              <div style="position:relative;padding:12px;"><?php echo $valueIntrotext;?></div>
                       <?php  if($value->fulltext){
                                 $leftBottom = "left:76px;bottom:215px;";
                                 if($i==6)$leftBottom = "left:250px;bottom:215px;";
                                 if($i==7)$leftBottom = "left:76px;bottom:47px;";
                                 if($i==8)$leftBottom = "left:250px;bottom:47px;";?>
                              <div id="arrowNewsUp<?php echo $i;?>" style="position:fixed;<?php echo $leftBottom;?>" class="arrow-up"></div>
                       <?php }?>
                          </div>
                      </td>
                    </tr>
                     <tr>
                      <td><div style="cursor:pointer;display:none;border-bottom-left-radius:5px;border-bottom-right-radius:5px;margin-bottom:10px;width:165px;overflow-y:auto;background:var(--color-lighter);border:1px solid var(--color-medium);"
                               id="divMsgFull<?php echo $i;?>" name="divMsgFull<?php echo $i;?>" onClick="hideMsg(<?php echo $i;?>,<?php echo $i/4;?>);">
                            <div style="padding:10px;"><?php echo $valueIntrotext.$valueFullText?></div>
                          </div>
                      </td>
                    </tr>
                  </table>
                </td>
                <?php 
                  if($i==8){?></tr><?php }
                }
               }
              }?>
        </tr>
        </table>
        <div id="arrowRight1" style="position:absolute;top:335px;left:75px;" class="imageColorNewGui dijitButtonIcon dijitButtonIconPrevious"> </div>
        <div id="arrowRight1" style="position:absolute;top:335px;left:247px;" class="imageColorNewGui dijitButtonIcon dijitButtonIconNext"> </div>
    </div>
    <div class="swapView" data-dojo-type="dojox/mobile/SwapView"  id="divNewsPage3" name="divNewsPage3">
        <table>
          <tr><?php
          $i=0;
           foreach ($obj as $objV=>$val){
              if($val!="id"){
                foreach ($val as $value){
                 if($value->lang!=$lang )continue;
                 $i++;
                 if($i<9)continue;
                 if($i==13)break;
                 if($i==11){?><tr>
                   <?php }
                   $valueIntrotext = $value->introtext;
                   $valueFullText= $value->fulltext;
                   if (strpos($valueFullText,$stringScr) !== FALSE) {
                      $valueFullText = str_replace($stringScr, $stringNewScr, $valueFullText);
                   }
                   if (strpos($valueIntrotext,$stringHref) !== FALSE) {
                     $valueIntrotext = str_replace($stringHref, $stringNewHref, $valueIntrotext);
                   }
                   if (strpos($valueFullText,$stringHref) !== FALSE) {
                     $valueFullText = str_replace($stringHref, $stringNewHref, $valueFullText);
                   } ?>
                  <td>
                  <table>
                    <tr>
                      <td>
                        <div style="position:relative;border-top-left-radius:5px;border-top-right-radius:5px;color:var(--color-dark);font-weight:bold;cursor:pointer;text-align:center;display:flex;flex-direction:column;justify-content:center;overflow:hidden;<?php if($i==9 or $i==11){?>margin-right:10px; <?php } ?>margin-bottom:10px;height:155px;width:165px;border:1px solid var(--color-medium);background:var(--color-light);border-radius:5px;" id="divMsgTitle<?php echo $i;?>" name="divMsgTitle<?php echo $i;?>" onmouseout="hideIntrotext(<?php echo $i;?>)" onmouseover="showIntrotext(<?php echo $i;?>)" onClick="showMsg(<?php echo $i;?>,<?php echo $i/4;?>);">
                         <div id="divMsgtextTitle<?php echo $i;?>" style="padding:15px;"> <?php echo $value->title;?> </div> 
                         <div id="arrowNewsDown<?php echo $i;?>" style="position:absolute;left:76px;bottom:4px;" class="arrow-down"></div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div  onmouseout="hideIntrotext(<?php echo $i;?>)" onmouseover="showIntrotext(<?php echo $i;?>)" 
                              style="margin-bottom:10px;position:relative;cursor:pointer;display:none;border-bottom-left-radius:5px;border-bottom-right-radius:5px;width:165px;overflow-y:auto;background:var(--color-lighter);border:1px solid var(--color-medium);" 
                              id="divSubTitle<?php echo $i;?>" name="divSubTitle<?php echo $i;?>" onClick="showMsg(<?php echo $i;?>,<?php echo $i/4;?>);">
                              <div style="position:relative;padding:12px;"><?php echo $valueIntrotext;?></div>
                       <?php  if($value->fulltext){
                                 $leftBottom = "left:76px;bottom:215px;";
                                 if($i==10)$leftBottom = "left:250px;bottom:215px;";
                                 if($i==11)$leftBottom = "left:76px;bottom:47px;";
                                 if($i==12)$leftBottom = "left:250px;bottom:47px;";?>
                              <div id="arrowNewsUp<?php echo $i;?>" style="position:fixed;<?php echo $leftBottom;?>" class="arrow-up"></div>
                       <?php }?>
                          </div>
                      </td>
                    </tr>
                     <tr>
                      <td><div style="cursor:pointer;display:none;border-bottom-left-radius:5px;border-bottom-right-radius:5px;margin-bottom:10px;width:165px;overflow-y:auto;background:var(--color-lighter);border:1px solid var(--color-medium);"
                               id="divMsgFull<?php echo $i;?>" name="divMsgFull<?php echo $i;?>" onClick="hideMsg(<?php echo $i;?>,<?php echo $i/4;?>);">
                            <div style="padding:10px;"><?php echo $valueIntrotext.$valueFullText?></div>
                          </div>
                      </td>
                    </tr>
                  </table>
                </td>
                <?php
                  if($i==12){?></tr><?php }
                }
               }
              }?>
        </tr>
        </table>
        <div id="arrowRight1" style="position:absolute;top:335px;left:75px;" class="imageColorNewGui dijitButtonIcon dijitButtonIconPrevious"> </div>
    </div>
    <div class="indicatorPage" data-dojo-type="dojox/mobile/PageIndicator" data-dojo-props='fixed:"bottom"'></div>

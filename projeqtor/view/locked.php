<?php
$indexPhp=true;
$theme="ProjeQtOr";
if (is_file ( "../tool/parametersLocation.php" )) {
  include_once '../tool/projeqtor.php';
  $theme=getTheme();
  if(isNewGui())$firstColor=Parameter::getUserParameter('newGuiThemeColor');
  $background=(isNewGui())?'#'.$firstColor.' !important':' #C3C3EB';
} 
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
 * Default page. Redirects to view directory
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html style="margin: 0px; padding: 0px;">
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<?php if (! isset($debugIEcompatibility) or $debugIEcompatibility==false) {?>  
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
<?php }?> 
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="stylesheet" type="text/css" href="css/projeqtor.css" />
  <link rel="stylesheet" type="text/css" href="css/projeqtorFlat.css" />
    <?php if(isNewGui()){?>
  <link rel="stylesheet" type="text/css" href="../view/css/projeqtorNew.css" />
  <script type="text/javascript" src="js/dynamicCss.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <script type="text/javascript" src="js/projeqtor.js?version=<?php echo $version.'.'.$build;?>" ></script>
  <?php }?>
  <title>ProjeQtOr</title>
  <script type="text/javascript" src="../external/dojo/dojo.js"
    djConfig='parseOnLoad: false, 
              isDebug: false'></script>
  <script type="text/javascript">    
  var isNewGui=<?php echo (isNewGui())?'true':'false';?>;             
     dojo.addOnLoad(function(){
       if (isNewGui) {
         changeTheme('<?php echo getTheme();?>');
         setColorTheming('<?php echo '#'.Parameter::getUserParameter('newGuiThemeColor');?>','<?php echo '#'.Parameter::getUserParameter('newGuiThemeColorBis');?>');
       }
       //dojo.byId("currentLocale").value=dojo.locale;
       window.setTimeout('hideWait();',10);
     });
  </script>
</head>

<body id="body" class="nonMobile tundra <?php echo $theme;?>" style="background-color:<?php echo $background;?>;"  >
  <div id="wait" style="display:none">
  &nbsp;
  </div> 
   <?php if (1 and isNewGui()) echo '<div style="position:absolute;margin-top:-50%;margin-left:-0%;width:250%;height:250%;opacity:10%;z-index:-2;" class="loginBackgroundNewGui"></div>';?>
  <?php if (isNewGui()) echo '<div style="position:absolute;width:100%;height:100%;opacity:60%;z-index:-1;" class="loginBackgroundNewGui"></div>';?>
  <?php if (0 and isNewGui()) echo '<div style="position:absolute;width:100%;height:100%;opacity:5%;position:-20px;" class="loginBackgroundNewGui"></div>';?> 
  <table align="center" width="100%" height="100%" class="<?php echo (isNewGui())?'':'loginBackground';?>">
    <tr height="100%">
      <td width="100%" align="center">
        <div class="background <?php  echo (isNewGui())?'loginFrameNewGui':'loginFrame' ;?>" >
        <table  align="center" >
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
          <tr style="height:10px;" >
            <td align="left" style="height: 1%;" valign="top">
			        <div style="position:relative;width: 400px; height: 54px;">
			          <div style="overflow:visible;position:absolute;width: 480px; height: 280px;top:15px;text-align: center">
				        <img style="max-height:60px" src="<?php 
				          if (file_exists("../logo.gif")) echo '../logo.gif';
				          else if (file_exists("../logo.jpg")) echo '../logo.jpg';
				          else if (file_exists("../logo.png")) echo '../logo.png';
				          else echo 'img/titleSmall.png';?>" />
			          </div>
			        </div>
            </td>
          </tr>
          <tr style="height:100%" height="100%">
            <td style="height:99%" align="left" valign="middle">
              <div  id="formDiv" dojoType="dijit.layout.ContentPane" region="center" style="width: 470px; height:210px;overflow:hidden">
  <form id="indexForm" name="indexForm" action="main.php" method="post">
    <input type="hidden" id="xcurrentLocale" name="xcurrentLocale" value="en" />
  </form>
  <div class="messageERROR" style="text-align:center;position:relative;top:20px;height:50px">Your application is locked. <br/>Please contact your service provider</div>
  <br/>
  <div class="messageERROR" style="text-align:center;position:relative;top:20px;height:50px">Votre application est verrouillée. <br/>Merci de contacter votre fournisseur de services.</div>
              </div>
            </td>
          </tr>
        </table>
        </div>
      </td>
    </tr>
  </table>
</body>

</html>
